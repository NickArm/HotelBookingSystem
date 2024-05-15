<?php

namespace App\Traits;

use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

trait PriceCalculationTrait
{
    public function calculatePriceDetails($room_id, $checkIn, $checkOut)
    {
        $roomdetails = Room::with(['prices'])->find($room_id);
        $priceDetails = [
            'total_price' => 0,
            'total_nights' => 0,
            'daily_prices' => [],
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

            $priceDetails['total_nights'] = iterator_count($period);
        }

        return $priceDetails;
    }
}
