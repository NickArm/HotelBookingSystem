<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Traits\PriceCalculationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe;

class PaymentController extends Controller
{
    use PriceCalculationTrait;

    public function createPayment(Request $request)
    {

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'country' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'zip_code' => 'required',
            'state' => 'required',
            'payment_method' => 'required',
        ]);

        $data = [];
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['country'] = $request->country;
        $data['phone'] = $request->phone;
        $data['address'] = $request->address;
        $data['zip_code'] = $request->zip_code;
        $data['state'] = $request->state;
        $data['payment_method'] = $request->payment_method;

        Session::put('book_info', $data); //store data from form to Session for next steps
        $book_data = Session::get('book_date');

        $room_extras = $book_data['room_extras'] ?? [];
        $formattedExtras = [];

        if (isset($room_extras) && is_array($room_extras)) {
            foreach ($room_extras as $extra) {
                $extraId = $extra['extra_id'];
                $quantity = (int) $extra['quantity'];

                if ($quantity > 0) {
                    $formattedExtras[$extraId] = $quantity;
                }
            }
        }

        $prices = $this->calculatePriceDetails($book_data['room_id'], $book_data['check_in'], $book_data['check_out'], $formattedExtras);

        if ($request->payment_method == 'Stripe') {
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $s_pay = Stripe\Charge::create([
                'amount' => $prices['total_price'] * 100,
                'currency' => 'EUR',
                'source' => $request->stripeToken,
                'description' => 'Payment for Booking. Booking No '.rand(00000000, 999999999),
            ]);

            if ($s_pay['status'] == 'succeeded') {
                $payment_status = 1;
                $trasation_id = $s_pay->id;
                app('App\Http\Controllers\Frontend\BookingController')->CheckoutStore($trasation_id);
            } else {
                $notification = [
                    'message' => 'Sorry Payment Failed',
                    'alert-type' => 'error',
                ];

                return redirect('/')->with($notification);
            }
        } elseif ($request->payment_method == 'Paypal') {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            $response = $provider->createOrder([
                'intent' => 'CAPTURE',
                'application_context' => [
                    'return_url' => route('paypal.success'),
                    'cancel_url' => route('paypal.cancel'),
                ],
                'purchase_units' => [
                    0 => [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($prices['final_price_with_extras'], 2),
                        ],
                    ],
                ],
            ]);

            if (isset($response['id']) && $response['status'] == 'CREATED') {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] == 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            } else {
                $notification = [
                    'message' => 'Error setting up PayPal payment.',
                    'alert-type' => 'error',
                ];

                return redirect('/')->with($notification);
            }
        } else {

            $payment_Status = 0;
            $trasation_id = 'COD';

            app('App\Http\Controllers\Frontend\BookingController')->CheckoutStore($trasation_id);
        }
    }

    public function successTransaction(Request $request)
    {

        $book_data = Session::get('book_date');
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {

            $notification = [
                'message' => 'Payment Done',
                'alert-type' => 'success',
            ];
            app('App\Http\Controllers\Frontend\BookingController')->CheckoutStore($response['id']);

            return redirect('/')->with($notification);

        } else {

            $notification = [
                'message' => 'Problem with the payment',
                'alert-type' => 'error',
            ];

            return redirect('/')->with($notification);
        }
    }

    public function cancelTransaction(Request $request)
    {
        $notification = [
            'message' => 'Problem with the payment',
            'alert-type' => 'error',
        ];

        return redirect('/')->with($notification);
    }
}
