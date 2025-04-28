<?php

namespace App\Http\Controllers;

use App\Courier;
use App\LivePrice;
use App\MillStatus;
use App\Packing;
use App\PackingType;
use App\Quality;
use App\Repositories\CourierRepository;
use App\Sample;
use App\User;
use App\ChartInterval;
use App\Port;
use App\PortImages;
use App\Gallery;
use App\Contact;
use App\RiceName;
use App\RiceType;
use App\RiceForm;
use App\Order;
use App\BuyQuery;
use App\Plan;
use App\SubPlan;
use App\Message;
use App\TrialPeriod;
use App\Version;
use App\OceanFreight;
use App\BagVendors;
use App\Helpers\StatusChat;
use App\USD_prices;
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
use App\WebBusinessDetails;
use App\WebPersonalDetails;
use App\WebUserAttachment;


class PortalApiController extends Controller
{

    public function uploadAttachments($file, $destination, array $requiredExtentionValidation)
    {
        // $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fileextension = $file->getClientOriginalExtension();

        if (in_array($fileextension, $requiredExtentionValidation)) {

            // $destinationPath = 'uploads/gallery';
            $file->move($destination, $filename);
            return $filename;
            // $gallery = Gallery::where('id', $request->id)->update(['attachment' => $galleryCounter . '_' . $filename]);
            return;
        } else {
            return back()->withErrors(['error' => "File type not allowed ...! only jpg , jpeg, png is allowed."]);
        }
    }

    public function saveUser(Request $request)
    {
        $mobile = $request['mobile'];
        $Newotp = rand(1000, 9999);
        $user = User::where(['mobile' => $mobile, 'userType' => 2, 'is_INR_active' => 0])->first();
        if (!$user) {
            $user = User::create(['mobile' => $mobile, 'otp' => $Newotp, 'userType' => 2]);
        } else {
            $user->update(['otp' => $Newotp]);
            // return response()->json(['status' => false , 'message' => 'User already available', 'isVerified' => ($user->is_INR_active == 1)? true: false ] , 401);
        }
        file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $Newotp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

        return response()->json(['status' => true, 'message' => 'OTP sent successfully on ' . $mobile, 'data' => ['user_id' => $user->id], 'isVerified' => ($user->is_INR_active == 1) ? true : false], 200);
    }

    public function verifyOTP(Request $request)
    {
        if ($request->has('user_id') && $request->has('otp') && $request->user_id != '' && $request->otp != '') {
            $user_id = $request->user_id;
            $otp = $request->otp;
            $user = User::where(['id' => $user_id]);

            $isOTPSame = $user->where(['otp' => $otp])->first();
            $hasBasicDetails = false;

            if ($isOTPSame) {
                $user->update(['is_INR_active' => 1]);
                $data = $user->with(['getWebPersonalDetails', 'getWebBusinessDetails', 'getWebUserAttachment'])->first();

                if ($data->getWebPersonalDetails != null || $data->getWebBusinessDetails != null || $data->getWebUserAttachment != null) {
                    $hasBasicDetails = true;
                }

                return response()->json(['status' => true, 'message' => 'OTP verified successfully', 'hasBasicDetails' => $hasBasicDetails], 200);
            } else {
                return response()->json(['status' => false, 'message' => 'Wrong OTP'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
        }
    }

    public function resendOTP(Request $request)
    {
        if ($request->has('user_id')) {
            $otp = round(1000, 9999);
            $user_id = $request->user_id;

            $user = User::where(['id' => $user_id]);
            if ($user->first()) {
                $user->update(['otp' => $otp]);
                $mobile = ($user->first()->mobile);
                file_get_contents('http://www.truebulksms.biz/api.php?username=rijulbajaj&password=158190&sender=SNTCAL&sendto=' . $mobile . '&message=Thank+you+for+registering+on+SNTC+Rice+Live+Pricing+App.+Your+OTP+Code+is+' . $otp . '.+SNTCAL&PEID=1701172916686910712&templateid=1707172924575773908');

                return response()->json(['status' => true, 'message' =>  'OTP send successfully'], 200);
            } else {
                return response()->json(['status' => false, 'message' => $mobile . ' not available in records'], 401);
            }
        } else {
            return response()->json(['status' => false, 'message' => 'Required fields are missing'], 401);
            //error : required fields are missing
        }
    }

    public function updateUserDetails(Request $request)
    {
        $user_id = $request->user_id;
        // User::
        if ($request->has('personal_details')) {
            $personalDetails = $request->personal_details;
            if (array_key_exists('avatar', $personalDetails)) {
                $dirname = 'webPortal/' . $user_id . '/attachments/avatar/';
                if (!$dirname) {
                    mkdir(asset($dirname, 0755));
                }
                $file = $this->uploadAttachments($personalDetails['avatar'], $dirname, ['jpeg', 'jpg', 'png']);
                $personalDetails['avatar'] = $file;
            }

            $personalDetails['user_id'] = $request['user_id'];
            WebPersonalDetails::updateOrCreate(['user_id' => $user_id], $personalDetails);
        }

        if ($request->has('business_details')) {
            $businessDetails = $request->business_details;
            $businessDetails['user_id'] = $request['user_id'];
            WebBusinessDetails::updateOrCreate(['user_id' => $user_id], $businessDetails);
        }

        if ($request->has('documents.pan_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/pan/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.pan_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['panCard' => $file]);
        }

        if ($request->has('documents.gst_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/gst/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.gst_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['gstCard' => $file]);
        }

        if ($request->has('documents.fssai_file')) {
            $dirname = 'webPortal/' . $user_id . '/attachments/fssai/';
            if (!$dirname) {
                mkdir(asset($dirname, 0755));
            }
            $file = $this->uploadAttachments($request->file('documents.fssai_file'), $dirname, ['jpeg', 'jpg', 'png', 'pdf']);
            WebUserAttachment::updateOrCreate(['user_id' => $user_id], ['fssai' => $file]);
        }

        return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => []], 200);
    }

    public function getUserDetails($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->with(['getWebPersonalDetails', 'getWebBusinessDetails', 'getWebUserAttachment'])->first();
            return response()->json(['status' => true, 'message' => 'user details added successfully', 'data' => $user, 'prefix' => [
                'avatar' => 'webPortal/' . $userId . '/attachments/avatar',
                'gst' => 'webPortal/' . $userId . '/attachments/gst',
                'pan' => 'webPortal/' . $userId . '/attachments/pan',
                'fssai' => 'webPortal/' . $userId . '/attachments/fssai'
            ]], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'required field is missing', 'data' => []], 401);
        }
    }


    public function deleteUser($userId)
    {
        if ($userId != null) {
            $user = User::where('id', $userId)->where('userType', 2)->delete();
            return response()->json(['status' => true, 'message' => 'user deleted successfully', 'data' => $user], 200);
        } else {
            return response()->json(['status' => false, 'message' => 'userid required', 'data' => []], 401);
        }
    }
}
