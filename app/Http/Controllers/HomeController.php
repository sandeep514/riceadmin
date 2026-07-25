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
        $today = Carbon::today();

        if (LivePrice::whereDate('created_at', $today)->exists()) {
            Session::flash('error', 'Error|Today already has live prices. Clone skipped to avoid duplicates.');
            return back();
        }

        $sourceDate = LivePrice::whereDate('created_at', '<', $today)
            ->orderBy('created_at', 'desc')
            ->value('created_at');

        if (! $sourceDate) {
            Session::flash('error', 'Error|No previous day live prices found to clone.');
            return back();
        }

        $sourceDay = Carbon::parse($sourceDate)->toDateString();
        $now = Carbon::now()->toDateTimeString();
        $columns = [
            'tradeFor',
            'farmingType',
            'name',
            'form',
            'cropGrade',
            'cropYear',
            'min_price',
            'max_price',
            'state',
            'up_down',
            'opening',
            'closing',
            'monthStart',
            'monthEnd',
            'status',
            'state_order',
            'name_order',
            'form_order',
        ];

        $cloned = 0;

        LivePrice::query()
            ->select(array_merge(['id'], $columns))
            ->whereDate('created_at', $sourceDay)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($columns, $now, &$cloned) {
                $payload = $rows->map(function ($row) use ($columns, $now) {
                    $data = [];
                    foreach ($columns as $column) {
                        $data[$column] = $row->getAttribute($column);
                    }
                    $data['created_at'] = $now;
                    $data['updated_at'] = $now;

                    return $data;
                })->all();

                if ($payload !== []) {
                    LivePrice::insert($payload);
                    $cloned += count($payload);
                }
            });

        if ($cloned === 0) {
            Session::flash('error', 'Error|No previous day live prices found to clone.');
            return back();
        }

        Session::flash('success', 'Success|Price cloned successfully from ' . $sourceDay . ' (' . $cloned . ' rows).');
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
