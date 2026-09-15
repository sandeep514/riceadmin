<?php

namespace App\Http\Controllers;

use App\Services\VendorDestinationMasterImportService;
use App\VendorDestinationCountry;
use App\VendorDestinationPort;
use App\VendorDestinationRegion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Session;

class VendorDestinationPortController extends AbstractVendorNameMasterController
{
    protected function modelClass(): string
    {
        return VendorDestinationPort::class;
    }

    protected function viewFolder(): string
    {
        return 'vendor-destination-ports';
    }

    protected function indexRoute(): string
    {
        return 'vendor-destination-ports';
    }

    protected function label(): string
    {
        return 'Destination port';
    }

    protected function nameColumn(): string
    {
        return 'name';
    }

    public function index()
    {
        $records = VendorDestinationPort::with(['region', 'country'])
            ->orderByDesc('id')
            ->get();

        return view($this->viewFolder().'.index', compact('records'));
    }

    public function create()
    {
        return view($this->viewFolder().'.create', $this->formOptions());
    }

    public function import()
    {
        return view($this->viewFolder().'.import');
    }

    public function importSave(Request $request, VendorDestinationMasterImportService $service)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $extension = strtolower((string) $request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return back()
                ->withErrors(['file' => 'Please upload an Excel file (.xlsx, .xls) or CSV.'])
                ->withInput();
        }

        try {
            $result = $service->import($request->file('file'));
        } catch (\Throwable $e) {
            Session::flash('error', 'Error|'.$e->getMessage());

            return back();
        }

        Session::flash(
            'success',
            'Success|Imported '.$result['regions_created'].' regions, '.$result['countries_created'].' countries, and '.$result['ports_created'].' ports. Skipped '.$result['ports_skipped'].' existing ports.'
        );

        return redirect()->route($this->indexRoute());
    }

    public function edit($id)
    {
        $model = VendorDestinationPort::find($id);
        if ($model === null) {
            Session::flash('error', 'Error|No record found!');

            return back();
        }

        return view($this->viewFolder().'.edit', array_merge($this->formOptions($model), [
            'model' => $model,
        ]));
    }

    /**
     * @return array{name:string, description:?string, status:int, region_id:int, country_id:int}
     */
    protected function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'region_id' => 'required|integer|exists:vendor_destination_regions,id',
            'country_id' => [
                'required',
                'integer',
                Rule::exists('vendor_destination_countries', 'id')
                    ->where('region_id', (int) $request->input('region_id')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendor_destination_ports', 'name')
                    ->where(fn ($query) => $query->where('country_id', (int) $request->input('country_id')))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
        ]);

        $country = VendorDestinationCountry::query()->find((int) $validated['country_id']);

        return [
            'region_id' => (int) ($country->region_id ?? $validated['region_id']),
            'country_id' => (int) $validated['country_id'],
            'name' => trim((string) $validated['name']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'status' => (int) $validated['status'],
        ];
    }

    /**
     * @return array{regions:array<int,string>, countries:\Illuminate\Support\Collection}
     */
    private function formOptions(?VendorDestinationPort $model = null): array
    {
        return [
            'regions' => VendorDestinationRegion::options($model?->region_id ? (int) $model->region_id : null),
            'countries' => VendorDestinationCountry::query()
                ->orderBy('name')
                ->get(['id', 'region_id', 'name', 'status']),
        ];
    }
}
