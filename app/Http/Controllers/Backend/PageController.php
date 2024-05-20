<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::all();

        return view('backend.pages.page.list_pages', compact('pages'));
    }

    public function create()
    {
        return view('backend.pages.page.add_page');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'header_image' => 'nullable|image|max:2048',
        ]);

        // Generate unique slug from the title
        $slug = Str::slug($request->title);
        $count = Page::where('page_slug', 'like', "{$slug}%")->count();
        $data['page_slug'] = $count ? "{$slug}-{$count}" : $slug;

        if ($request->file('header_image')) {

            $image = $request->file('header_image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->resize(1920, 370)->save('upload/pages/'.$name_gen);
            $data['header_image'] = 'upload/pages/'.$name_gen;
        }

        Page::create($data);

        return redirect()->route('list.pages')->with('success', 'Page created successfully');
    }

    public function show(Page $page)
    {
        return view('list.pages', compact('page'));
    }

    public function edit($id)
    {
        $page = Page::find($id);

        return view('backend.pages.page.edit_page', compact('page'));
    }

    public function update(Request $request)
    {

        $page = Page::find($request->id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'header_image' => 'nullable|image|max:2048',
        ]);

        // Update the slug only if the title has changed
        if ($page->title !== $request->title) {
            $slug = Str::slug($request->title);
            $count = Page::where('page_slug', 'like', "{$slug}%")->count();
            $data['page_slug'] = $count ? "{$slug}-{$count}" : $slug;
        }

        if ($request->file('header_image')) {

            $image = $request->file('header_image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->resize(1920, 370)->save('upload/pages/'.$name_gen);
            $data['header_image'] = 'upload/pages/'.$name_gen;
        }

        $page->update($data);

        return redirect()->route('list.pages')->with('success', 'Page updated successfully');
    }

    public function destroy(Request $request)
    {
        $page = Page::find($request->id)->delete();

        return redirect()->route('list.pages')->with('success', 'Page deleted successfully');
    }

    public function PageDetails($slug)
    {

        $page = Page::where('page_slug', $slug)->first();

        return view('frontend.pages.page_details', compact('page'));

    }// End Method
}
