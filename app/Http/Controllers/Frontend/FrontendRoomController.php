<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;

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
}
