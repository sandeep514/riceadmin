<?php

namespace App\Http\Controllers;

use App\PaddyQuality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Session;

class PaddyQualityController extends Controller
{
    public function listPaddyQuality()
    {
        $paddyQualities = PaddyQuality::orderByRaw('`order` IS NULL, `order` ASC')
            ->orderBy('id')
            ->get();

        return View('paddyQuality.index', compact('paddyQualities'));
    }

    public function createPaddyQuality()
    {
        return View('paddyQuality.create');
    }

    public function savePaddyQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:basmati,non-basmati',
            'quality' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $nextOrder = ((int) PaddyQuality::max('order')) + 1;

        PaddyQuality::create([
            'type' => $request->type,
            'quality' => $request->quality,
            'description' => $request->description,
            'order' => $nextOrder > 0 ? $nextOrder : 1,
            'status' => 1,
        ]);

        Session::flash('success', 'Success|Paddy quality created successfully.');

        return redirect()->route('list.paddy.quality');
    }

    public function editPaddyQuality($id)
    {
        $data = PaddyQuality::findOrFail($id);

        return View('paddyQuality.edit', compact('data'));
    }

    public function updatePaddyQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:paddy_qualities,id',
            'type' => 'required|in:basmati,non-basmati',
            'quality' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PaddyQuality::where('id', $request->id)->update([
            'type' => $request->type,
            'quality' => $request->quality,
            'description' => $request->description,
        ]);

        Session::flash('success', 'Success|Paddy quality updated successfully.');

        return redirect()->route('list.paddy.quality');
    }

    public function updateStatus($id)
    {
        $paddyQuality = PaddyQuality::findOrFail($id);
        $paddyQuality->update([
            'status' => $paddyQuality->status ? 0 : 1,
        ]);

        Session::flash('success', 'Success|Paddy quality status updated successfully.');

        return back();
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:paddy_qualities,id',
            'order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $item = PaddyQuality::lockForUpdate()->findOrFail($request->id);
            $newOrder = (int) $request->order;
            $oldOrder = $item->order;

            if ((int) $oldOrder === $newOrder) {
                return;
            }

            $other = PaddyQuality::lockForUpdate()
                ->where('order', $newOrder)
                ->where('id', '!=', $item->id)
                ->first();

            $item->update(['order' => null]);
            if ($other) {
                $other->update(['order' => $oldOrder]);
            }
            $item->update(['order' => $newOrder]);
        });

        Session::flash('success', 'Success|Paddy quality order updated successfully.');

        return back();
    }
}
