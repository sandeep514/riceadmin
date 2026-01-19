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
use App\RiceBrandForm;
use Mail;
use Auth;
use App\NewsRunner;
use App\TradeCurrentStatus;
use App\WebBrands;
use App\WebStates;
use App\WebCities;
use Illuminate\Support\Facades\Validator;
use Session;


class WebStatesController extends Controller
{
    public function getStatesList()
    {
        $getWebStates = WebStates::all();
        return response()->json(['status' => true , 'message' => 'Web status get successfully.' ,'data' => $getWebStates ] , 200);
    }

    public function getCityFromStateId($stateId)
    {
        $WebCities = WebCities::where('state_id' , $stateId)->get();
        return response()->json(['status' => true , 'message' => 'Web cities get successfully' ,'data' => $WebCities ] , 200);
    }

    public function getWebBrandForm()
    {
        $riceBrandForm = RiceBrandForm::select(['id' , 'form_name'])->get();
        return response()->json(['status' => true , 'message' => 'Rice brand forms get successfully' ,'data' => $riceBrandForm ] , 200);

    }
}