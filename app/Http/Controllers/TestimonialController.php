<?php

namespace App\Http\Controllers;

use App\DataTables\RolesDataTable;
use App\Testimonial;
use App\TestimonialVideo;
use Illuminate\Support\Facades\Hash;
use Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonial = Testimonial::all();

        return View('testimonial.index' , compact('testimonial'));
    }


    public function create()
    {
        return view('testimonial.create');
    }


    public function save(Request $request)
    {
        $data = $request->all();
        $request->validate([
            'title' => 'required',
            'message' => 'required',
            'file' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if( $request->has('file') ){
            $file = $request->file('file');
            $fileName = rand(11111, 99999) . '.' . $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $file->move('uploads/testimonial' , $fileName);
            unset($data->file);
            $data['file'] = $fileName;
        }

        Testimonial::create($data);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('testimonial.index');
    }


    public function edit($id)
    {
        $decodedId = base64_decode($id);

        $testimonial = Testimonial::find($decodedId);
        if($testimonial == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('testimonial.edit',['model'=>$testimonial]);
    }


    public function update(Request $request)
    {
        $data = $request->all();
        $request->validate([
            'title' => 'required',
            'message' => 'required',
            'file' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if( $request->has('file') ){
            $file = $request->file('file');
            $fileName = rand(11111, 99999) . '.' . $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $file->move('uploads/testimonial' , $fileName);
            unset($data->file);
            $data['file'] = $fileName;
        }
        unset($data['_token']);
        unset($data[$data['id']]);

        Testimonial::where('id' , $data['id'])->update($data);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('testimonial.index');
    }


    public function delete($id)
    {
        $id = base64_decode($id);
        $deleteTestimonial = Testimonial::where('id' , $id)->delete();
        if($deleteTestimonial == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }



    public function videoIndex(){
        $testimonial = TestimonialVideo::all();

        return View('testimonialVideo.index' , compact('testimonial'));
    }

    public function videoShow(){
        $testimonialvideo = TestimonialVideo::get();
    }

    public function videoCreate( ){
        return View('testimonialVideo.create');
    }

    public function videoSave( Request $request){
        $data = $request->all();
        $request->validate([
            'file' => 'file|mimes:mp4,mov',
        ]);

        if( $request->has('file') ){
            $file = $request->file('file');
            $fileName = rand(11111, 99999) . '.' . $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $file->move('uploads/testimonial/video' , $fileName);
            unset($data->file);
            $data['file'] = $fileName;
        }

        TestimonialVideo::create($data);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('testimonial.video.index');
    }
    public function videoEdit(){
        $decodedId = base64_decode($id);

        $testimonial = TestimonialVideo::find($decodedId);
        if($testimonial == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        return view('testimonialVideo.edit',['model'=>$testimonial]);
    }
    public function videoUpdate(Request $request){
        $data = $request->all();
        // $request->validate([
        //     'title' => 'required',
        //     'file' => 'file|mimes:mp4,mov',
        // ]);

        if( $request->has('file') ){
            $file = $request->file('file');
            $fileName = rand(11111, 99999) . '.' . $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $file->move('uploads/testimonial/video' , $fileName);
            unset($data->file);
            $data['file'] = $fileName;
        }
        
        unset($data['_token']);
        unset($data['1']);

        TestimonialVideo::where('id' , $data['id'])->update($data);
        Session::flash('success','Success|Record Saved Successfully!');
        return redirect()->route('testimonial.video.index');
    }

    public function videoDelete($id)
    {
        $id = base64_encode($id);
        $deleteTestimonial = TestimonialVideo::where('id' , $id)->delete();
        if($deleteTestimonial == false){
            Session::flash('error','Error|Remove user associated with role before!');
            return back();
        }else{
            Session::flash('success','Success|Record deleted successfully!');
            return back();
        }
    }

}