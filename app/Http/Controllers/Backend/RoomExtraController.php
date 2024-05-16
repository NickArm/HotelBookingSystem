<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RoomExtra;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class RoomExtraController extends Controller
{
    public function store(Request $request, $roomId)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image',
        ]);

        if ($request->file('image')) {

            $image = $request->file('image');
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            Image::make($image)->resize(100, 100)->save('upload/extras/'.$name_gen);
            $room['image'] = $name_gen;

        }

        $data['room_id'] = $roomId;
        RoomExtra::create($data);

        return back()->with('success', 'Extra added successfully.');
    }

    public function update(Request $request, $id)
    {
        $extra = RoomExtra::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image',
        ]);

        if ($request->file('image')) {

            $image = $request->file('image');
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            Image::make($image)->resize(100, 100)->save('upload/extras/'.$name_gen);
            $room['image'] = $name_gen;

        }

        $extra->update($data);

        return back()->with('success', 'Extra updated successfully.');
    }

    public function destroy($id)
    {
        RoomExtra::findOrFail($id)->delete();

        return back()->with('success', 'Extra deleted successfully.');
    }
}
