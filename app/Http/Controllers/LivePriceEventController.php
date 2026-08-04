<?php

namespace App\Http\Controllers;

use App\LivePriceEvent;
use App\RiceForm;
use App\RiceName;
use App\RiceType;
use Illuminate\Http\Request;
use Session;

class LivePriceEventController extends Controller
{
    public function index()
    {
        $events = LivePriceEvent::with(['qualityType', 'quality', 'qualityForm'])
            ->orderBy('event_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('live_price_events.index', compact('events'));
    }

    public function create()
    {
        $types = RiceType::orderBy('name')->get();
        $qualities = RiceName::orderBy('name')->get();
        $forms = RiceForm::orderBy('form_name')->get();

        return view('live_price_events.create', compact('types', 'qualities', 'forms'));
    }

    public function save(Request $request)
    {
        $data = $this->validatedPayload($request);
        LivePriceEvent::create($data);

        Session::flash('success', 'Success|Live prices event created successfully!');

        return redirect()->route('live.price.events');
    }

    public function edit($id)
    {
        $event = LivePriceEvent::find($id);
        if (! $event) {
            Session::flash('error', 'Error|No record found!');

            return redirect()->route('live.price.events');
        }

        $types = RiceType::orderBy('name')->get();
        $qualities = RiceName::orderBy('name')->get();
        $forms = RiceForm::orderBy('form_name')->get();

        return view('live_price_events.edit', compact('event', 'types', 'qualities', 'forms'));
    }

    public function update(Request $request, $id)
    {
        $event = LivePriceEvent::find($id);
        if (! $event) {
            Session::flash('error', 'Error|No record found!');

            return redirect()->route('live.price.events');
        }

        $data = $this->validatedPayload($request);
        $event->update($data);

        Session::flash('success', 'Success|Live prices event updated successfully!');

        return redirect()->route('live.price.events');
    }

    public function delete($id)
    {
        $event = LivePriceEvent::find($id);
        if (! $event) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        $event->delete();
        Session::flash('success', 'Success|Record deleted successfully!');

        return back();
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'quality_type_id' => 'nullable|integer|exists:rice_types,id',
            'quality_id' => 'nullable|integer|exists:rice_names,id',
            'quality_form_id' => 'nullable|integer|exists:rice_forms,id',
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'note' => 'required|string',
        ]);
    }
}
