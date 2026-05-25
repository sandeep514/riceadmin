<?php

namespace App\Http\Controllers;

use App\DataTables\SamplesDataTable;
use App\Http\Requests\SampleRequest;
use App\Sample;
use App\Notification;
use App\Services\SampleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\User;
use App\TrialPeriod;
use App\QualityMaster;
use App\RiceName;
use App\PublicPacking;
use App\TradeQueriesINR;
use App\TradeCurrentStatus;
use App\RiceFormMilestone3;
use App\RiceForm;
use App\WandModel;
use App\SellerPackingINR;
use App\Buyerpackinginr;
use App\WandTypeModel;
use App\TradeLike;
use App\LivePrice;
use App\Category;
use App\CategoryRoleMap;
use App\TradeCategoryMap;
use App\Services\TradeWebNotificationService;
use App\Services\UserInterestService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function index(){
        // $sellQueries = TradeQueriesINR::with(['RiceNameData' , 'RiceFormMilestone3','RicePackingBuyer' ,'RicePackingSeller','riceGrade' => function($query){
        //     return $query->with('getWandType');
        // }])->orderBy('id' , 'DESC')->get();

        $sellQueries = TradeQueriesINR::with([
            'RiceFormData',
            'RiceNameData',
            'RiceFormMilestone3',
            'RicePackingBuyer',
            'RicePackingSeller',
            'riceGrade' => function ($query) {
                return $query->with('getWandType');
            }
        ])
        ->where('status', '>', 0)
        ->orderByRaw("FIELD(status, 1,4,6,5,2,3,11,12)")
        ->orderBy('id', 'DESC')
        ->get();

        $tradeStatus = [1=> 'open' , 11=> 'close', 12 => 'hold'];
        $tradeCurrentStatus = TradeCurrentStatus::first();
        $currentTrade = $tradeStatus[$tradeCurrentStatus->id];

        $cutoff = Carbon::now()->subDays(30)->startOfDay();

        $closingCount = TradeQueriesINR::where('status', 11)->where('created_at', '<=', $cutoff)->count();
        $soldCount    = TradeQueriesINR::where('status', 3)->where('created_at', '<=', $cutoff)->count();
        $expiredCount = TradeQueriesINR::where('status', 2)->where('created_at', '<=', $cutoff)->count();

        return View('trade.index' , compact('sellQueries' , 'currentTrade','closingCount','soldCount','expiredCount'));
    }

    public function purgeOldByStatus(Request $request)
    {
        $type = $request->input('type'); // closing | sold | expired
        $map = [
            'closing' => 11,
            'sold'    => 3,
            'expired' => 2,
        ];
        if (!array_key_exists($type, $map)) {
            Session::flash('error','Error|Invalid type selected.');
            return back();
        }
        $status = $map[$type];
        $cutoff = Carbon::now()->subDays(30)->startOfDay();
        $updated = TradeQueriesINR::where('status', $status)
            ->where('created_at', '<=', $cutoff)
            ->update(['status' => 0]);
        Session::flash('success','Success|Records deleted for '.$updated.' records of '.$type.' older than 30 days.');
        return back();
    }
    

    public function create(){
        $qualityMaster = RiceName::pluck('type_status' , 'type');
        $packing = PublicPacking::get();
        $livePricesStates =  LivePrice::select('state', 'state_order')->distinct()->orderBy('state_order')->get();
        $categoryList = Category::where('status', 1)->orderByRaw('COALESCE(`order`, 999999)')->orderBy('category')->get();
        $selectedTradeCategoryIds = $categoryList->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        return View('trade.create' , compact('qualityMaster','packing','livePricesStates','categoryList','selectedTradeCategoryIds'));
    }


    public function save(Request $request){
        $request->validate([
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,wmv,webm,mkv', 'max:102400'],
        ]);

        $data = [];
        $selectedQualityTypeInt = $request->category;
        $queryId = $request->queryId??'';
        $quality = $request->quality;
        $qualityForm = $request->riceform;
        $riceformLinkWithLivePrice = $request->riceformLinkWithLivePrice ?? '';
        $stateLinkWithLivePrice = $request->stateLinkWithLivePrice ?? '';
        $packingStreamType = $request->packingStreamType ?? '';

        $selectedGrade = $request->ricegrade;
        $changePackingType = $request->ricepacking;
        $quantity = $request->quantity;
        $offerPrice = $request->price ?? 0;
        $validDays = $request->validity;
        $additioanlInfo = $request->additioanlInfo;
        $location = $request->location;
        $tradeType = $request->tradeType;
        $isHotdeal = $request->hotdeal;
        $riceSize = $request->riceSize;
        $personal_remarks = $request->personal_remarks??'';
        $sntcLotNo = $request->sntcLotNo??'';

        if( isset($_FILES['packingImage']) ){
            $file_name      = $_FILES['packingImage']['name'];
            $file_size      = $_FILES['packingImage']['size'];
            $file_tmp       = $_FILES['packingImage']['tmp_name'];
            $file_type      = $_FILES['packingImage']['type'];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['packing_file'] = $file_name;
        }

        if ($videoFile = $this->storeTradeVideoUpload($request)) {
            $data['video_file'] = $videoFile;
        }


        foreach($_FILES["cookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["cookedFiles"]["name"][$key];
            $file_tmp=$_FILES["cookedFiles"]["tmp_name"][$key];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['cooked_file'] = $file_name;
            }else{
                $data['cooked_file'.$key] = $file_name;
            } 
        }

        foreach($_FILES["uncookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["uncookedFiles"]["name"][$key];
            $file_tmp=$_FILES["uncookedFiles"]["tmp_name"][$key];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['uncooked_file'] = $file_name;
            }else{
                $data['uncooked_file'.$key] = $file_name;
            } 
        }

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['queryId'] = $queryId;
        $data['qualityForm'] = $qualityForm;
        $data['qualityFormLinkWithLivePrice'] = $riceformLinkWithLivePrice??'';
        $data['stateLinkWithLivePrice'] = $request->stateLinkWithLivePrice ?? '';
        $data['packingStreamType'] = $request->packingStreamType ?? '';
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['additioanlInfo'] = $additioanlInfo;
        $data['location'] = $location;
        $data['tradeType'] = $tradeType;
        $data['riceSize'] = $riceSize;
        $data['crop'] = $request->crop;
        $data['hotdeal'] = $isHotdeal;
        $data['personal_remarks'] = $personal_remarks;
        $data['sntcLotNo'] = $sntcLotNo;

        $data['moisture'] = $request->moisture;
        $data['kett'] = $request->kett;
        $data['broken'] = $request->broken;
        $data['dd'] = $request->dd;
        $data['admixture'] = $request->admixture;
        $data['elongation'] = $request->elongation;
        $data['tradeFor'] = $request->tradeFor;
        $data['farmingType'] = $request->farmingType;
        $tradeQuery = TradeQueriesINR::create($data);
        $this->syncTradeCategoryMaps($tradeQuery->id, $request->input('category_ids', []));
        $notifyNote = $this->dispatchTradeWebNotification($tradeQuery, $request);
        $interestNotifyNote = $this->dispatchTradeInterestNotification($tradeQuery, $request);
        $flashNotes = trim($notifyNote . ' ' . $interestNotifyNote);
        Session::flash('success', 'Success|Trade saved successfully!' . ($flashNotes !== '' ? ' ' . $flashNotes : ''));
        if( $request->has('heart') ){
            for( $i = 1 ; $i <= $request->heart ;$i++ ){
                TradeLike::create([
                    'tradeId' => $tradeQuery->id,
                    'userId' => 224,   
                    'status' => 1
                ]);
            }
        }

        return back();
        return View('trade.index');
    }
    
    public function edit($id){
        $livePricesStates =  LivePrice::select('state', 'state_order')->distinct()->orderBy('state_order')->get();

        $tradequeriesinr = TradeQueriesINR::where('id' , $id)->first();
        $tradeType = $tradequeriesinr->tradeType;
        $type = $tradequeriesinr->quality_type;
        $riceNameId = $tradequeriesinr->quality;

        $riceName = RiceName::orderBy('order', 'ASC')->where('status' , 1)->where('type_status' , $type)->pluck('id','name');
        $riceForm = RiceFormMilestone3::orderBy('order' , 'ASC')->where('status' , 1)->pluck('id','name');
        $ricefm = RiceForm::orderBy('order' , 'ASC')->where('type' , ($type == 1)? 'basmati' : 'non-basmati')->where('status' ,1)->pluck('id','form_name');

        $WandType = (WandTypeModel::pluck('id' , 'type'));
        $wandModel = WandModel::where('RiceNameId' , $riceNameId)->with(['getWandType'])->orderBy('order' , 'ASC')->get();

        if( $tradeType == 2 ){
            $packingType  = Buyerpackinginr::get();
        }else{
            $packingType  = SellerPackingINR::get();
        }

        $qualityMaster = RiceName::pluck('type_status' , 'type');
        // $packing = PublicPacking::get();
        $categoryList = Category::where('status', 1)->orderByRaw('COALESCE(`order`, 999999)')->orderBy('category')->get();
        $selectedTradeCategoryIds = TradeCategoryMap::where('trade_id', (int) $id)->where('status', 1)->pluck('category_id')->all();

        return View('trade.edit' , compact('qualityMaster','tradequeriesinr','tradeType','type','riceNameId','riceName','riceForm','ricefm','wandModel','packingType','WandType','livePricesStates','categoryList','selectedTradeCategoryIds'));

    }

    public function update(Request $request){
        $request->validate([
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,wmv,webm,mkv', 'max:102400'],
        ]);

        $data = [];
        $selectedQualityTypeInt = $request->category;
        $quality = $request->quality;
        $qualityForm = $request->riceform;
        $riceformLinkWithLivePrice = $request->riceformLinkWithLivePrice ?? '';
        $stateLinkWithLivePrice = $request->stateLinkWithLivePrice ?? '';
        $packingStreamType = $request->packingStreamType ?? '';
        $selectedGrade = $request->ricegrade;
        $changePackingType = $request->ricepacking;
        $quantity = $request->quantity;
        $offerPrice = $request->price;
        $validDays = $request->validity;
        $riceSize = $request->riceSize;
        $additioanlInfo = $request->additioanlInfo;
        $location = $request->location;
        $tradeType = $request->tradeType;
        $isHotdeal = $request->hotdeal;
        $personal_remarks = $request->personal_remarks;
        $sntcLotNo = $request->sntcLotNo;
        $sold_at = $request->sold_at;

        if( $request->packingImage != '' && isset($_FILES['packingImage']) ){
            $file_name      = $_FILES['packingImage']['name'];
            $file_size      = $_FILES['packingImage']['size'];
            $file_tmp       = $_FILES['packingImage']['tmp_name'];
            $file_type      = $_FILES['packingImage']['type'];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            $data['packing_file'] = $file_name;
        }

        if ($videoFile = $this->storeTradeVideoUpload($request)) {
            $data['video_file'] = $videoFile;
        }

        // if( $request->uncookedFiles != '' && isset($_FILES['uncookedFiles']) ){
        //     $file_name      = $_FILES['uncookedFiles']['name'];
        //     $file_size      = $_FILES['uncookedFiles']['size'];
        //     $file_tmp       = $_FILES['uncookedFiles']['tmp_name'];
        //     $file_type      = $_FILES['uncookedFiles']['type'];
        if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
        //     move_uploaded_file($file_tmp,"uploads/".$file_name);
        //     $data['uncooked_file'] = $file_name;
        // }
        
        // if( $request->cookedFiles != '' && isset($_FILES['cookedFiles']) ){
        //     $file_name      = $_FILES['cookedFiles']['name'];
        //     $file_size      = $_FILES['cookedFiles']['size'];
        //     $file_tmp       = $_FILES['cookedFiles']['tmp_name'];
        //     $file_type      = $_FILES['cookedFiles']['type'];
        if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
        //     move_uploaded_file($file_tmp,"uploads/".$file_name);
        //     $data['cooked_file'] = $file_name;
        // }

        foreach($_FILES["cookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["cookedFiles"]["name"][$key];
            $file_tmp=$_FILES["cookedFiles"]["tmp_name"][$key];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['cooked_file'] = $file_name;
            }else{
                $data['cooked_file'.$key] = $file_name;
            } 
        }

        foreach($_FILES["uncookedFiles"]["tmp_name"] as $key=>$tmp_name) {
            $file_name=$_FILES["uncookedFiles"]["name"][$key];
            $file_tmp=$_FILES["uncookedFiles"]["tmp_name"][$key];
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            move_uploaded_file($file_tmp,"uploads/".$file_name);
            if( $key == 0 ) {
                $data['uncooked_file'] = $file_name;
            }else{
                $data['uncooked_file'.$key] = $file_name;
            } 
        }

        $data['quality_type'] = $selectedQualityTypeInt;
        $data['quality'] = $quality;
        $data['qualityForm'] = $qualityForm;
        $data['qualityFormLinkWithLivePrice'] = $riceformLinkWithLivePrice;
        $data['stateLinkWithLivePrice'] = $stateLinkWithLivePrice;
        $data['packingStreamType'] = $packingStreamType;
        $data['grade'] = $selectedGrade;
        $data['packing'] = $changePackingType;
        $data['quantity'] = $quantity;
        $data['offerPrice'] = $offerPrice;
        $data['validDays'] = $validDays;
        $data['riceSize'] = $riceSize;
        $data['additioanlInfo'] = $additioanlInfo;
        $data['location'] = $location;
        $data['tradeType'] = $tradeType;
        $data['crop'] = $request->crop;
        $data['hotdeal'] = $isHotdeal;
        $data['personal_remarks'] = $personal_remarks;
        $data['sntcLotNo'] = $sntcLotNo;
        $data['sold_at'] = ($sold_at != null)? $sold_at : 0;

        $data['moisture'] = $request->moisture;
        $data['kett'] = $request->kett;
        $data['broken'] = $request->broken;
        $data['dd'] = $request->dd;
        $data['admixture'] = $request->admixture;
        $data['elongation'] = $request->elongation;

        $data = array_filter($data);
        TradeQueriesINR::where('id' , $request['id'])->update(($data));
        $this->syncTradeCategoryMaps((int) $request['id'], $request->input('category_ids', []));
        $tradeRow = TradeQueriesINR::where('id', (int) $request['id'])->first();
        $notifyNote = '';
        if ($tradeRow) {
            $notifyNote = $this->dispatchTradeWebNotification($tradeRow, $request);
        }
        Session::flash('success', 'Success|Trade saved successfully!' . ($notifyNote ? ' ' . $notifyNote : ''));

        return back();
        return View('trade.index');
    }

    protected function storeTradeVideoUpload(Request $request): ?string
    {
        if (! $request->hasFile('video_file')) {
            return null;
        }

        $file = $request->file('video_file');
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        if (! file_exists('uploads')) {
            mkdir('uploads', 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
        $fileName = 'trade_video_' . uniqid('', true) . '.' . $ext;
        $file->move('uploads', $fileName);

        return $fileName;
    }
    
    public function changeStatus ( $tradeId , $status ){
        TradeQueriesINR::where('id' , $tradeId)->update(['status' => $status]);
        // Session::flash('success','Status changed successfully!');
        
        return back();
    }

    public function updateTradeStatus($tradeStatus)
    {
        // 1: open
        // 11: close
        // 12: hold
        $status = [1=> 'open' , 11=> 'Market closed', 12 => 'Market on hold.'];

        TradeCurrentStatus::where('id' , 1)->update(['currentStatus' => $tradeStatus , 'message' => $status[$tradeStatus] ]);
        Session::flash('success','Success|Trade status updated successfully!');
        return back();
    }

    /**
     * Same data as WebAccessController@getCategoriesByRole, but registered on trade routes (auth only).
     * web-access/get-categories sits behind the admin middleware, so non-admin users get 302 on AJAX.
     */
    public function getCategoriesByRoleJson(Request $request)
    {
        $roleId = $request->input('role_id');
        if ($roleId === null || $roleId === '') {
            return response()->json([]);
        }

        $categoryMaps = CategoryRoleMap::where('role', $roleId)
            ->where('status', 1)
            ->with('category_rel')
            ->get();

        $categories = [];
        foreach ($categoryMaps as $map) {
            if ($map->category_rel) {
                $categories[$map->category_rel->id] = $map->category_rel->category;
            }
        }

        return response()->json($categories);
    }

    /**
     * Replace trade_category_map rows for a trade from submitted category_ids[].
     */
    protected function syncTradeCategoryMaps(int $tradeId, $categoryIds): void
    {
        TradeCategoryMap::where('trade_id', $tradeId)->delete();
        $raw = is_array($categoryIds) ? $categoryIds : [];
        $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
        if (empty($ids)) {
            return;
        }
        $validIds = Category::where('status', 1)->whereIn('id', $ids)->pluck('id');
        foreach ($validIds as $cid) {
            TradeCategoryMap::create([
                'trade_id' => $tradeId,
                'category_id' => (int) $cid,
                'status' => 1,
            ]);
        }
    }

    /**
     * Web portal notifications for trade (web_notifications + Reverb/private channel).
     */
    /**
     * @return string Optional note appended to success toast (notification skipped reason).
     */
    protected function dispatchTradeWebNotification(TradeQueriesINR $trade, Request $request): string
    {
        $send = (string) $request->input('trade_notify_send', '0') === '1';
        if (! $send) {
            return '';
        }

        $raw = $request->input('category_ids', []);
        $categoryIds = is_array($raw) ? $raw : [];
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return '(Notification not sent: select at least one web category.)';
        }

        $audience = (string) $request->input('trade_notify_audience', 'all_category');
        if (! in_array($audience, ['all_category', 'selected_users'], true)) {
            $audience = 'all_category';
        }

        $title = (string) $request->input('trade_notify_title', 'New Trade alert');
        $message = (string) $request->input('trade_notify_message', TradeWebNotificationService::DEFAULT_TRADE_NOTIFY_MESSAGE);
        if (trim($message) === '') {
            $message = TradeWebNotificationService::DEFAULT_TRADE_NOTIFY_MESSAGE;
        }

        $selected = $request->input('trade_notify_user_ids', []);
        $selected = is_array($selected) ? array_values(array_filter(array_map('intval', $selected))) : [];

        if ($audience === 'selected_users' && $selected === []) {
            return '(Notification not sent: no users selected.)';
        }

        /** @var TradeWebNotificationService $svc */
        $svc = app(TradeWebNotificationService::class);

        $eligible = $svc->eligibleWebUserIds($categoryIds);
        if ($eligible === []) {
            return '(Notification not sent: no web users found for selected categories.)';
        }

        if ($audience === 'selected_users') {
            $ok = array_values(array_intersect($selected, $eligible));
            if ($ok === []) {
                return '(Notification not sent: selected users do not belong to the chosen categories.)';
            }
        }

        $svc->send(
            $trade,
            $categoryIds,
            true,
            $audience,
            $audience === 'selected_users' ? $selected : null,
            $title,
            $message
        );

        return '';
    }

    /**
     * JSON: web users (portal) for selected web category ids — for trade notification recipient picker.
     */
    public function getWebUsersForCategoriesJson(Request $request)
    {
        $raw = $request->input('category_ids', []);
        $categoryIds = is_array($raw) ? $raw : [];
        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($categoryIds === []) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $svc = app(TradeWebNotificationService::class);
        $ids = $svc->eligibleWebUserIds($categoryIds);
        $users = User::query()
            ->whereIn('id', $ids)
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'mobile', 'email']);

        return response()->json(['status' => true, 'data' => $users]);
    }

    /**
     * JSON: web users whose saved interests match trade quality + form + grade.
     */
    public function getInterestedUsersForTradeJson(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'quality' => 'required|integer|exists:rice_names,id',
            'riceform' => 'required|integer|exists:rice_form_milestone3,id',
            'ricegrade' => 'required|integer|exists:wand,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $users = UserInterestService::webUsersWithExactInterest(
            (int) $request->input('quality'),
            (int) $request->input('riceform'),
            (int) $request->input('ricegrade')
        );

        return response()->json([
            'status' => true,
            'data' => $users->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'mobile' => $u->mobile,
                    'email' => $u->email,
                ];
            })->values(),
            'count' => $users->count(),
        ]);
    }

    /**
     * @return string Optional note appended to success toast.
     */
    protected function dispatchTradeInterestNotification(TradeQueriesINR $trade, Request $request): string
    {
        if ((string) $request->input('trade_interest_notify_send', '0') !== '1') {
            return '';
        }

        $raw = $request->input('trade_interest_notify_user_ids', []);
        $userIds = is_array($raw) ? array_values(array_filter(array_map('intval', $raw))) : [];
        if ($userIds === []) {
            return '(Interest notification not sent: no users selected.)';
        }

        $title = (string) $request->input('trade_interest_notify_title', 'Special trade alert');
        $message = (string) $request->input(
            'trade_interest_notify_message',
            TradeWebNotificationService::DEFAULT_TRADE_INTEREST_NOTIFY_MESSAGE
        );
        if (trim($message) === '') {
            $message = TradeWebNotificationService::DEFAULT_TRADE_INTEREST_NOTIFY_MESSAGE;
        }

        /** @var TradeWebNotificationService $svc */
        $svc = app(TradeWebNotificationService::class);
        $svc->sendInterestMatch($trade, $userIds, $title, $message);

        return '';
    }
}
