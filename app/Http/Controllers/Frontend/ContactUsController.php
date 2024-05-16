<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactUs;
use App\Models\Contact;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    public function ContactUs()
    {

        return view('frontend.contact.contact_us');
    }// End Method

    public function StoreContactUs(Request $request)
    {

        Contact::insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'created_at' => Carbon::now(),
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,

        ];

        $site_email = SiteSetting::first()->email;

        Mail::to($site_email)->send(new ContactUs($data));

        $notification = [
            'message' => 'Your Message Send Successfully',
            'alert-type' => 'success',
        ];

        return redirect()->back()->with($notification);

    }// End Method

    public function AdminContactMessage()
    {

        $contact = Contact::latest()->get();

        return view('backend.contact.contact_message', compact('contact'));

    }// End Method
}
