<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategoryRoleMap;
use App\Export\VendorBulkImportSampleExport;
use App\User;
use App\WebBusinessDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class VendorBulkImportController extends Controller
{
    /** Roles used for Other Services / vendors on the portal. */
    private const VENDOR_ROLES = [11, 12];

    public function index(Request $request)
    {
        $categories = $this->activeServiceCategories();
        $activeTab = (int) $request->input('category_id', $categories->first()->id ?? 0);
        $importResult = Session::get('vendor_bulk_import_result');

        return view('vendor-bulk-import.index', compact('categories', 'activeTab', 'importResult'));
    }

    public function sample()
    {
        $filename = 'vendor-bulk-import-sample_'.date('Y-m-d').'.xlsx';

        return Excel::download(new VendorBulkImportSampleExport(), $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'category_id' => 'required|integer|exists:category,id',
            'is_active_listing' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('vendor-bulk-import', ['category_id' => $request->input('category_id')])
                ->withErrors($validator)
                ->withInput();
        }

        $categoryId = (int) $request->input('category_id');
        $isActiveListing = (int) $request->input('is_active_listing');

        $roleId = $this->roleIdForCategory($categoryId);
        if ($roleId === null) {
            Session::flash('error', 'Error|Selected category is not mapped to Vendor (11) or Service Provider (12).');

            return redirect()->route('vendor-bulk-import', ['category_id' => $categoryId]);
        }

        $category = Category::query()->where('id', $categoryId)->where('status', 1)->first();
        if (! $category) {
            Session::flash('error', 'Error|Category not found or inactive.');

            return redirect()->route('vendor-bulk-import');
        }

        try {
            $sheets = Excel::toArray([], $request->file('file'));
        } catch (\Throwable $e) {
            Session::flash('error', 'Error|Could not read Excel file: '.$e->getMessage());

            return redirect()->route('vendor-bulk-import', ['category_id' => $categoryId]);
        }

        $rows = $sheets[0] ?? [];
        if ($rows === []) {
            Session::flash('error', 'Error|Excel file is empty.');

            return redirect()->route('vendor-bulk-import', ['category_id' => $categoryId]);
        }

        $headerMap = $this->mapHeaderRow($rows[0] ?? []);
        if ($headerMap === null) {
            Session::flash('error', 'Error|Invalid header row. Download the sample Excel and keep the column names.');

            return redirect()->route('vendor-bulk-import', ['category_id' => $categoryId]);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $excelRow = $index + 2; // 1-based + header
            $data = $this->rowToData($row, $headerMap);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $company = $data['company_name'];
            $email = $data['email'];
            $mobile = $data['mobile'];

            if ($company === '') {
                $skipped++;
                $errors[] = "Row {$excelRow}: Company Name is required.";
                continue;
            }

            if ($email === '' && $mobile === '') {
                $skipped++;
                $errors[] = "Row {$excelRow}: Email or Mobile is required.";
                continue;
            }

            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                $errors[] = "Row {$excelRow}: Invalid email ({$email}).";
                continue;
            }

            if ($email !== '' && User::query()->where('email', $email)->exists()) {
                $skipped++;
                $errors[] = "Row {$excelRow}: Email already exists ({$email}).";
                continue;
            }

            if ($mobile !== '' && User::query()->where(function ($q) use ($mobile) {
                $q->where('mobile', $mobile)->orWhere('phone', $mobile);
            })->exists()) {
                $skipped++;
                $errors[] = "Row {$excelRow}: Mobile already exists ({$mobile}).";
                continue;
            }

            try {
                DB::transaction(function () use ($data, $roleId, $categoryId, $isActiveListing, $email, $mobile, $company) {
                    $name = $data['name'] !== '' ? $data['name'] : $company;
                    $contactMobile = $data['contact_mobile'] !== '' ? $data['contact_mobile'] : $mobile;

                    $user = User::create([
                        'name' => $name,
                        'email' => $email !== '' ? $email : null,
                        'mobile' => $mobile !== '' ? $mobile : null,
                        'phone' => $mobile !== '' ? $mobile : null,
                        'password' => Hash::make(Str::random(32)),
                        'role' => $roleId,
                        'user_from' => 'web',
                        'userType' => 2,
                        'status' => 1,
                        'is_viewed_by_admin' => 1,
                        'is_active_by_admin' => 1,
                    ]);

                    WebBusinessDetails::create([
                        'user_id' => $user->id,
                        'company_name' => $company,
                        'product' => $data['product'] !== '' ? $data['product'] : null,
                        'contactPerson' => $data['contact_person'] !== '' ? $data['contact_person'] : $name,
                        'contactMobile' => $contactMobile !== '' ? $contactMobile : null,
                        'address' => $data['address'] !== '' ? $data['address'] : null,
                        'city' => $data['city'] !== '' ? $data['city'] : null,
                        'state' => $data['state'] !== '' ? $data['state'] : null,
                        'registered_email' => $email !== '' ? $email : null,
                        'phone' => $contactMobile !== '' ? $contactMobile : null,
                        'selected_category' => (string) $categoryId,
                        'status' => 1,
                        'is_sntc_recommended' => 0,
                        'is_active_listing' => $isActiveListing,
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$excelRow}: ".$e->getMessage();
            }
        }

        Session::flash(
            'vendor_bulk_import_result',
            [
                'category_id' => $categoryId,
                'category_name' => $category->category,
                'created' => $created,
                'skipped' => $skipped,
                'errors' => array_slice($errors, 0, 50),
                'error_total' => count($errors),
            ]
        );

        if ($created > 0) {
            Session::flash('success', "Success|Imported {$created} vendor(s) for {$category->category}. Skipped: {$skipped}.");
        } elseif ($skipped > 0) {
            Session::flash('error', "Error|No vendors created. Skipped: {$skipped}. Check the import summary.");
        } else {
            Session::flash('error', 'Error|No data rows found in the Excel file.');
        }

        return redirect()->route('vendor-bulk-import', ['category_id' => $categoryId]);
    }

    /**
     * Active Other Services categories (roles 11 & 12), same as web/vendor/type.
     */
    private function activeServiceCategories()
    {
        $categoryIds = CategoryRoleMap::query()
            ->whereIn('role', self::VENDOR_ROLES)
            ->where('status', 1)
            ->pluck('category');

        return Category::query()
            ->select(['id', 'category', 'image'])
            ->whereIn('id', $categoryIds)
            ->where('status', 1)
            ->orderByRaw('COALESCE(`order`, 999999)')
            ->orderBy('category')
            ->get();
    }

    private function roleIdForCategory(int $categoryId): ?int
    {
        $role = CategoryRoleMap::query()
            ->where('category', $categoryId)
            ->whereIn('role', self::VENDOR_ROLES)
            ->where('status', 1)
            ->orderBy('role')
            ->value('role');

        return $role !== null ? (int) $role : null;
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array<string, int>|null
     */
    private function mapHeaderRow(array $headerRow): ?array
    {
        $aliases = [
            'name' => ['name'],
            'email' => ['email'],
            'mobile' => ['mobile', 'phone'],
            'company_name' => ['company name', 'company_name', 'company'],
            'contact_person' => ['contact person', 'contact_person', 'contactperson'],
            'contact_mobile' => ['contact mobile', 'contact_mobile', 'contactmobile', 'contact number', 'contact_number'],
            'address' => ['address'],
            'city' => ['city'],
            'state' => ['state'],
            'product' => ['product', 'specialised', 'specialized'],
        ];

        $normalized = [];
        foreach ($headerRow as $index => $label) {
            $key = strtolower(trim((string) $label));
            $key = preg_replace('/\s+/', ' ', $key);
            if ($key !== '') {
                $normalized[$key] = (int) $index;
            }
        }

        $map = [];
        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                if (isset($normalized[$name])) {
                    $map[$field] = $normalized[$name];
                    break;
                }
            }
        }

        // Must recognize at least company + (email or mobile) columns.
        if (! isset($map['company_name'])) {
            return null;
        }
        if (! isset($map['email']) && ! isset($map['mobile'])) {
            return null;
        }

        return $map;
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array<string, int>  $headerMap
     * @return array<string, string>
     */
    private function rowToData(array $row, array $headerMap): array
    {
        $get = function (string $field) use ($row, $headerMap): string {
            if (! isset($headerMap[$field])) {
                return '';
            }
            $value = $row[$headerMap[$field]] ?? '';
            if (is_numeric($value) && ($field === 'mobile' || $field === 'contact_mobile')) {
                // Excel may store mobiles as numbers.
                $value = (string) $value;
                if (str_contains($value, 'E') || str_contains($value, 'e')) {
                    $value = sprintf('%.0f', (float) $value);
                }
            }

            return trim((string) $value);
        };

        $mobile = preg_replace('/\D+/', '', $get('mobile')) ?: '';
        $contactMobile = preg_replace('/\D+/', '', $get('contact_mobile')) ?: '';

        return [
            'name' => $get('name'),
            'email' => strtolower($get('email')),
            'mobile' => $mobile,
            'company_name' => $get('company_name'),
            'contact_person' => $get('contact_person'),
            'contact_mobile' => $contactMobile,
            'address' => $get('address'),
            'city' => $get('city'),
            'state' => $get('state'),
            'product' => $get('product'),
        ];
    }

    /**
     * @param  array<string, string>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
