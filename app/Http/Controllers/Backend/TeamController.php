<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BookArea;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\Facades\Image;

class TeamController extends Controller
{
    public function AllTeam()
    {
        $team = Team::latest()->get();

        return view('admin.backend.team.all_team', compact('team'));
    }

    public function AddTeam()
    {

        return view('admin.backend.team.add_team');
    }

    public function StoreTeam(Request $request)
    {

        $image = $request->file('image');
        $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
        Image::make($image)->resize(550, 670)->save('upload/team/'.$name_gen);
        $save_url = 'upload/team/'.$name_gen;

        Team::insert([
            'name' => $request->name,
            'position' => $request->position,
            'facebook' => $request->facebook,
            'image' => $save_url,
            'created_at' => Carbon::now(),

        ]);

        $notification = [
            'message' => 'Team Inserted  Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->route('all.team')->with($notification);

    }

    public function EditTeam($id)
    {
        $editData = Team::findOrFail($id);

        return view('admin.backend.team.edit_team', compact('editData'));
    }

    public function UpdateTeam(Request $request)
    {
        $team_id = $request->id;
        if ($request->file('image')) {
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()).'.'.$request->file('image')->getClientOriginalExtension();
            $img = $manager->read($request->file('image'));
            $img = $img->resize(550, 670);
            $img->toJpeg(80)->save(base_path('public/upload/team/'.$name_gen));
            $save_url = 'upload/team/'.$name_gen;
            Team::findOrFail($team_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'facebook' => $request->facebook,
                'image' => $save_url,
                'created_at' => Carbon::now(),

            ]);

            $notification = [
                'message' => 'Team Updated  Successfully',
                'alert-type' => 'success',
            ];

            return redirect()->route('all.team')->with($notification);

        }
    }

    public function DeleteTeam($id)
    {
        $teamData = Team::findOrFail($id);
        $img = $teamData->image;
        unlink($img);

        Team::findOrFail($id)->delete();

        return redirect()->back();
    }

    public function BookArea()
    {
        $book = BookArea::find(1);

        return view('admin.backend.book_area.book_area', compact('book'));

    }

    public function BookAreaUpdate(Request $request)
    {

        $book_id = $request->id;

        if ($request->file('image')) {

            // $manager = new ImageManager(new Driver());
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            // $img = $manager->read($request->file('image'));
            Image::make($image)->resize(1000, 1000)->save('upload/bookarea/'.$name_gen);
            $save_url = 'upload/bookarea/'.$name_gen;

            BookArea::findOrFail($book_id)->update([

                'short_title' => $request->short_title,
                'main_title' => $request->main_title,
                'short_desc' => $request->short_desc,
                'link_url' => $request->link_url,
                'image' => $save_url,
            ]);

            $notification = [
                'message' => 'Book Area Updated With Image Successfully',
                'alert-type' => 'success',
            ];

            return redirect()->back()->with($notification);

        } else {

            BookArea::findOrFail($book_id)->update([

                'short_title' => $request->short_title,
                'main_title' => $request->main_title,
                'short_desc' => $request->short_desc,
                'link_url' => $request->link_url,
            ]);

            $notification = [
                'message' => 'Book Area Updated Without Image Successfully',
                'alert-type' => 'success',
            ];

            return redirect()->back()->with($notification);

        }
    }
}
