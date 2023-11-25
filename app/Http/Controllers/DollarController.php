<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Excel;
use App\USD_defaultmaster;
use App\OceanFreight;
use App\QualityMaster;
use App\Defaultvalue;
use App\Domestictransport;
use App\Vendorcategory;
use App\BagVendors;

class DollarController extends Controller
{
    public function index()
    {
        $usdPrices = USD_defaultmaster::get();
        return View('defaultMaster.create' , compact('usdPrices'));
    }
    public function save(Request $request)
    {
        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 2 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){
                        if( count(array_filter($v)) > 1 ){
                            if( !in_array( 'CHA ' , array_filter($v)) ) {          
                                if( count(array_filter($v)) > 3 ){

                                    if( $k > 1 ){
                                        $countableData = (float)((float)round($v[3])+(float)$v[4]+(float)$v[5]+(float)$v[6]+(float)$v[7]);
                                        $processedData[] = [
                                            'order'          => $v[0],
                                            'bag_size'          => $v[1],
                                            'bag_type'          => $v[2],
                                            'bag_cost'          => round($v[3]),
                                            'local_freight'     =>  $v[4],
                                            'cha'               =>  $v[5],
                                            'bank_charges'      =>  $v[6],
                                            'ins'               =>  $v[7],
                                            'total'             =>  $countableData,
                                            'conversion_rate'   =>  $v[9],
                                            'PMT_USD'           =>  round(($countableData / $v[9])),
                                            'applied_for'       =>  ($v[11] == 'All Basmati Varients')? 1 : 0
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if( count($processedData) > 0 ){
            USD_defaultmaster::truncate();
            // foreach($processedData as $k => $v){
                USD_defaultmaster::insert($processedData);
            // }
        }
        return back();

    }
    public function indexFreight()
    {
        $OceanFreight = OceanFreight::get();
        return View('oceanFreight.create' , compact('OceanFreight'));
    }

    public function saveOceanFreight(Request $request)
    {

        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData= [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 0 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){                        
                        if( count(array_filter($v)) > 1 ){
                            if( !in_array( 'Country' , array_filter($v)) ) {   
                                if( count($v) > 5 ){
                                    $processedData[] = [
                                        'sno'           => (int)$v[0],
                                        'region'        => $v[1],
                                        'country'       => $v[2],
                                        'port'          =>  ($v[3] == null)? '' : $v[3],
                                        'freight_21MT'  =>  ($v[4] == null)? 0 : $v[4],
                                        'freight_25MT'  =>  ($v[5] == null)? 0 : $v[5],
                                        'freight_21MT_1MT' => ($v[4] == null)? 0 : round( ( ((int)$v[4])/25) ),
                                        'freight_25MT_1MT' => ($v[5] == null)? 0 : round( ( ((int)$v[5])/25)),
                                        'mobile_code' => $v[6]
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }


        
        if( count($processedData) > 0 ){
            OceanFreight::truncate();
            // foreach($processedData as $k => $v){
                OceanFreight::insert($processedData);
            // }
        }

        return back();
    }
    public function qualityMaster()
    {
        $QualityMaster = QualityMaster::orderBy('quality_type_status' , 'ASC')->get();
        return View('quality_master.create' , compact('QualityMaster'));
    }
    public function saveQualityMaster(Request $request)
    {
        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 0 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){
                        if( !in_array( 'Quality' , array_filter($v))  ) {  
                            if( in_array('basmati' , array_filter($v)) ) {
                                $processedData[] = [
                                    'quality' => $v[1],
                                    'quality_name' => $v[2],
                                    'quality_type' => 'basmati',
                                    'quality_type_status' => 1,
                                    'order' => $v[4],
                                    'status' => 1
                                ];
                            }else{
                                $processedData[] = [
                                    'quality' => $v[1],
                                    'quality_name' => $v[2],
                                    'quality_type' => 'non-basmati',
                                    'quality_type_status' => 2,
                                    'status' => 1,
                                    'order' => $v[4],

                                ];
                            }
                        }
                    }
                }
            }
        }
        if( count($processedData) > 0 ){
            QualityMaster::truncate();
            QualityMaster::insert($processedData);
        }
        return back();

    }
    public function defaultValueMaster(){
        $defaultvalue = Defaultvalue::first();

        return View('defaultValues.create' , compact('defaultvalue'));
    }  
    public function saveDefaultValueMaster(Request $request){
        Defaultvalue::truncate();
        Defaultvalue::create([
            'localcharges' => $request->localcharges,
            'financecost' => $request->financecost,
            'dollarvalue' => $request->dollarvalue,
            'bagcost' => $request->bagcost,
            
        ]);
        return redirect()->route('dollarExcel.default.value.master');
    }  

    public function domesticTransportMaster(){
        $domestictransport = Domestictransport::get();

        return View('domestictransport.create' , compact('domestictransport'));
    }

    public function saveDomestictransportMaster(Request $request){
        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 0 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){
                        if( !in_array( 'Transport from' , array_filter($v))  ) {  
                            $processedData[] = [
                                'from' => $v[0],
                                'to' => $v[1],
                                'upto' => $v[2],
                                'pmt' => $v[3],
                                'status' => 1
                            ];
                        }
                    }
                }
            }
        }
        if( count($processedData) > 0 ){
            Domestictransport::truncate();
            Domestictransport::insert($processedData);
        }
        return back();
    }
    public function vendorCategoryMaster(){
        $domestictransport = Vendorcategory::get();
        return View('vendorCategory.create' , compact('domestictransport'));
    }
    public function saveVendorCategoryVendor(Request $request)
    {
        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];
        if( count($excel) > 0 ){
            if( count($excel[0]) > 0 ){
                foreach( $excel[0] as $k => $v ){
                    if( count(array_filter($v)) > 0 ){
                        if( !in_array( 'Sector of Vendors' , array_filter($v))  ) {  
                            $processedData[] = [
                                'name' => $v[1],
                            ];
                        }
                    }
                }
            }
        }

        if( count($processedData) > 0 ){
            Vendorcategory::truncate();
            Vendorcategory::insert($processedData);
        }
        
        return back();
    }

    public function bagVendorMaster()
    {
        $bagvendor = BagVendors::get();
        $vendorType = BagVendors::vendorType();

        return view('bagVendors.create' , compact('bagvendor' , 'vendorType'));
    }
    
    public function saveBagVendor(Request $request)
    {
        $vendorType = 0;
        if( $request->has('vendorType') ) {
            $vendorType = (int)$request->vendorType;
        }

        $excel = Excel::toArray( [] , $request->file('file'));
        $processedData = [];

        if( count($excel) > 0 ){
            if( count($excel[0]) > 0 ){
                foreach( $excel[0] as $k => $v ){
                    $filter = array_filter($v);
                    if( count($filter) > 1 ){
                        if( !in_array('Address' ,$v) ){
                            $processedData[] = [
                                'vendor_name' => $v[1],
                                'vendor_address' => $v[2],
                                'contact_person' => $v[3],
                                'contact_number' => $v[4],
                                'specialised' => $v[5],
                                'vendor_type' => $vendorType,
                                'email' => $v[6],
                            ];
                        }
                    }

                    // if( count() > 1 ){
                    //     if( !in_array( 'Rice Bag Suppliers' , array_filter($v))  ) { 
                    //         if( !in_array( 'Vendor Name' , array_filter($v))  ) { 

                    //             $processedData[] = [
                    //                 'vendor_name' => $v[1],
                    //                 'vendor_address' => $v[2],
                    //                 'contact_person' => $v[3],
                    //                 'contact_number' => $v[4],
                    //                 'specialised' => $v[5],
                    //                 'vendor_type' => $vendorType
                    //             ];
                    //         }
                    //     }
                    // }
                }
            }
        }
        if( count($processedData) > 0 ){
            BagVendors::where(['vendor_type' => $vendorType])->delete();
            BagVendors::insert($processedData);
        }
        
        return back();
    }
}