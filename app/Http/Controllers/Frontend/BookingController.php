<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\BookConfirm;
use App\Models\Booking;
use App\Models\BookingRoomList;
use App\Models\Room;
use App\Models\RoomBookDate;
use App\Models\RoomExtra;
use App\Models\RoomNumber;
use App\Models\User;
use App\Notifications\BookingComplete;
use App\Traits\PriceCalculationTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    use PriceCalculationTrait;

    public function Checkout()
    {

        if (Session::has('book_date')) {
            $book_data = Session::get('book_date');
            $room = Room::find($book_data['room_id']);
            $fromDate = Carbon::parse($book_data['check_in']);
            $toDate = Carbon::parse($book_data['check_out']);
            $nights = $toDate->diffInDays($fromDate);
            $room_extras = $book_data['room_extras'] ?? [];
            $extraDetails = [];

            // Load the extras' details
            foreach ($room_extras as $extra) {
                $extraId = $extra['extra_id'];
                $quantity = $extra['quantity'];
                if ($quantity > 0) {
                    $extraData = RoomExtra::find($extraId);
                    if ($extraData) {
                        $extraDetails[] = [
                            'title' => $extraData->title,
                            'price' => $extraData->price,
                            'quantity' => $quantity,
                            'total_price' => $quantity * $extraData->price,
                        ];
                    }
                }
            }

            $formattedExtras = [];

            //format room extras to pass them in calculatePriceDetails()
            if (isset($room_extras) && is_array($room_extras)) {
                foreach ($room_extras as $extra) {
                    // $extra['extra_id'] and $extra['quantity'] are assumed to exist based on your data structure
                    $extraId = $extra['extra_id'];
                    $quantity = $extra['quantity'];

                    if ($quantity > 0) {
                        $formattedExtras[$extraId] = (int) $quantity;
                    }
                }
            }

            $priceDetails = $this->calculatePriceDetails($book_data['room_id'], $book_data['check_in'], $book_data['check_out'], $formattedExtras);

            return view('frontend.checkout.checkout', compact('book_data', 'room', 'nights', 'priceDetails', 'extraDetails'));
        } else {
            $notification = [
                'message' => 'Something went wrong!',
                'alert-type' => 'error',
            ];

            return redirect('/')->with($notification);
        }

    }

    public function BookingStore(Request $request)
    {

        $validateData = $request->validate([
            'check_in' => 'required',
            'check_out' => 'required',
            'persion' => 'required',
            'number_of_rooms' => 'required',
            'extras' => 'sometimes|array',

        ]);

        if ($request->available_room < $request->number_of_rooms) {

            $notification = [
                'message' => 'Something want to wrong!',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);
        }
        Session::forget('book_date');

        $data = [];
        $data['number_of_rooms'] = $request->number_of_rooms;
        $data['available_room'] = $request->available_room;
        $data['persion'] = $request->persion;
        $data['check_in'] = date('Y-m-d', strtotime($request->check_in));
        $data['check_out'] = date('Y-m-d', strtotime($request->check_out));
        $data['room_id'] = $request->room_id;
        $data['room_extras'] = $request->extras;

        Session::put('book_date', $data);

        return redirect()->route('checkout');

    }

    public function CheckoutStore($trasation_id)
    {
        $user = User::where('role', 'admin')->get();

        $book_info = Session::get('book_info');
        $book_data = Session::get('book_date');
        //Log::info($book_info);
        //Log::info($book_data);
        $fromDate = Carbon::parse($book_data['check_in']);
        $toDate = Carbon::parse($book_data['check_out']);
        $total_nights = $toDate->diffInDays($fromDate);
        $room = Room::find($book_data['room_id']);
        $room_extras = $book_data['room_extras'] ?? [];

        $formattedExtras = [];

        // Format room extras to pass them in calculatePriceDetails()
        if (isset($room_extras) && is_array($room_extras)) {
            foreach ($room_extras as $extra) {
                $extraId = $extra['extra_id'];
                $quantity = (int) $extra['quantity']; // Cast to integer to handle data correctly

                if ($quantity > 0) {
                    $formattedExtras[$extraId] = $quantity;
                }
            }
        }
        // Format room extras to store in booking table
        $storedExtras = [];
        foreach ($room_extras as $extra) {
            if ((int) $extra['quantity'] > 0) {  // Ensure quantity is more than zero before processing
                $extraData = RoomExtra::find($extra['extra_id']);
                if ($extraData) {
                    $storedExtras[] = [
                        'id' => $extraData->id,
                        'title' => $extraData->title,
                        'description' => $extraData->description,
                        'price' => $extraData->price,
                        'quantity' => (int) $extra['quantity'],
                    ];
                }
            }
        }

        $prices = $this->calculatePriceDetails($book_data['room_id'], $book_data['check_in'], $book_data['check_out'], $formattedExtras);

        $subtotal = $prices['total_price'];
        $discount = ($room->discount / 100) * $subtotal;
        $total_price = $subtotal - $discount;
        $code = rand(00000000, 999999999);

        //store the data @Booking
        $data = new Booking();
        $data->room_id = $room->id;
        $data->user_id = Auth::user()->id;
        $data->check_in = date('Y-m-d', strtotime($book_data['check_in']));
        $data->check_out = date('Y-m-d', strtotime($book_data['check_out']));
        $data->persion = $book_data['persion'];
        $data->number_of_rooms = $book_data['number_of_rooms'];
        $data->total_nights = $total_nights;
        $data->actual_price = $room->price;
        $data->subtotal = $subtotal;
        $data->discount = $discount;
        $data->total_price = $total_price;
        $data->selected_extras = $storedExtras;
        $data->pricing_data = $prices;
        $data->payment_method = $book_info['payment_method'];
        $data->transation_id = $trasation_id;
        $data->payment_status = 0;
        $data->name = $book_info['name'];
        $data->email = $book_info['email'];
        $data->phone = $book_info['phone'];
        $data->address = $book_info['address'];
        $data->country = $book_info['country'];
        $data->state = $book_info['state'];
        $data->zip_code = $book_info['zip_code'];
        $data->code = $code;
        $data->status = 0;
        $data->created_at = Carbon::now();
        $data->save();

        //store the data @BookedDates
        $sdate = date('Y-m-d', strtotime($book_data['check_in']));
        $edate = date('Y-m-d', strtotime($book_data['check_out']));
        $eldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $eldate);
        foreach ($d_period as $period) {
            $booked_dates = new RoomBookDate();
            $booked_dates->booking_id = $data->id;
            $booked_dates->room_id = $room->id;
            $booked_dates->book_date = date('Y-m-d', strtotime($period));
            $booked_dates->save();
        }

        Session::forget('book_date');

        $notification = [
            'message' => 'Your Booking Added Successfully',
            'alert-type' => 'success',
        ];

        Notification::send($user, new BookingComplete($book_info['name']));

        return redirect('/')->with($notification);
    }

    public function BookingList()
    {

        $allData = Booking::orderBy('id', 'desc')->get();

        return view('backend.booking.booking_list', compact('allData'));

    }

    public function EditBooking($id)
    {

        $editData = Booking::with('room')->find($id);

        // $room_prices_data = $this->calculatePriceDetails($editData->room_id, $editData->check_in, $editData->check_out);

        return view('backend.booking.edit_booking', compact('editData'));

    }

    public function UpdateBookingStatus(Request $request, $id)
    {

        $booking = Booking::find($id);
        $booking->payment_status = $request->payment_status;
        $booking->status = $request->status;
        $booking->save();

        //Send Email Functionality -- START

        $sendmail = Booking::find($id);
        $data = [
            'check_in' => $sendmail->check_in,
            'check_out' => $sendmail->check_out,
            'name' => $sendmail->name,
            'email' => $sendmail->email,
            'phone' => $sendmail->phone,
        ];

        Mail::to($sendmail->email)->send(new BookConfirm($data));

        //Send Email Functionality -- END

        $notification = [
            'message' => 'Information Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }

    public function UpdateBooking(Request $request, $id)
    {

        if ($request->available_room < $request->number_of_rooms) {

            $notification = [
                'message' => 'Something Want To Wrong!',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);
        }

        $data = Booking::find($id);
        $data->number_of_rooms = $request->number_of_rooms;
        $data->check_in = date('Y-m-d', strtotime($request->check_in));
        $data->check_out = date('Y-m-d', strtotime($request->check_out));
        $data->save();

        RoomBookDate::where('booking_id', $id)->delete();

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        $eldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $eldate);
        foreach ($d_period as $period) {
            $booked_dates = new RoomBookDate();
            $booked_dates->booking_id = $data->id;
            $booked_dates->room_id = $data->rooms_id;
            $booked_dates->book_date = date('Y-m-d', strtotime($period));
            $booked_dates->save();
        }

        $notification = [
            'message' => 'Booking Updated Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }  // End Method

    public function AssignRoom($booking_id)
    {

        $booking = Booking::find($booking_id);
        $booking_date_array = RoomBookDate::where('booking_id', $booking_id)->pluck('book_date')->toArray();
        $check_date_booking_ids = RoomBookDate::whereIn('book_date', $booking_date_array)->where('room_id', $booking->room_id)->distinct()->pluck('booking_id')->toArray();
        $booking_ids = Booking::whereIn('id', $check_date_booking_ids)->pluck('id')->toArray();
        $assign_room_ids = BookingRoomList::whereIn('booking_id', $booking_ids)->pluck('room_number_id')->toArray();
        $room_numbers = RoomNumber::where('rooms_id', $booking->room_id)->whereNotIn('id', $assign_room_ids)->where('status', 'Active')->get();

        return view('backend.booking.assign_room', compact('booking', 'room_numbers'));
    }

    public function AssignRoomStore($booking_id, $room_number_id)
    {

        $booking = Booking::find($booking_id);
        $check_data = BookingRoomList::where('booking_id', $booking_id)->count();

        if ($check_data < $booking->number_of_rooms) {
            $assign_data = new BookingRoomList();
            $assign_data->booking_id = $booking_id;
            $assign_data->room_id = $booking->rooms_id;
            $assign_data->room_number_id = $room_number_id;
            $assign_data->save();

            $notification = [
                'message' => 'Room Assign Successfully',
                'alert-type' => 'success',
            ];

            return redirect()->back()->with($notification);

        } else {

            $notification = [
                'message' => 'Room Already Assign',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);

        }

    }

    public function AssignRoomDelete($id)
    {

        $assign_room = BookingRoomList::find($id);
        $assign_room->delete();

        $notification = [
            'message' => 'Assign Room Deleted Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }

    public function DownloadInvoice($id)
    {

        $editData = Booking::with('room')->find($id);
        $pdf = Pdf::loadView('backend.booking.booking_invoice', compact('editData'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);

        return $pdf->download('invoice.pdf');

    }// End Method

    public function UserBooking()
    {
        $id = Auth::user()->id;
        $allData = Booking::where('user_id', $id)->orderBy('id', 'desc')->get();

        return view('frontend.dashboard.user_booking', compact('allData'));

    }

    public function UserInvoice($id)
    {

        $editData = Booking::with('room')->find($id);
        $pdf = Pdf::loadView('backend.booking.booking_invoice', compact('editData'))->setPaper('a4')->setOption([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);

        return $pdf->download('invoice.pdf');

    }

    public function MarkAsRead(Request $request, $notificationId)
    {

        $user = Auth::user();
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['count' => $user->unreadNotifications()->count()]);

    }
}
