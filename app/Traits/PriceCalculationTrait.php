<?php

namespace App\Traits;

use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

trait PriceCalculationTrait
{
    public function calculatePriceDetails($room_id, $checkIn, $checkOut, $selectedExtras)
    {
        $roomdetails = Room::with(['prices'])->find($room_id);
        $priceDetails = [
            'total_price' => 0,
            'total_nights' => 0,
            'daily_prices' => [],
            'total_price_after_discount' => 0,
            'discount' => $roomdetails->discount ?? 0,
            'discount_amount' => 0,
            'final_price_with_extras' => 0,
        ];

        if ($checkIn && $checkOut) {
            $period = new CarbonPeriod($checkIn, '1 day', Carbon::parse($checkOut)->subDay());
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $applicablePrice = $roomdetails->prices->first(function ($price) use ($dateStr) {
                    return $dateStr >= $price->start_date && $dateStr <= $price->end_date;
                });

                $dailyPrice = $applicablePrice ? $applicablePrice->price : $roomdetails->price;
                $priceDetails['total_price'] += $dailyPrice;
                $priceDetails['daily_prices'][$dateStr] = $dailyPrice;
            }

            if ($priceDetails['discount'] > 0) {
                $discountFactor = $priceDetails['discount'] / 100;
                $priceDetails['discount_amount'] = $priceDetails['total_price'] * $discountFactor;
                $priceDetails['total_price_after_discount'] = $priceDetails['total_price'] - $priceDetails['discount_amount'];
            } else {
                $priceDetails['total_price_after_discount'] = $priceDetails['total_price'];
            }

            // Calculate the price with selected extras
            foreach ($selectedExtras as $extraId => $quantity) {
                if ($quantity > 0) {
                    $extra = $roomdetails->extras->find($extraId);
                    if ($extra) {
                        $priceDetails['final_price_with_extras'] += $extra->price * $quantity;
                    }
                }
            }
            $priceDetails['final_price_with_extras'] += $priceDetails['total_price_after_discount'];
        }

        return $priceDetails;
    }
}
