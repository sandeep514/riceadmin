<?php

namespace App\Http\Controllers;

use App\DataTables\GalleryReportDatatable;
use Illuminate\Http\Request;
use App\Gallery;

class GalleryController extends Controller
{
	public function index(GalleryReportDatatable $dataTable)
	{
		return $dataTable->render('gallery.index');
	}

	public function Create()
	{
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
		$galleryCounter = rand(1111, 9999) . '0001';
		$lastInsertedGallery = Gallery::all()->last();

		if ($lastInsertedGallery != null) {
			$galleryCounter = rand(1111, 9999) . '' . $lastInsertedGallery->id++;
		}
		if ($request->file) {

			$file = $request->file('file');
			$filename = $file->getClientOriginalName();
			$fileextension = $file->getClientOriginalExtension();
			if ($fileextension == "png" ||  $fileextension == "jpg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" || $fileextension == "jpeg") {

				if ($request->key != null && $request->value != null) {
					if (count($request->key) == count($request->value)) {
						$destinationPath = 'uploads/gallery';

						$file->move($destinationPath, $galleryCounter . '_' . $filename);
						if ($request->has('file2')) {
							$file2 = $request->file('file2');
							$filename2 = $file2->getClientOriginalName();

							$fileextension2 = $file2->getClientOriginalExtension();

							if ($fileextension2 == "png" ||  $fileextension == "JPG" ||  $fileextension == "JPEG" || $fileextension2 == "jpg" || $fileextension2 == "jpeg") {
								$destinationPath2 = 'uploads/gallery';
								$file2->move($destinationPath2, $galleryCounter . '_' . $filename2);
							} else {
								return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
							}
						} else {
							$filename2 = null;
						}

						Gallery::create([
							'title' => $request->title,
							'description' => $request->desc,
							'attachment' => $galleryCounter . '_' . $filename,
							'attachment2' => $galleryCounter . '_' . $filename2,
							'spec' => json_encode(array_combine($request->key, $request->value)),
							'status' => 1,
							'type' => $request->type,
							'amount' => 0
						]);

						return redirect()->route('gallery');
					} else {
						return back()->withErrors(['error' => 'field must be same and valid.']);
					}
				} else {
					return back()->withErrors(['error' => 'field must be same and valid.']);
				}
			} else {
				return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
			}
		}
	}

	public function galleryUpdate(Request $request)
	{


		$requestedValue = [];
		$requestedKey = [];
		foreach ($request->value as $k => $v) {
			if ($v != null) {
				$requestedValue[] = $v;
			}
		}
		foreach ($request->key as $k => $v) {
			if ($v != null) {
				$requestedKey[] = $v;
			}
		}

		$request->validate([
			'title' => 'required',
			'desc' => 'required',
			'key' => 'required|array',
			'value' => 'required|array',
			'type' => 'required'
		]);

		$galleryCounter = rand(1111, 9999) . '0001';
		$lastInsertedGallery = Gallery::all()->last();

		if ($lastInsertedGallery != null) {
			$galleryCounter = rand(1111, 9999) . '' . $lastInsertedGallery->id++;
		}

		$request['key'] = $requestedKey;
		$request['value'] = $requestedValue;

		$gallery = Gallery::where('id', $request->id)->update(['title' => $request->title, 'description' => $request->desc, 'spec' => json_encode(array_combine($request->key, $request->value)), 'type' => $request->type, 'amount' => 0]);

		if ($request->file) {

			$file = $request->file('file');
			$filename = $file->getClientOriginalName();
			$fileextension = $file->getClientOriginalExtension();

			if ($fileextension == "png" || $fileextension == "jpg" || $fileextension == "jpeg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG") {

				if ($request->key != null && $request->value != null) {
					if (count($request->key) == count($request->value)) {
						$destinationPath = 'uploads/gallery';
						$file->move($destinationPath, $galleryCounter . '_' . $filename);

						$gallery = Gallery::where('id', $request->id)->update(['attachment' => $galleryCounter . '_' . $filename]);
						return back();
					} else {
						return back()->withErrors(['error' => 'field must be same and valid.']);
					}
				} else {
					return back()->withErrors(['error' => 'field must be same and valid.']);
				}
			} else {
				return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
			}
		}
		if ($request->has('file2')) {
			if ($request->has('file2')) {
				$file2 = $request->file('file2');
				$filename2 = $file2->getClientOriginalName();

				$fileextension2 = $file2->getClientOriginalExtension();

				if ($fileextension2 == "png" || $fileextension2 == "jpg" || $fileextension2 == "jpeg" ||  $fileextension == "JPG" ||  $fileextension == "JPEG") {
					$destinationPath2 = 'uploads/gallery';
					$file2->move($destinationPath2, $galleryCounter . '_' . $filename2);

					$gallery = Gallery::where('id', $request->id)->update(['attachment2' => $galleryCounter . '_' . $filename2]);
					return back();
				} else {
					return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
				}
			} else {
				$filename2 = null;
			}
		}

		return back()->with('message', 'Update successfully');
	}
	public function editGallery($id)
	{
		$gallery = Gallery::where(['id' => $id])->first();
		return view('gallery.edit', compact('gallery'));
	}
	public function deleteGallery($galleryId)
	{
		Gallery::where('id', $galleryId)->delete();
		return back();
	}
}
