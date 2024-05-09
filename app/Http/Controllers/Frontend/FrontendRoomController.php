<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use App\Models\RoomBookDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class FrontendRoomController extends Controller
{
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

        return view('frontend.room.room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms'));

    }

    public function BookingSearch(Request $request)
    {
        $request->flash(); //store data at session
        if ($request->check_in == $request->check_out) {
            $notification = [
                'message' => 'You cannot checkin and checkout thesame day',
                'alert-type' => 'error',
            ];

            return redirect()->back()->with($notification);
        }

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        $alldate = Carbon::create($edate)->subDay(); // -1 day because we count nights
        $d_period = CarbonPeriod::create($sdate, $alldate); // list dates from sdate to alldate

        $dt_array = [];
        foreach ($d_period as $period) {
            array_push($dt_array, date('Y-m-d', strtotime($period))); // ['2024-05-01','2024-05-02','2024-05-03','2024-05-04'....... ]
        }

        $check_date_booking_ids = RoomBookDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();   // Find distinct booking IDs that have booked any of the dates in the array using RoomBookDate.

        $rooms = Room::withCount('room_numbers')->where('status', 1)->get();     // Fetch all available rooms that are active, including a count of associated room numbers.

        return view('frontend.room.search_room', compact('rooms', 'check_date_booking_ids'));

    }

    public function SearchRoomDetails(Request $request, $id)
    {
        $request->flash();
        $roomdetails = Room::find($id);
        $multiImage = MultiImage::where('rooms_id', $id)->get();
        $facility = Facility::where('rooms_id', $id)->get();

        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        $room_id = $id;

        return view('frontend.room.search_room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms', 'room_id'));

    }// End Method
}
