<?php

namespace App\Http\Controllers;

use App\WebVendorCategory;
use App\CategoryRoleMap;
use App\Courier;
use App\LivePrice;
use App\LivePricesOpeningClosing;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Category;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\Designation;
use App\ChartInterval;
use App\Port;
use App\PortImages;
use App\Gallery;
use App\Grade;
use App\Contact;
use App\RiceName;
use App\RiceType;
use App\Testimonial;
use App\TestimonialVideo;
use App\RiceForm;
use App\Order;
use App\BuyQuery;
use App\Plan;
use App\SubPlan;
use App\Message;
use App\TrialPeriod;
use App\Version;
use App\WebBrandVariant;
use App\OceanFreight;
use App\BagVendors;
use App\FutureBuyQueriesINR;
use App\FutureSellQueriesINR;
use App\Helpers\StatusChat;
use App\USD_prices;
// use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\FreeTrialMonths;
use App\QualityMaster;
use App\USD_defaultmaster;
use App\Defaultvalue;
use App\Vendorcategory;
use App\Bid;
use App\USDPlan;
use App\HotDealAccept;
use App\HotDealNotification;
use App\Http\Controllers\MailController;
use Illuminate\Support\Str;
use App\Notification;
use App\Brand;
use App\WandModel;
use App\WandTypeModel;
use App\SellerPackingINR;
use App\RiceFormMilestone3;
use App\SellQueriesINR;
use App\TradeQueriesINR;
use App\TradeStatusMessages;
use App\Buyerpackinginr;
use App\BuyQueriesINR;
use App\TradeLike;
use App\TradeIntrested;
use Mail;
use Auth;
use App\NewsRunner;
use App\TradeCurrentStatus;
use App\WebBrands;
use Illuminate\Support\Facades\Validator;
use Session;
use App\RiceBrandForm;
use App\WebBusinessDetails;
use App\BrandAvailability;

class WebBrandController extends Controller
{
    public function index($userId)
    {
        $imagePre = asset('brands');
        $webBrands = WebBrands::where('user_id' , $userId)->with(['RiceName:id,name' , 'getVariants:id,brand_id'])->get();
        return response()->json(['status' => 'success', 'message' => "Brand get successfully" ,'imagePre' => $imagePre ,'data' => $webBrands]);
    }

    public function brandsForDistributers($userId)
    {
        $imagePre = asset('brands');
        $userDetails = WebBusinessDetails::where('user_id' , $userId)->first();
        if( $userDetails )  {
            $stateId = $userDetails->state;
            $cityId = $userDetails->city;

            $brandsId = BrandAvailability::where('city_id' , $cityId)->pluck('brand_id')->toArray();
            $nearCityBrands = WebBrands::whereIn('id' , $brandsId)->where('status', 1)->get();
            $otherBrands = WebBrands::whereNotIn('id' , $brandsId)->where('status', 1)->get();
        }else{
            $nearCityBrands = collect();
            $otherBrands = WebBrands::where('status', 1)->get();
        }
        
        return response()->json(['status' => 'success', 'message' => "Brand get successfully" ,'imagePre' => $imagePre ,'nearCityBrands' => $nearCityBrands , 'otherBrands' => $otherBrands]);
        
    }

    public function create(Request $request)
    {
        $data = $request->all();
        // User should not be able to control activation from payload.
        unset($data['status']);
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'quality'      => 'integer',
            // 'brand_year'   => 'digits:4|integer|min:1900|max:' . date('Y'),
            'address'      => 'string',
            'product_mode' => 'string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (isset($_FILES['logo'])) {
            $file_tmp  = $_FILES['logo']['tmp_name'];
            $file_name = $_FILES['logo']['name'];
            $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);

            // Generate new unique filename
            $new_name = time() . '_' . uniqid() . '.' . strtolower($file_ext);

            // Create directory if not exists
            if (!is_dir('brands')) {
                mkdir('brands', 0777, true);
            }

            // Move file
            if (move_uploaded_file($file_tmp, "brands/" . $new_name)) {
                $data['logo'] = $new_name;
            } else {
                $data['logo'] = null; // fallback if move fails
            }
        }
        $data['status'] = 0;

        $webBrands = WebBrands::create($data);
        $webBrands->load('RiceName:id,name');

        try {
            $this->sendWebBrandCreatedNotificationMail($webBrands);
        } catch (\Throwable $e) {
            \Log::warning('Web brand created notification mail failed: '.$e->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => "Brand added successfully"]);
    }

    private function sendWebBrandCreatedNotificationMail(WebBrands $brand): void
    {
        $userId = (int) ($brand->user_id ?? auth()->id() ?? 0);
        $user = $userId > 0 ? User::query()->find($userId, ['id', 'name', 'email']) : null;

        $logoUrl = null;
        if (! empty($brand->logo)) {
            $logoUrl = asset('brands/'.$brand->logo);
        }

        $mailData = [
            'brandId' => (int) $brand->id,
            'brandName' => $brand->name ?: '—',
            'qualityName' => $brand->RiceName->name ?? '—',
            'brandYear' => $brand->brand_year ?: '—',
            'address' => $brand->address ?: '—',
            'productMode' => $brand->product_mode ?: '—',
            'description' => $brand->description ?: '—',
            'logoUrl' => $logoUrl,
            'statusLabel' => 'Pending',
            'userId' => $userId > 0 ? $userId : '—',
            'userName' => $user->name ?? null,
            'userEmail' => $user->email ?? null,
            'submittedAt' => Carbon::now()->timezone('Asia/Kolkata')->format('d-m-Y, g:i A'),
        ];

        $subject = 'New Web Brand Added – '.($brand->name ?: 'Brand #'.$brand->id);

        MailController::sendWebBrandCreatedMail(
            'enquiry@sntcgroup.com',
            'enquiry@sntcgroup.com',
            'SNTC',
            $subject,
            $mailData
        );
    }

    public function edit(Request $request)
    {
        
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'brand_id'     => 'required|integer',
            'name'         => 'required|string|max:255',
            'quality'      => 'integer',
            'address'      => 'string',
            'product_mode' => 'string',
            'logo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (isset($_FILES['logo'])) {
            $file_tmp  = $_FILES['logo']['tmp_name'];
            $file_name = $_FILES['logo']['name'];
            $file_ext  = pathinfo($file_name, PATHINFO_EXTENSION);

            // Generate new unique filename
            $new_name = time() . '_' . uniqid() . '.' . strtolower($file_ext);

            // Create directory if not exists
            if (!is_dir('brands')) {
                mkdir('brands', 0777, true);
            }

            // Move file
            if (move_uploaded_file($file_tmp, "brands/" . $new_name)) {
                $data['logo'] = $new_name;
            } else {
                $data['logo'] = null; // fallback if move fails
            }
        }

        // $data['status'] = 0;
        unset($data['brand_id']);
        $webBrands = WebBrands::where('id' , $request->brand_id)->update($data);
        return response()->json(['status' => 'success', 'message' => "Brand updated successfully"]);
    }

    public function getQualities(Request $request)
    {
        $quality = RiceName::select(['id' , 'name' ,'status'])->where('status' , 1);
        if( isset($request->type) ){
            $quality = $quality->where('type_status' , $request->type);
        }

        if( $request->has('search') ){
            $searchValue = $request->search;
            $quality = $quality->where('name' , 'like' , '%'.$searchValue.'%');
        }
        $quality = $quality->get();

        return response()->json(['status' => 'success', 'message' => "Quality get successfully" , 'data' => $quality]);

    }

    public function showBrandsToAdmin()
    {
        $brands = webBrands::orderBy('id' , 'desc')->get();

        return View('webBrands.list' , compact('brands'));
    }

    public function toggleWebBrandsStatus($id)
    {
        $brand = WebBrands::find($id);

        if ($brand) {
            $newStatus = $brand->status == 1 ? 0 : 1; // toggle 1→0 or 0→1
            $brand->update(['status' => $newStatus]);

            Session::flash('success' , 'Success|Status updated successfully');
        }
        Session::flash('error' , 'Error|Something went wrong');
        return back();

    }

    public function indexVariant($brandId, Request $request){
        $isMyBrand = false;
        $brand = WebBrands::select('id', 'user_id')->where('id', $brandId)->first();
        if ($brand) {
            if ($request->has('user_id')) {
                $isMyBrand = ((int) $request->user_id === (int) $brand->user_id);
            } elseif (auth()->check()) {
                $isMyBrand = ((int) auth()->id() === (int) $brand->user_id);
            }
        }

        $WebBrandVariant = WebBrandVariant::select('id','variant','brand_id','quality_id','form_id','grade','packing','image','cut_image')->with(['qualityRel:id,name' , 'formRel:id,form_name'])->where('brand_id' , $brandId)->where('status' , 1)->get();

        $availability = $this->buildBrandAvailabilityGrouped((int) $brandId);

        $imagesPath = asset('brands/' . $brandId . '/variant/');
        return response()->json([
            'status' => true,
            'message' => 'Variants get successfully.',
            'imagePath' => $imagesPath,
            'is_my_brand' => $isMyBrand,
            'availability' => $availability,
            'data' => $WebBrandVariant->map(function ($variant) use ($availability) {
                $variant->setAttribute('availability', $availability);

                return $variant;
            }),
        ], 200);
    }

    /**
     * States and cities where a brand is available (from brand_availability).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildBrandAvailabilityGrouped(int $brandId): array
    {
        $rows = BrandAvailability::query()
            ->where('brand_id', $brandId)
            ->where('status', 1)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('web_brands')
                    ->whereColumn('web_brands.id', 'brand_availability.brand_id')
                    ->where('web_brands.status', 1);
            })
            ->with([
                'state_rel:id,state_name,state_code,order_no',
                'city_rel:id,city_name,state_id',
            ])
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $stateId = (int) $row->state_id;
            if (! isset($grouped[$stateId])) {
                $grouped[$stateId] = [
                    'state_id' => $stateId,
                    'state_name' => $row->state_rel->state_name ?? null,
                    'state_code' => $row->state_rel->state_code ?? null,
                    'state_order' => $row->state_rel->order_no ?? null,
                    'cities' => [],
                ];
            }

            if ($row->city_id) {
                $cityId = (int) $row->city_id;
                $grouped[$stateId]['cities'][$cityId] = [
                    'city_id' => $cityId,
                    'city_name' => $row->city_rel->city_name ?? null,
                ];
            }
        }

        $availability = array_values(array_map(function ($state) {
            $state['cities'] = array_values($state['cities']);
            usort($state['cities'], fn ($a, $b) => strcmp((string) $a['city_name'], (string) $b['city_name']));

            return $state;
        }, $grouped));

        usort($availability, function ($a, $b) {
            $orderA = $a['state_order'] ?? PHP_INT_MAX;
            $orderB = $b['state_order'] ?? PHP_INT_MAX;
            if ($orderA !== $orderB) {
                return $orderA <=> $orderB;
            }

            return strcmp((string) $a['state_name'], (string) $b['state_name']);
        });

        return array_map(function ($state) {
            unset($state['state_order']);

            return $state;
        }, $availability);
    }

    public function createVariant(Request $request)
    {
        $variants = $request->input('variants');

        if (!is_array($variants) || empty($variants)) {
            return response()->json([
                'status' => false,
                'message' => 'No variant data provided.'
            ], 400);
        }

        $insertData = [];

        foreach ($variants as $index => $variant) {
            // Validate each variant
            $validator = Validator::make($variant, [
                'variant'       => 'required|string|max:255',
                'brand_id'      => 'required|integer',
                'quality_id'    => 'nullable|integer',
                'form_id'       => 'nullable|integer',
                'grade'         => 'nullable|string|max:255',
                'packing'       => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                    'index'  => $index
                ], 422);
            }

            $data = $variant;

            // ✅ Handle image upload safely (Laravel way)
            if ($request->hasFile("variants.$index.image")) {
                $file = $request->file("variants.$index.image");
                $fileExt = $file->getClientOriginalExtension();
                $newName = time() . '_' . uniqid() . '.' . strtolower($fileExt);

                $folder = public_path('brands/' . $variant['brand_id'] . '/variant');
                if (!is_dir($folder)) mkdir($folder, 0777, true);

                $file->move($folder, $newName);
                $data['image'] = $newName;
            } else {
                $data['image'] = null;
            }

            if ($request->hasFile("variants.$index.cut_image")) {
                $file = $request->file("variants.$index.cut_image");
                $fileExt = $file->getClientOriginalExtension();
                $newName = time() . '_' . uniqid() . '.' . strtolower($fileExt);

                $folder = public_path('brands/' . $variant['brand_id'] . '/variant');
                if (!is_dir($folder)) mkdir($folder, 0777, true);

                $file->move($folder, $newName);
                $data['cut_image'] = $newName;
            } else {
                $data['cut_image'] = null;
            }

            // Add timestamps (insert() doesn’t do this automatically)
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $insertData[] = $data;
        }

        // ✅ One-time bulk insert
        WebBrandVariant::insert($insertData);

        return response()->json([
            'status' => true,
            'message' => 'All variants inserted successfully.',
            'count' => count($insertData)
        ], 201);
    }

    public function editVariant(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'variant'       => 'required|string|max:255',
            'brand_id'      => 'required|integer',
            'quality_id'    => 'nullable|integer',
            'form_id'       => 'nullable|integer',
            'grade'         => 'nullable|string|max:255',
            'packing'       => 'nullable|string|max:255',
            'variantId'     => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
                'index'  => $index
            ], 422);
        }

        if ($request->hasfile("image")) {
            $file = $request->file("image");
            $fileext = $file->getclientoriginalextension();
            $newname = time() . '_' . uniqid() . '.' . strtolower($fileext);

            $folder = public_path('brands/' . $data['brand_id'] . '/variant');
            if (!is_dir($folder)) mkdir($folder, 0777, true);

            $file->move($folder, $newname);
            $data['image'] = $newname;
        } 

        if ($request->hasfile("cut_image")) {
            $file = $request->file("cut_image");
            $fileext = $file->getclientoriginalextension();
            $newname = time() . '_' . uniqid() . '.' . strtolower($fileext);

            $folder = public_path('brands/' . $data['brand_id'] . '/variant');
            if (!is_dir($folder)) mkdir($folder, 0777, true);

            $file->move($folder, $newname);
            $data['cut_image'] = $newname;
        } 
        unset($data['variantId']);

        WebBrandVariant::where(['id' => $request->variantId])->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Variants updated successfully.',
            'data'  => $data
        ], 201);
    }

    public function deleteVariant($variantId)
    {
        $webBrandVariant = WebBrandVariant::where('id' , $variantId)->update(['status' => 0]);

        return response()->json([
            'status' => true,
            'message' => 'Variants deleted successfully.'
        ], 200);
    }

    // public function vendorType()
    // {
    //     $vendorType = BagVendors::vendorType();
    //     $selectedType = [];
    //     foreach( $vendorType as $k => $v ){
    //         if( $k != 8 ){
    //             $selectedType[$k] = $v;
    //         }
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Vendor type get successfully.',
    //         'data' => $selectedType
    //     ], 200);
    // }


    public function vendorType()
    {
        $role = [11,12];
        $categories = CategoryRoleMap::whereIn('role' , $role)->where('status' , 1)->pluck('category');
        $category = Category::whereIn('id' , $categories)->select('category' , 'id' , 'image')->where('status' , 1)->get();

        return response()->json([
            'status' => true,
            'message' => 'Vendor type get successfully.',
            'filePath' => asset('bagimages/'),
            'data' => $category,

        ], 200);
    }



    // public function vendorList($vendorType)
    // {
    //     $bagVendors = BagVendors::select(['id','vendor_name','email','vendor_address','contact_person','contact_number','specialised','vendor_type','status'])->where(['vendor_type' => $vendorType])->get();
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Vendors get successfully.',
    //         'data' => $bagVendors
    //     ], 200);
    // }




    public function vendorList($vendorType)
    {
        $webBusinessDetails = WebBusinessDetails::query()
            ->select(['company_name', 'product', 'contactPerson', 'contactMobile', 'address', 'is_sntc_recommended'])
            ->where('selected_category', $vendorType)
            ->get()
            ->map(function ($row) {
                return [
                    'company_name' => $row->company_name,
                    'product' => $row->product,
                    'contactPerson' => $row->contactPerson,
                    'contactMobile' => $row->contactMobile,
                    'address' => $row->address,
                    'recommended' => (int) ($row->is_sntc_recommended ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Vendors get successfully.',
            'data' => $webBusinessDetails,
        ], 200);
    }



}