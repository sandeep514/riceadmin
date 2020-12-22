<?php

namespace App\Http\Controllers;

use App\DataTables\GalleryReportDatatable;
use Illuminate\Http\Request;
use App\Gallery;

class GalleryController extends Controller
{
	public function index(GalleryReportDatatable $dataTable){
        return $dataTable->render('gallery.index');
    }

	public function Create(){
		return View('gallery.create');
	}
    
    public function save(Request $request)
    {
        
    	$request->validate([
    		'title' => 'required',
			'desc' => 'required',
			'file' => 'required|mimes:jpg,jpeg,png',
			'key' => 'required|array',
			'value' => 'required|array',
            'type' => 'required'
    	]);
    	
    	if( $request->file ) {

    		$file = $request->file('file');
    		$filename = $file->getClientOriginalName();
    		$fileextension = $file->getClientOriginalExtension();
    		if( $fileextension =="png" ||  $fileextension == "jpg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" || $fileextension == "jpeg"){

    			if( $request->key != null && $request->value != null ){
    				if( count($request->key) == count($request->value) ){
			    		$destinationPath = 'uploads/gallery';
						$file->move($destinationPath,$filename);
                        if( $request->has('file2') ) {
                            $file2 = $request->file('file2');
                            $filename2 = $file2->getClientOriginalName();

                            $fileextension2 = $file2->getClientOriginalExtension();      

                            if( $fileextension2 =="png" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" || $fileextension2 == "jpg" || $fileextension2 == "jpeg"){
                                $destinationPath2 = 'uploads/gallery';
                                $file2->move($destinationPath2,$filename2);
                                
                            }else{
                                return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
                            }
                        }else{
                            $filename2 = null;
                        }
						Gallery::create([
							'title' => $request->title,
							'description' => $request->desc,
							'attachment' => $filename,
                            'attachment2' => $filename2,
							'spec' => json_encode(array_combine($request->key , $request->value)),
							'status' => 1,
                            'type' => $request->type,
							'amount' => 0
						]);
						return redirect()->route('gallery');
    				}else{
	    				return back()->withErrors(['error' => 'field must be same and valid.']);
	    			}
    			}else{
    				return back()->withErrors(['error' => 'field must be same and valid.']);
    			}

    		}else{
    			return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
    		}
    	}
    }
    
    public function galleryUpdate(Request $request){

        $request->validate([
    		'title' => 'required',
			'desc' => 'required',
			'key' => 'required|array',
			'value' => 'required|array',
            'type' => 'required'
    	]);
    	$request['key'] = array_filter($request->key);
    	$request['value'] = array_filter($request->value);

    	$gallery = Gallery::where('id' , $request->id)->update(['title' => $request->title,'description' => $request->desc,'spec' => json_encode(array_combine($request->key , $request->value)),'type' => $request->type,'amount' => 0]);

    	if( $request->file ) {

    		$file = $request->file('file');
    		$filename = $file->getClientOriginalName();
    		$fileextension = $file->getClientOriginalExtension();

    		if( $fileextension =="png" || $fileextension == "jpg" || $fileextension == "jpeg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" ){

    			if( $request->key != null && $request->value != null ){
    				if( count($request->key) == count($request->value) ){
			    		$destinationPath = 'uploads/gallery';
						$file->move($destinationPath,$filename);
                        
						$gallery = Gallery::where('id' , $request->id)->update([ 'attachment' => $filename ]);
						return back();
    				}else{
	    				return back()->withErrors(['error' => 'field must be same and valid.']);
	    			}
    			}else{
    				return back()->withErrors(['error' => 'field must be same and valid.']);
    			}

    		}else{
    			return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
    		}
    	}
    	if( $request->has('file2') ) {
            if( $request->has('file2') ) {
                $file2 = $request->file('file2');
                $filename2 = $file2->getClientOriginalName();

                $fileextension2 = $file2->getClientOriginalExtension();      

                if( $fileextension2 =="png" || $fileextension2 == "jpg" || $fileextension2 == "jpeg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" ){
                    $destinationPath2 = 'uploads/gallery';
                    $file2->move($destinationPath2,$filename2);
                    
                    $gallery = Gallery::where('id' , $request->id)->update([ 'attachment2' => $filename2 ]);
					return back();
                }else{
                    return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
                }
            }else{
                $filename2 = null;
            }
		}
		
		return back()->with('message', 'Update successfully');
    }
    public function editGallery($id){
        $gallery = Gallery::where(['id' => $id])->first();
        return view('gallery.edit', compact('gallery'));
        
    }
    public function deleteGallery($galleryId)
    {
        Gallery::where('id' , $galleryId)->delete();
        return back();
    }
}