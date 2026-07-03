<?php

namespace App\Http\Controllers;

use App\BrandInterest;
use App\BrandInterestLocation;
use App\BrandInterestMap;
use App\User;
use App\WebBrands;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandInterestController extends Controller
{
    public function store(Request $request)
    {
        $payload = $this->normalizeBrandInterestPayload($request);

        $validator = Validator::make($payload, [
            'brand_id' => 'required|integer|min:1',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_number' => 'required|string|max:64',
            'working_with_brand' => 'nullable|string|max:32',
            'existing_brand_names' => 'nullable|string',
            'basmati_monthly_mt' => 'nullable|string|max:100',
            'non_basmati_monthly_mt' => 'nullable|string|max:100',
            'interested_locations' => 'nullable|array',
            'interested_locations.*.state_id' => 'required|integer|min:1',
            'interested_locations.*.city_id' => 'required|integer|min:1',
            'interested_locations.*.state_name' => 'nullable|string|max:255',
            'interested_locations.*.city_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $brandInterest = BrandInterest::create([
                'user_id' => auth()->id() ?? 0,
                'brand_id' => (int) $payload['brand_id'],
                'contact_person_name' => $payload['contact_person_name'],
                'contact_person_number' => $payload['contact_person_number'],
                'basmati_monthly' => $payload['basmati_monthly_mt'] ?? null,
                'non_basmati_monthly' => $payload['non_basmati_monthly_mt'] ?? null,
                'status' => 1,
            ]);

            $this->saveExistingBrandNames(
                $brandInterest->id,
                (string) ($payload['existing_brand_names'] ?? ''),
                (string) ($payload['working_with_brand'] ?? '')
            );

            $this->saveInterestedLocations(
                $brandInterest->id,
                $payload['interested_locations'] ?? []
            );

            DB::commit();

            $brandInterest->load(['locations', 'brandInterestMaps']);

            try {
                $this->sendBrandInterestNotificationMail($payload, $brandInterest);
            } catch (\Throwable $e) {
                \Log::warning('Brand interest notification mail failed: '.$e->getMessage());
            }

            return response()->json([
                'status' => true,
                'message' => 'Brand interest added successfully.',
                'data' => $brandInterest,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept snake_case (web) and legacy camelCase request keys.
     *
     * @return array<string, mixed>
     */
    private function normalizeBrandInterestPayload(Request $request): array
    {
        $interestedLocations = $request->input('interested_locations');
        if (! is_array($interestedLocations)) {
            $interestedLocations = $request->input('interestedLocations', []);
        }

        $normalizedLocations = [];
        if (is_array($interestedLocations)) {
            foreach ($interestedLocations as $location) {
                if (! is_array($location)) {
                    continue;
                }

                $stateId = (int) ($location['state_id'] ?? $location['stateId'] ?? 0);
                $cityId = (int) ($location['city_id'] ?? $location['cityId'] ?? 0);
                if ($stateId < 1 || $cityId < 1) {
                    continue;
                }

                $normalizedLocations[] = [
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'state_name' => $location['state_name'] ?? $location['stateName'] ?? null,
                    'city_name' => $location['city_name'] ?? $location['cityName'] ?? null,
                ];
            }
        }

        return [
            'brand_id' => (int) ($request->input('brand_id') ?? $request->input('brandId') ?? 0),
            'brand_name' => $request->input('brand_name') ?? $request->input('brandName'),
            'working_with_brand' => $request->input('working_with_brand') ?? $request->input('workingWithBrand'),
            'existing_brand_names' => $request->input('existing_brand_names') ?? $request->input('brandNames'),
            'basmati_monthly_mt' => $request->input('basmati_monthly_mt') ?? $request->input('basmatiMonthly'),
            'non_basmati_monthly_mt' => $request->input('non_basmati_monthly_mt') ?? $request->input('nonBasmatiMonthly'),
            'contact_person_name' => $request->input('contact_person_name') ?? $request->input('contactPersonName'),
            'contact_person_number' => $request->input('contact_person_number') ?? $request->input('contactPersonNumber'),
            'interested_locations' => $normalizedLocations,
        ];
    }

    private function saveExistingBrandNames(int $brandInterestId, string $brandNames, string $workingWithBrand): void
    {
        if ($brandNames === '') {
            return;
        }

        $isWorking = strcasecmp(trim($workingWithBrand), 'Yes') === 0;

        foreach (preg_split('/\s*,\s*/', $brandNames) as $name) {
            $trimmedName = trim($name);
            if ($trimmedName === '') {
                continue;
            }

            BrandInterestMap::create([
                'brand_interest_id' => $brandInterestId,
                'already_working_with_brand_name' => $trimmedName,
                'status' => $isWorking ? 1 : 0,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $locations
     */
    private function saveInterestedLocations(int $brandInterestId, array $locations): void
    {
        if ($locations === []) {
            return;
        }

        $rows = [];
        $now = now();

        foreach ($locations as $location) {
            $rows[] = [
                'brand_interest_id' => $brandInterestId,
                'state_id' => (int) $location['state_id'],
                'city_id' => (int) $location['city_id'],
                'state_name' => $location['state_name'] ?? null,
                'city_name' => $location['city_name'] ?? null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        BrandInterestLocation::insert($rows);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendBrandInterestNotificationMail(array $payload, BrandInterest $brandInterest): void
    {
        $brandName = $payload['brand_name'] ?? null;
        if (! $brandName) {
            $brandName = WebBrands::query()->where('id', $payload['brand_id'])->value('name');
        }
        $brandName = $brandName ?: ('Brand #'.$payload['brand_id']);

        $userId = (int) (auth()->id() ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email']) : null;

        $locationSummary = collect($brandInterest->locations ?? [])
            ->map(function ($location) {
                $state = $location->state_name ?: ('State #'.$location->state_id);
                $city = $location->city_name ?: ('City #'.$location->city_id);

                return $state.' – '.$city;
            })
            ->filter()
            ->implode(', ');

        $mailData = [
            'brandName' => $brandName,
            'brandId' => (int) $payload['brand_id'],
            'interestId' => (int) $brandInterest->id,
            'contactPersonName' => $payload['contact_person_name'],
            'contactPersonNumber' => $payload['contact_person_number'],
            'workingWithBrand' => $payload['working_with_brand'] ?: '—',
            'brandNames' => $payload['existing_brand_names'] ?: '—',
            'basmatiMonthly' => $payload['basmati_monthly_mt'] ?: '—',
            'nonBasmatiMonthly' => $payload['non_basmati_monthly_mt'] ?: '—',
            'interestedLocations' => $locationSummary !== '' ? $locationSummary : '—',
            'userId' => $userId > 0 ? $userId : '—',
            'userName' => $user->name ?? null,
            'userEmail' => $user->email ?? null,
            'submittedAt' => Carbon::now()->timezone('Asia/Kolkata')->format('d-m-Y, g:i A'),
        ];

        $subject = 'New Brand Interest Received – '.$brandName;

        MailController::sendBrandInterestMail(
            'enquiry@sntcgroup.com',
            'enquiry@sntcgroup.com',
            'SNTC',
            $subject,
            $mailData
        );
    }
}
