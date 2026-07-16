<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\ChatStatus;
use App\USD_defaultmaster;
use App\LivePrice;
use App\OceanFreight;
use App\QualityMaster;
use App\Defaultvalue;
use App\Events\AdminEvent;
use App\Services\WebPortalNotificationDelivery;
use App\User;
use Session;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $defaultvalue = Defaultvalue::first();
        $chatstatus = ChatStatus::first();
        $defaultMaster = USD_defaultmaster::orderBy('applied_for' , 'DESC')->get();
        return view('home' , compact('chatstatus' , 'defaultMaster','defaultvalue'));
    }

    public function clonePreviousDayRecord()
    {
        $date = Carbon::now()->format('Y-m-d');
        $lastInsertedDataDate = LivePrice::orderBy('created_at' , 'desc')->whereDate('created_at' , '<=' , $date)->first()->created_at;

        $lastInsertedData = LivePrice::select("tradeFor","farmingType","name","form","cropGrade","cropYear","min_price","max_price","state","up_down","opening","closing","monthStart","monthEnd","status","state_order","name_order","form_order")->whereDate('created_at' , Carbon::parse($lastInsertedDataDate)->format('Y-m-d'))->get()->toArray();

        LivePrice::insert($lastInsertedData);
        Session::flash('success','Success|Price cloned successfully!');
        return back();
    }

    /**
     * Test notification:
     * - Web React: AdminEvent on admin-events (Reverb)
     * - Mobile app: portal notification + FCM when user_id is provided
     */
    public function sendReverbNotification(Request $request)
    {
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            $message = 'Test notification from admin dashboard at ' . now()->format('H:i:s');
        }

        $userId = (int) $request->input('user_id', 0);

        broadcast(new AdminEvent('admin_notification', [
            'message' => $message,
            'source' => 'admin_dashboard',
        ]))->toOthers();

        $flash = 'Success|Reverb sent to web (admin-events channel).';

        if ($userId > 0) {
            $user = User::query()
                ->where('id', $userId)
                ->where('userType', 2)
                ->first();

            if (! $user) {
                Session::flash('error', 'Error|User #' . $userId . ' not found or is not a portal user (userType 2).');
                return back();
            }

            app(WebPortalNotificationDelivery::class)->deliverToUsers(
                [$userId],
                'Test notification',
                $message,
                [
                    'audience_mode' => 'individual',
                    'push_type' => 'admin_test',
                    'fill_role_from_user' => true,
                    'fill_category_from_business' => true,
                ]
            );

            $hasFcm = filled($user->user_token);
            $flash .= ' Portal notification sent to user #' . $userId . '.';
            $flash .= $hasFcm
                ? ' FCM push queued (requires queue worker).'
                : ' No FCM token on this user — log in on the mobile app and enable notifications first.';
        } else {
            $flash .= ' Add a User ID to also send portal Reverb + mobile FCM push.';
        }

        Session::flash('success', $flash);

        return back();
    }
}
