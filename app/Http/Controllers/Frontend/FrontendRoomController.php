<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use App\Models\RoomBookDate;
use App\Traits\PriceCalculationTrait;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendRoomController extends Controller
{
    use PriceCalculationTrait;

    public function AllFrontendRoomList()
    {

        $rooms = Room::latest()->get();

        return view('frontend.room.all_rooms', compact('rooms'));
    }

    public function RoomDetailsPage($id)
    {

        $roomdetails = Room::find($id);
        $multiImage = MultiImage::where('rooms_id', $id)->get();
        $facility = Facility::where('rooms_id', $id)->get();
        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        dd($roomExtras);

        return view('frontend.room.room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms'));

    }

    public function BookingSearch(Request $request)
    {
        $request->flash();
        if ($request->check_in == $request->check_out) {
            $notification = [
                'message' => 'You cannot checkin and checkout thesame day',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);
        }

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);

        $dt_array = [];
        foreach ($d_period as $period) {
            array_push($dt_array, date('Y-m-d', strtotime($period)));
        }

        $check_date_booking_ids = RoomBookDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();

        $rooms = Room::withCount('room_numbers')->where('status', 1)->get();

        return view('frontend.room.search_room', compact('rooms', 'check_date_booking_ids'));

    }// End Method

    // public function SearchRoomDetails(Request $request, $id)
    // {
    //     $request->flash();
    //     $roomdetails = Room::find($id);
    //     $multiImage = MultiImage::where('rooms_id', $id)->get();
    //     $facility = Facility::where('rooms_id', $id)->get();

    //     $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
    //     $room_id = $id;

    //     return view('frontend.room.search_room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms', 'room_id'));

    // }

    public function SearchRoomDetails(Request $request, $id)
    {
        $request->flash();

        $roomdetails = Room::with(['prices', 'extras'])->find($id);
        $multiImage = MultiImage::where('rooms_id', $id)->get();
        $facility = Facility::where('rooms_id', $id)->get();
        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        $room_id = $id;

        $checkIn = session('_old_input.check_in');
        $checkOut = session('_old_input.check_out');
        $selectedExtras = $request->input('extras', []);

        $priceDetails = $this->calculatePriceDetails($id, $checkIn, $checkOut, $selectedExtras);

        return view('frontend.room.search_room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms', 'room_id', 'priceDetails'));
    }

    public function CheckRoomAvailability(Request $request)
    {

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);
        $dt_array = [];
        foreach ($d_period as $period) {
            array_push($dt_array, date('Y-m-d', strtotime($period)));
        }

        $check_date_booking_ids = RoomBookDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();

        $room = Room::withCount('room_numbers')->find($request->room_id);

        $bookings = Booking::withCount('assign_rooms')->whereIn('id', $check_date_booking_ids)->where('room_id', $room->id)->get()->toArray();

        $total_book_room = array_sum(array_column($bookings, 'assign_rooms_count'));
        $av_room = @$room->room_numbers_count - $total_book_room;

        $toDate = Carbon::parse($request->check_in);
        $fromDate = Carbon::parse($request->check_out);
        $nights = $toDate->diffInDays($fromDate);

        return response()->json(['available_room' => $av_room, 'total_nights' => $nights]);
    }//

    public function calculatePriceWithExtras(Request $request)
    {
        $room_id = $request->input('room_id');
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');
        $selectedExtras = $request->input('extras', []);

        // Ensure the extras array is properly formatted
        $formattedExtras = [];
        foreach ($selectedExtras as $id => $quantity) {
            if ($quantity > 0) {
                $formattedExtras[$id] = (int) $quantity;
            }
        }
        Log::info($formattedExtras);
        $priceDetails = $this->calculatePriceDetails($room_id, $checkIn, $checkOut, $formattedExtras);

        return response()->json([
            'total_price' => $priceDetails['total_price'],
            'discount_amount' => $priceDetails['discount_amount'],
            'final_price_with_extras' => $priceDetails['final_price_with_extras'],
        ]);
    }
}
