<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Brand;
use App\Brandattachmentmodel;

class BrandController extends Controller
{
    public function index()
    {
        $data = Brand::all();
        $dataTable = collect();
        return View('brands.index' , compact('data'));
    }

    public function create(){
        return View('brands.create');        
    }
    
    public function save(Request $request){
        $file = $request->file('brand_logo');
        $filename = $file->getCLientOriginalName();
        $fileExtension = $file->getCLientOriginalExtension();
        
        $acceptedFileType = ['png' , 'jpg', 'jpeg'];
        $lastAddedBrandOrder = Brand::orderBy('orders' , 'DESC')->first();
        
        if( in_array($fileExtension , $acceptedFileType) ) {

            Brand::create(['name' => $request->brand_name, 'image' => $filename , 'orders' => ((int)$lastAddedBrandOrder+1)]);
            $destinationPath = 'uploads/brandlogo';
            $file->move($destinationPath,$filename);

            Session::flash('sucess' , 'Brand generated successfully.');
        }else{
            Session::flash('error', 'File not supported.');
        }
        return back();
    }

    public function edit($brandId){
        $brandId = base64_decode($brandId);
        $brand = Brand::where(['id' => $brandId])->with(['getAttachments'])->first();
        return View('brands.edit' , compact('brand'));
    }
    public function update(Request $request){

        if( $request->has('brand_attachment') ){
            foreach( $request->brand_attachment as $k => $v){
                $file = $v;

                $filename = $file->getCLientOriginalName();
                $fileExtension = $file->getCLientOriginalExtension();
                $acceptedFileType = ['png' , 'jpg', 'jpeg'];
                
                if( in_array($fileExtension , $acceptedFileType) ) { 
                    $destinationPath = 'uploads/brandlogo/brandAttachment/';
                    $file->move($destinationPath,$filename);

                    Brandattachmentmodel::create(['brand_id' => $request->id,'attachment' => $filename, 'status' => 1]);
                    Session::flash('sucess' , 'Brand generated successfully.');
                }else{
                    Session::flash('error', 'File not supported.');
                }
            }
        }
        if( $request->has('brand_logo') ){
            $file = $request->file('brand_logo');

            $filename = $file->getCLientOriginalName();
            $fileExtension = $file->getCLientOriginalExtension();
            $acceptedFileType = ['png' , 'jpg', 'jpeg'];
            
            if( in_array($fileExtension , $acceptedFileType) ) { 
                $destinationPath = 'uploads/brandlogo';
                $file->move($destinationPath,$filename);

                Brand::where('id' , $request->id)->update(['name' => $request->brand_name, 'image' => $filename]);
                Session::flash('sucess' , 'Brand generated successfully.');
            }else{
                Session::flash('error', 'File not supported.');
            }
     
        }else{
            Session::flash('sucess' , 'Brand generated successfully.');
            Brand::where('id' , $request->id)->update(['name' => $request->brand_name]);
        }
        return back();
    }
    public function changeStatus($brandId)
    {   
        $brandDecodedId = base64_decode($brandId);
        $getBrandData = Brand::where('id' , $brandDecodedId)->first();
        if( $getBrandData->status == 1 ){
            Brand::where('id' , $brandDecodedId)->update(['status' => 0]);
        }else{
            Brand::where('id' , $brandDecodedId)->update(['status' => 1]);
        }
        Session::flash('sucess', 'Status updated successfully.');
        return back();    
    }

    public function deleteBrand($brandId)
    {
        Brandattachmentmodel::where('id' , $brandId)->delete();
        Session::flash('sucess', 'Brand attachment deleted successfully.');
        return back();
    }
    public function changeBrandOrder(Request $request)
    {
        $data = array_combine($request->id, $request->brand_order);
        foreach($data as $id => $order){
            Brand::where('id' , $id)->update([ 'orders' => $order ]);
        }
        Session::flash('sucess', 'Brand order updated successfully.');
        return back();
    }
}