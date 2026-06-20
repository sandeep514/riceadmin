<?php

namespace App\Http\Controllers;

use App\BrandInterest;
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
        $validator = Validator::make($request->all(), [
            'brandId' => 'required|integer',
            'workingWithBrand' => 'nullable|string',
            'brandNames' => 'nullable|string',
            'basmatiMonthly' => 'nullable|string',
            'nonBasmatiMonthly' => 'nullable|string',
            'contactPersonName' => 'required|string',
            'contactPersonNumber' => 'required|string',
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
                'brand_id' => $request->brandId,
                'contact_person_name' => $request->contactPersonName,
                'contact_person_number' => $request->contactPersonNumber,
                'basmati_monthly' => $request->basmatiMonthly,
                'non_basmati_monthly' => $request->nonBasmatiMonthly,
                'status' => 1,
            ]);

            if (! empty($request->brandNames)) {
                $brandNames = explode(',', $request->brandNames);
                foreach ($brandNames as $name) {
                    $trimmedName = trim($name);
                    if ($trimmedName !== '') {
                        BrandInterestMap::create([
                            'brand_interest_id' => $brandInterest->id,
                            'already_working_with_brand_name' => $trimmedName,
                            'status' => ($request->workingWithBrand == 'Yes') ? 1 : 0,
                        ]);
                    }
                }
            }

            DB::commit();

            try {
                $this->sendBrandInterestNotificationMail($request, $brandInterest);
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

    private function sendBrandInterestNotificationMail(Request $request, BrandInterest $brandInterest): void
    {
        $brand = WebBrands::query()->where('id', $request->brandId)->value('name');
        $brandName = $brand ?: ('Brand #'.$request->brandId);

        $userId = (int) (auth()->id() ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email']) : null;

        $mailData = [
            'brandName' => $brandName,
            'brandId' => (int) $request->brandId,
            'interestId' => (int) $brandInterest->id,
            'contactPersonName' => $request->contactPersonName,
            'contactPersonNumber' => $request->contactPersonNumber,
            'workingWithBrand' => $request->workingWithBrand ?: '—',
            'brandNames' => $request->brandNames ?: '—',
            'basmatiMonthly' => $request->basmatiMonthly ?: '—',
            'nonBasmatiMonthly' => $request->nonBasmatiMonthly ?: '—',
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
