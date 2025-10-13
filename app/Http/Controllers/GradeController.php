<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Grade;
use Session;

class GradeController extends Controller
{
	public function index()
	{
		$grade = Grade::get();
		return View('grade.index' , compact('grade'));
	}

	// public function create()
	// {
	// 	return View('grade.create');
	// }

	public function save(Request $request)
	{
		$request->validate([
			'name' => 'required',
		]);
		
		Grade::create([
			'name' => $request->name,
			'status' => 1
		]);
		Session::flash('success' , 'Grade created successfully');
		return back();
	}

	public function GradeUpdate(Request $request)
	{
		$request->validate([
			'name' => 'required',
		]);
		$grade = Grade::where('id', $request->id)->update(['name' => $request->name]);
						
		return back()->with('message', 'Grade Update successfully');
	}
	public function editGrade($id)
	{
		$grade = Grade::where(['id' => $id])->first();
		return view('grade.edit', compact('grade'));
	}
	public function deleteGrade($gradeId)
	{
		Grade::where('id', $gradeId)->delete();
		return back();
	}
}
