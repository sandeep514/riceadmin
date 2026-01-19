<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BrandInterest;
use App\BrandInterestMap;
use Illuminate\Support\Facades\Validator;
use DB;


class BrandInterestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brandId' => 'required|integer',
            'workingWithBrand' => 'nullable|string',
            'brandNames' => 'nullable|string', // now optional
            'basmatiMonthly' => 'nullable|string',
            'nonBasmatiMonthly' => 'nullable|string',
            'contactPersonName' => 'required|string',
            'contactPersonNumber' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create BrandInterest entry
            $brandInterest = BrandInterest::create([
                'user_id' => auth()->id() ?? 0, // Replace with authenticated user ID if needed
                'brand_id' => $request->brandId,
                'contact_person_name' => $request->contactPersonName,
                'contact_person_number' => $request->contactPersonNumber,
                'basmati_monthly' => $request->basmatiMonthly,
                'non_basmati_monthly' => $request->nonBasmatiMonthly,
                'status' => 1
            ]);

            // Insert entries in brand_interest_map if brandNames provided
            if (!empty($request->brandNames)) {
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

            return response()->json([
                'status' => true,
                'message' => 'Brand interest added successfully.',
                'data' => $brandInterest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
