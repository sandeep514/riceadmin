<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contact;


class ContactController extends Controller
{
    public static function index()
    {
        $contact = Contact::where('id' , 1)->first();
        return View('contact.create' , compact('contact'));
    }
    public function createContact(Request $request)
    {
        $contact = Contact::get();

        if( $contact->count() == 0 ){
            Contact::create([
                'phone' => $request->phone,
                'email' => $request->email,
                'status'=> 1
            ]);
        }else{
            Contact::where('id' , 1)->update([
                'phone' => $request->phone,
                'email' => $request->email,
                'status'=> 1
            ]);
        }
        return back();
    }
}