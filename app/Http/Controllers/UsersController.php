<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\DataTables\AllUsersDatatable;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\User;
use App\UserInterestedMap;
use App\VendorUserMap;
use App\ServiceProviderUserMap;
use App\WebBusinessDetails;
use App\ChatStatus;
use App\Services\UserInterestService;
use App\Services\WebPortalNotificationDelivery;
use App\Support\VendorProductCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Session;
use Mail;
use App\Support\QueuedMail;


class UsersController extends Controller
{

    public function index($role )
    {
        $users = User::where('role', $role)->orderBy('id', 'desc')->get();
        return View('users.users',compact('users'));
        // return $dataTable->render('users.index');
    }

    // public function index(UsersDataTable $dataTable )
    // {
    //     return $dataTable->render('users.index');
    // }

    public function getVendors()
    {
        $vendorUsers = User::where('bagCategory', '!=', 0)->with('bagVendor')->orderBy('id', 'desc')->get();
        return View('users.vendors',compact('vendorUsers'));
    }


    public function create()
    {
        return view('users.create');
    }


    public function save(UserRequest $request)
    {
        UserService::saveUser($request);
        Session::flash('success','Success|Record Saved Successfully!');
        return back();
    }


    public function show($id)
    {
    }


    public function edit($id)
    {
        $userModel = User::with(['field_runner_rel','seller_rel','buyer_rel'])->find($id);
        if($userModel == null){
            Session::flash('error','Error|No record found!');
            return back();
        }
        if(request()->route('role') == 4){
            $userModel->company = $userModel->seller_rel->company_name;
            $userModel->contact_person = $userModel->seller_rel->contact_person;
            $userModel->email_ids = json_decode($userModel->seller_rel->email_ids,true);
        }elseif(request()->route('role') == 5){
            $userModel->company = $userModel->buyer_rel->company_name;
            $userModel->contact_person = $userModel->buyer_rel->contact_person;
            $userModel->email_ids = json_decode($userModel->buyer_rel->email_ids,true);
        }
        return view('users.edit',['model'=>$userModel]);
    }


    public function update(UserRequest $request, $id)
    {
        UserService::updateUser($request, $id);
        Session::flash('success','Success|Record Updated Successfully!');
        return redirect()->route('users',request()->route('role'));
    }


    public function delete($id)
    {
        $sellerModel = User::find($id);
        if($sellerModel == null){
            Session::flash('error','Error|Something went wrong');
            return back();
        }
        $sellerModel->delete();
        Session::flash('success','Success|Record deleted successfully!');
        return back();
    }

    public function deleteWebUserWithPin(Request $request, $id)
    {
        // Temporarily disabled — do not hard-delete web users from admin.
        Session::flash('error', 'Error|Delete Web User is temporarily disabled. Use Reject / On Hold instead.');

        return back();

        /*
        try {
            $request->validate([
                'pin' => ['required', 'string', 'in:22334455'],
            ], [
                'pin.in' => 'Invalid security PIN.',
            ]);
        } catch (ValidationException $e) {
            Session::flash('error', 'Error|' . ($e->validator->errors()->first('pin') ?: 'Invalid security PIN.'));
            return back();
        }

        $user = User::find($id);
        if (! $user) {
            Session::flash('error', 'Error|User not found.');
            return back();
        }

        if ((string) ($user->user_from ?? '') !== 'web') {
            Session::flash('error', 'Error|Only web users can be deleted from this action.');
            return back();
        }

        // Deactivate vendor catalog products before removing the account.
        VendorProductCatalog::deactivateVerifiedProductsForUser((int) $user->id);

        // Hide the business/listing row before removing the account so the
        // vendor list and other web consumers cannot surface a deleted user.
        WebBusinessDetails::where('user_id', $user->id)->update(['is_active_listing' => 0]);

        $role = $user->role;
        $user->delete();
        Session::flash('success', 'Success|Web user deleted successfully. Active products were deactivated.');

        return redirect()->route('users', $role);
        */
    }
    
    public function changeChatStatus(Request $request){
        ChatStatus::where('id' , 1)->update(['status' => $request->status]);
        return back();
    }
    public function getTotalUsers( AllUsersDatatable $dataTable )
    {
        return $dataTable->render('users.allUsers');
    }
    public function getTotalUsersWithDateFilter( AllUsersDatatable $dataTable,Request $request )
    {
        return $dataTable->render('users.allUsers' );
    }

    public function view($userId)
    {
        $userModel = User::with([
            'getWebPersonalDetails' => function ($q) {
                $q->with(['stateRel:id,state_name,state_code', 'districtRel:id,city_name,state_id']);
            },
            'getWebBusinessDetails' => function ($q) {
                return $q->with(['cityRel:id,city_name', 'stateRel:id,state_name', 'getCategoryDetails:id,category']);
            },
            'getWebUserAttachment',
            'getWebUserSubscription',
            'role_rel:id,role_name',
        ])->find($userId);

        if (! $userModel) {
            abort(404);
        }

        $interestedMaps = UserInterestedMap::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->with(['riceName', 'riceForm', 'wandGrade.getWandType'])
            ->orderBy('rice_name_id')
            ->orderBy('form_id')
            ->orderByRaw('grade IS NULL, grade')
            ->get();

        $interestEditRows = $interestedMaps
            ->groupBy(fn ($m) => $m->rice_name_id.'_'.$m->form_id)
            ->map(function ($group) {
                $r = $group->first();

                return [
                    'rice_type' => optional($r->riceName)->type ?? '',
                    'name_id' => (int) $r->rice_name_id,
                    'form_id' => (int) $r->form_id,
                    'grades' => $group->pluck('grade')->filter(fn ($g) => $g !== null && $g !== '')->map(fn ($g) => (int) $g)->unique()->values()->all(),
                ];
            })
            ->values()
            ->all();

        $canAdminManageInterests = $userModel->allowsAdminInterestManagement();
        $canEditByAdmin = $userModel->canEditByAdminFlag();

        if ($canAdminManageInterests && $interestEditRows === []) {
            $interestEditRows = [
                ['rice_type' => '', 'name_id' => '', 'form_id' => '', 'grades' => []],
            ];
        }

        $userArray = $userModel->toArray();
        $userArray['can_edit_by_admin'] = $canEditByAdmin;

        $roleId = (int) ($userModel->role ?? 0);
        $vendorProfileMaps = collect();
        $vendorProfileType = null;
        if ($roleId === 11) {
            $vendorProfileType = 'vendor';
            $vendorProfileMaps = VendorUserMap::query()
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->get();
        } elseif ($roleId === 12) {
            $vendorProfileType = 'service_provider';
            $vendorProfileMaps = ServiceProviderUserMap::query()
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->get();
        }

        return view('users.view', [
            'user' => $userArray,
            'interestedMaps' => $interestedMaps,
            'interestEditRows' => $interestEditRows,
            'canAdminManageInterests' => $canAdminManageInterests,
            'canEditByAdmin' => $canEditByAdmin,
            'vendorProfileMaps' => $vendorProfileMaps,
            'vendorProfileType' => $vendorProfileType,
        ]);
    }

    public function updateVendorProfileRecommended(Request $request, $userId)
    {
        $user = User::find($userId);
        if (! $user) {
            Session::flash('error', 'Error|User not found.');

            return redirect()->back();
        }

        $roleId = (int) ($user->role ?? 0);
        if (! in_array($roleId, [11, 12], true)) {
            Session::flash('error', 'Error|This action is only available for vendor or service provider users.');

            return redirect()->back();
        }

        $validator = Validator::make($request->all(), [
            'map_id' => ['required', 'integer', 'min:1'],
            'is_sntc_recommended' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Error|' . $validator->errors()->first());

            return redirect()->back();
        }

        $mapId = (int) $request->input('map_id');
        $mapModel = $roleId === 11 ? VendorUserMap::class : ServiceProviderUserMap::class;
        $map = $mapModel::query()
            ->where('id', $mapId)
            ->where('user_id', (int) $userId)
            ->first();

        if (! $map) {
            Session::flash('error', 'Error|Profile record not found for this user.');

            return redirect()->back();
        }

        $map->update([
            'is_sntc_recommended' => $request->boolean('is_sntc_recommended') ? 1 : 0,
        ]);

        Session::flash('success', 'Success|SNTC recommended status updated successfully.');

        return redirect()->back();
    }

    public function updateBusinessDetailsRecommended(Request $request, $userId)
    {
        $user = User::find($userId);
        if (! $user) {
            Session::flash('error', 'Error|User not found.');

            return redirect()->back();
        }

        $businessDetails = WebBusinessDetails::query()
            ->where('user_id', (int) $userId)
            ->first();

        if (! $businessDetails) {
            Session::flash('error', 'Error|Business details not found for this user.');

            return redirect()->back();
        }

        $updateField = (string) $request->input('update_field', '');

        if ($updateField === 'is_sntc_recommended') {
            $businessDetails->update([
                'is_sntc_recommended' => $request->boolean('is_sntc_recommended') ? 1 : 0,
            ]);
            Session::flash('success', 'Success|SNTC recommended status updated successfully.');

            return redirect()->back();
        }

        if ($updateField === 'is_active_listing') {
            $businessDetails->update([
                'is_active_listing' => $request->boolean('is_active_listing') ? 1 : 0,
            ]);
            Session::flash('success', 'Success|Active listing status updated successfully.');

            return redirect()->back();
        }

        $businessDetails->update([
            'is_sntc_recommended' => $request->boolean('is_sntc_recommended') ? 1 : 0,
            'is_active_listing' => $request->boolean('is_active_listing') ? 1 : 0,
        ]);

        Session::flash('success', 'Success|Business details updated successfully.');

        return redirect()->back();
    }

    private function userAllowsAdminInterestManagement(User $user): bool
    {
        $fresh = $user->fresh();

        return $fresh ? $fresh->allowsAdminInterestManagement() : false;
    }

    /**
     * Admin: add new rice interest rows only (does not remove existing; duplicates skipped).
     */
    public function saveUserInterests(Request $request, $userId)
    {
        $user = User::find($userId);
        if (! $user) {
            Session::flash('error', 'Error|User not found.');

            return redirect()->back();
        }

        if (! $this->userAllowsAdminInterestManagement($user)) {
            Session::flash('error', 'Error|This user manages their own interests. Admin cannot add or change them.');

            return redirect()->route('view.user', $userId);
        }

        $interested = $request->input('interested', []);
        if (! is_array($interested)) {
            $interested = [];
        }

        $cleaned = [];
        foreach ($interested as $row) {
            if (! is_array($row)) {
                continue;
            }
            $nid = isset($row['name_id']) ? (int) $row['name_id'] : 0;
            $fid = isset($row['form_id']) ? (int) $row['form_id'] : 0;
            if ($nid <= 0 || $fid <= 0) {
                continue;
            }
            $cleaned[] = [
                'name_id' => $nid,
                'form_id' => $fid,
                'grades' => $row['grades'] ?? null,
            ];
        }

        if (count($cleaned) > 0) {
            $validator = Validator::make(
                ['interested' => $cleaned],
                [
                    'interested' => 'required|array|min:1',
                    'interested.*.name_id' => 'required|exists:rice_names,id',
                    'interested.*.form_id' => 'required|exists:rice_form_milestone3,id',
                    'interested.*.grades' => 'nullable|array',
                    'interested.*.grades.*' => 'integer|exists:wand,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->route('view.user', $userId)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        if (count($cleaned) === 0) {
            Session::flash('error', 'Error|Nothing to add — select rice name and form on at least one line. Existing interests are kept. Use Delete in the table above to remove saved rows.');

            return redirect()->route('view.user', $userId);
        }

        try {
            $added = UserInterestService::appendUniqueInterestsForUser((int) $userId, $cleaned);
            if ($added === 0) {
                Session::flash('success', 'Success|No new combinations to add (already on file). Existing rows were unchanged.');
            } else {
                Session::flash('success', 'Success|Added '.$added.' new interest row(s). Previous entries were kept.');
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Error|Could not save interests: '.$e->getMessage());
        }

        return redirect()->route('view.user', $userId);
    }

    /**
     * Admin: delete one user_interested_map_table row by id (summary table action).
     */
    public function deleteUserInterestRow(Request $request, $userId)
    {
        $request->validate([
            'map_id' => 'required|integer',
        ]);

        $user = User::find($userId);
        if (! $user) {
            Session::flash('error', 'Error|User not found.');

            return redirect()->back();
        }

        if (! $this->userAllowsAdminInterestManagement($user)) {
            Session::flash('error', 'Error|This user manages their own interests. Admin cannot delete them.');

            return redirect()->route('view.user', $userId);
        }

        $map = UserInterestedMap::query()
            ->where('id', (int) $request->input('map_id'))
            ->where('user_id', (int) $userId)
            ->first();

        if (! $map) {
            Session::flash('error', 'Error|That interest row was not found for this user.');

            return redirect()->route('view.user', $userId);
        }

        $map->delete();
        Session::flash('success', 'Success|Interest row deleted.');

        return redirect()->route('view.user', $userId);
    }

    public function rejectUser(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $userId = (int) $validated['userId'];
        $mailmessage = trim($validated['message']);

        // Notify before clearing tokens so FCM can still reach the device.
        // Notification failures must never block or fail the admin reject action.
        try {
            $this->sendWebUserNotification(
                $userId,
                'Account On Hold',
                'Your account is on hold. Reason: ' . $mailmessage
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Reject user: notification failed.', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        // Keep api_token / mobile_api_token so other open sessions still resolve the user
        // and receive the reject/on-hold message from auth middleware (not "Invalid API token").
        User::where(['id' => $userId])->update([
            'message' => $mailmessage,
            'has_validation' => $mailmessage,
            'is_deactivated' => 1,
            'is_active_by_admin' => 0,
            'user_token' => null,
        ]);

        // Products go back to pending so admin must re-verify after account reactivation.
        // Rewind failures are logged, surfaced as a session warning, never a 500.
        try {
            VendorProductCatalog::deactivateVerifiedProductsForUser($userId);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Reject user: product deactivation failed.', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            Session::flash('warning', 'Rejected|User rejected, but resetting vendor products failed: '.$e->getMessage());
        }

        $userDetail = User::where( ['id' => $userId  ])->first();

        $data = [ 'userName' => $userDetail['name'] , 'mailmessage' => $mailmessage ] ; 

        $mailTo = $userDetail->email;
        $subject = 'Account on Hold';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        // Send user mail asynchronously (queue); a sync-queue SMTP failure is
        // logged, warned in session, and must not turn the reject into a 500.
        try {
            QueuedMail::send('mail.rejectedUserMail', $data, $mailTo, $subject, $mailFrom, $mailFromName, $mailmessage);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Reject user: mail dispatch failed.', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            Session::flash('warning', 'Success|User rejected, but the rejection mail could not be queued: '.$e->getMessage());
        }

        Session::flash('success', 'Success|User rejected successfully! Reason: '.$mailmessage);
        return back();

    }

    private function sendWebUserNotification(int $userId, string $title, string $message): void
    {
        app(WebPortalNotificationDelivery::class)->deliverToUsers(
            [$userId],
            $title,
            $message,
            [
                'audience_mode' => 'individual',
                'push_type' => 'account',
                'fill_role_from_user' => true,
                'fill_category_from_business' => true,
            ]
        );
    }

    public function listWebChangeSttausUser($userId)
    {
        $user = User::where('id', $userId);
        $userDetail = $user->first();

        if (! $userDetail) {
            Session::flash('error', 'Error|User not found.');
            return back();
        }

        $wasPendingActivation = (int) ($userDetail->is_active_by_admin ?? 0) === 0;
        $wasDeactivated = (int) ($userDetail->is_deactivated ?? 0) === 1;

        if ($wasPendingActivation && $userDetail->getWebUserSubscription === null) {
            Session::flash('error', 'Error|This user has no plan. Activate is not allowed.');

            return back();
        }

        if ($wasPendingActivation) {
            $user->update([
                'is_active_by_admin' => 1,
                'is_deactivated' => 0,
                'has_validation' => '',
            ]);
        } elseif ($wasDeactivated) {
            $user->update(['is_deactivated' => 0]);
        } else {
            try {
                $this->sendWebUserNotification(
                    (int) $userId,
                    'Account Deactivated',
                    'Your account has been deactivated . Please contact support if you need help.'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Deactivate user: notification failed.', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }

            // Keep api_token / mobile_api_token so other open sessions still resolve the user
            // and receive the deactivated message from auth middleware (not "Invalid API token").
            $user->update([
                'is_deactivated' => 1,
                'user_token' => null,
            ]);

            // Products go back to pending so admin must re-verify after account reactivation.
            try {
                VendorProductCatalog::deactivateVerifiedProductsForUser((int) $userId);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Deactivate user: product deactivation failed.', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                Session::flash('warning', 'Success|User deactivated, but resetting vendor products failed: '.$e->getMessage());
            }

            $mailTo = $userDetail->email;
            if (! empty($mailTo)) {
                $data = ['user_name' => $userDetail->name];
                $subject = 'Account Deactivated';
                $mailFrom = 'info@sntcgroup.com';
                $mailFromName = 'SNTC Team - India';
                $mailMessage = '';

                try {
                    QueuedMail::send('mail.deactivatedUserMail', $data, $mailTo, $subject, $mailFrom, $mailFromName, $mailMessage);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Deactivate user: mail dispatch failed.', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($wasPendingActivation || $wasDeactivated) {
            $data = [];
            $data['user_name'] = $userDetail->name;

            $mailTo = $userDetail->email;
            $mailMessage = '';
            $subject = 'User Activated';
            $mailFrom = 'info@sntcgroup.com';
            $mailFromName = 'SNTC Team - India';

            try {
                QueuedMail::send('mail.activeUserMail', $data, $mailTo, $subject, $mailFrom, $mailFromName, $mailMessage);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Activate user: mail dispatch failed.', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $this->sendWebUserNotification(
                    (int) $userId,
                    'Account Activated',
                    'Your account has been activated. Welcome aboard!'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Activate user: notification failed.', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Session::flash('success','Success|User status updated successfully!');
        return back();
    }

    public function webusers()
    {
        $request = request();
        $from = trim((string) $request->get('from'));
        $to = trim((string) $request->get('to'));
        $search = $this->resolvedWebUserSearch($request);
        $export = $request->get('export');
        $stage = $this->normalizeWebOnboardingStageKey($request->get('stage'));
        $perPage = (int) $request->get('per_page', 30);
        if (! in_array($perPage, [25, 30, 50, 100], true)) {
            $perPage = 30;
        }

        $stageTabs = $this->webOnboardingStageTabs();
        $stageCounts = $this->webOnboardingStageCounts($from, $to, $search);

        // Full CSV export for the active stage + date/search filter.
        if ($export === 'csv') {
            $exportQuery = $this->webUserBaseQuery($from, $to, $search);
            $this->applyWebOnboardingStageFilter($exportQuery, $stage);
            $exportQuery->with([
                'getWebPersonalDetails',
                'getWebUserSubscription',
                'getWebUserAttachment',
                'getWebBusinessDetails' => function ($q) {
                    return $q->with(['getCategoryDetails:id,category']);
                },
            ]);

            return $this->exportWebUsersCsv($exportQuery, $from, $to, $stage);
        }

        $query = $this->webUserBaseQuery($from, $to, $search);
        $this->applyWebOnboardingStageFilter($query, $stage);
        $query->with([
            'getWebPersonalDetails',
            'getWebUserSubscription',
            'getWebUserAttachment',
            'getWebBusinessDetails' => function ($q) {
                return $q->with(['getCategoryDetails:id,category']);
            },
        ]);

        $vendorUsers = $query
            ->paginate($perPage)
            ->appends($request->except('page'));

        foreach ($vendorUsers as $user) {
            $label = $this->webUserOnboardingStage($user);
            $user->setAttribute('onboarding_stage', $label);
            $user->setAttribute('onboarding_stage_key', $this->webOnboardingStageKeyFromLabel($label));
        }

        return view('users.webUser', compact(
            'vendorUsers',
            'from',
            'to',
            'search',
            'stage',
            'stageCounts',
            'stageTabs',
            'perPage'
        ));
    }

    private function webUserBaseQuery(string $from, string $to, ?string $search = null)
    {
        $query = User::query()
            ->where('user_from', 'web')
            ->orderBy('id', 'desc');

        if ($from !== '') {
            $query->where('created_at', '>=', \Illuminate\Support\Carbon::parse($from, 'Asia/Kolkata')->startOfDay());
        }
        if ($to !== '') {
            $query->where('created_at', '<=', \Illuminate\Support\Carbon::parse($to, 'Asia/Kolkata')->endOfDay());
        }

        $this->applyWebUserSearchFilter($query, $search);

        return $query;
    }

    private function resolvedWebUserSearch($request): ?string
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        if ($search === '') {
            return null;
        }

        return mb_substr($search, 0, 100);
    }

    private function applyWebUserSearchFilter($query, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $like = '%'.$search.'%';
        $query->where(function ($q) use ($like, $search) {
            $q->where('id', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('mobile', 'like', $like)
                ->orWhereHas('getWebBusinessDetails', function ($bq) use ($like) {
                    $bq->where('company_name', 'like', $like)
                        ->orWhere('contactPerson', 'like', $like)
                        ->orWhere('contactMobile', 'like', $like)
                        ->orWhereHas('getCategoryDetails', function ($cq) use ($like) {
                            $cq->where('category', 'like', $like);
                        });
                });

            $status = strtolower($search);
            if (in_array($status, ['active', 'pending', 'rejected', 'deactivated'], true)) {
                if ($status === 'active') {
                    $q->orWhere(function ($inner) {
                        $inner->where('is_active_by_admin', 1)->where(function ($d) {
                            $d->whereNull('is_deactivated')->orWhere('is_deactivated', 0);
                        });
                    });
                } elseif ($status === 'pending') {
                    $q->orWhere(function ($inner) {
                        $inner->where(function ($a) {
                            $a->whereNull('is_active_by_admin')->orWhere('is_active_by_admin', 0);
                        })->where(function ($d) {
                            $d->whereNull('is_deactivated')->orWhere('is_deactivated', 0);
                        });
                    });
                } elseif ($status === 'rejected') {
                    $q->orWhere(function ($inner) {
                        $inner->where('is_deactivated', 1)->where(function ($a) {
                            $a->whereNull('is_active_by_admin')->orWhere('is_active_by_admin', 0);
                        });
                    });
                } elseif ($status === 'deactivated') {
                    $q->orWhere(function ($inner) {
                        $inner->where('is_deactivated', 1)->where('is_active_by_admin', 1);
                    });
                }
            }
        });
    }

    /**
     * @return array<string, int>
     */
    private function webOnboardingStageCounts(string $from, string $to, ?string $search = null): array
    {
        $counts = ['all' => 0, 'otp' => 0, 'profile' => 0, 'documents' => 0, 'trial' => 0, 'paid' => 0];
        foreach (array_keys($counts) as $key) {
            $q = $this->webUserBaseQuery($from, $to, $search);
            $this->applyWebOnboardingStageFilter($q, $key);
            $counts[$key] = (int) $q->count();
        }

        return $counts;
    }

    private function applyWebOnboardingStageFilter($query, string $stage): void
    {
        if ($stage === 'all') {
            return;
        }

        if ($stage === 'paid') {
            $query->whereHas('getWebUserSubscription', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('subscription_type')
                        ->orWhere('subscription_type', '')
                        ->orWhere('subscription_type', '!=', 'trial');
                });
            });

            return;
        }

        if ($stage === 'trial') {
            $query->whereHas('getWebUserSubscription', function ($q) {
                $q->where('subscription_type', 'trial');
            })->whereDoesntHave('getWebUserSubscription', function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNull('subscription_type')
                        ->orWhere('subscription_type', '')
                        ->orWhere('subscription_type', '!=', 'trial');
                });
            });

            return;
        }

        // No subscription for the remaining funnel stages.
        $query->whereDoesntHave('getWebUserSubscription');

        if ($stage === 'documents') {
            $query->whereHas('getWebUserAttachment', function ($q) {
                $this->scopeWebUserHasDocuments($q);
            });

            return;
        }

        if ($stage === 'profile') {
            $query->whereDoesntHave('getWebUserAttachment', function ($q) {
                $this->scopeWebUserHasDocuments($q);
            })->where(function ($q) {
                $q->whereHas('getWebPersonalDetails')
                    ->orWhereHas('getWebBusinessDetails');
            });

            return;
        }

        // otp
        $query->whereDoesntHave('getWebUserAttachment', function ($q) {
            $this->scopeWebUserHasDocuments($q);
        })->whereDoesntHave('getWebPersonalDetails')
            ->whereDoesntHave('getWebBusinessDetails');
    }

    private function scopeWebUserHasDocuments($query): void
    {
        $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNotNull('panCard')->where('panCard', '!=', '');
            })->orWhere(function ($inner) {
                $inner->whereNotNull('farmer_file')->where('farmer_file', '!=', '');
            })->orWhere(function ($inner) {
                $inner->whereNotNull('gst_fssai')->where('gst_fssai', '!=', '');
            })->orWhere(function ($inner) {
                $inner->whereNotNull('gstCard')->where('gstCard', '!=', '');
            })->orWhere(function ($inner) {
                $inner->whereNotNull('fssaiCard')->where('fssaiCard', '!=', '');
            });
        });
    }

    private function exportWebUsersCsv($exportQuery, string $from, string $to, string $stage = 'all')
    {
        @set_time_limit(0);

        $filename = 'web_users_'.($from !== '' ? $from : 'start').'_'.($to !== '' ? $to : 'end')
            .'_'.($stage !== '' ? $stage : 'all').'.csv';

        return response()->streamDownload($this->webUsersCsvWriter($exportQuery), $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function webUsersCsvWriter($exportQuery): \Closure
    {
        return function () use ($exportQuery) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'User ID', 'Name', 'Email', 'Phone', 'Joined At', 'Category', 'Onboarding Stage',
                'SNTC Recommended', 'Status',
            ], ',', '"', '\\');

            $exportQuery->orderBy('id', 'desc')->chunk(500, function ($users) use ($out) {
                foreach ($users as $user) {
                    $stage = $this->webUserOnboardingStage($user);
                    $business = $user->getWebBusinessDetails;
                    $phone = $user->phone ?: $user->mobile;

                    fputcsv($out, [
                        $user->id,
                        (string) ($user->name ?? ''),
                        (string) ($user->email ?? ''),
                        (string) ($phone ?? ''),
                        $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('Y-m-d') : '',
                        (string) ($business->getCategoryDetails->category ?? ''),
                        $stage,
                        $business !== null ? (int) ($business->is_sntc_recommended ?? 0) : '',
                        $user->is_deactivated == 1
                            ? ($user->is_active_by_admin == 1 ? 'Deactivated' : 'Rejected')
                            : ($user->is_active_by_admin == 1 ? 'Active' : 'Pending'),
                    ], ',', '"', '\\');
                }
            });

            fclose($out);
        };
    }

    /**
     * "Where the user left": furthest step of the onboarding journey.
     * OTP login → profile details → documents → trial → paid plan (+ admin activation).
     */
    private function webUserOnboardingStage(User $user): string
    {
        $subscription = $user->getWebUserSubscription;
        $type = $subscription !== null ? (string) ($subscription->subscription_type ?? '') : '';

        if ($user->is_active_by_admin == 1 && $type !== '' && $type !== 'trial') {
            return 'Paid plan — Active';
        }

        if ($type !== '') {
            return $type === 'trial' ? 'Trial started' : 'Paid plan — '.ucfirst($type);
        }

        $attachment = $user->getWebUserAttachment;
        if ($attachment !== null && (
            ! empty(trim((string) $attachment->panCard))
            || ! empty(trim((string) $attachment->farmer_file))
            || ($attachment->resolveGstFssaiRelativePath() ?? '') !== ''
        )) {
            return 'Documents uploaded';
        }

        if ($user->getWebPersonalDetails !== null || $user->getWebBusinessDetails !== null) {
            return 'Profile completed';
        }

        return 'OTP login only';
    }

    /**
     * @return array<string, string> key => label
     */
    private function webOnboardingStageTabs(): array
    {
        return [
            'all' => 'All',
            'otp' => 'OTP login only',
            'profile' => 'Profile completed',
            'documents' => 'Documents uploaded',
            'trial' => 'Trial started',
            'paid' => 'Paid plan',
        ];
    }

    private function normalizeWebOnboardingStageKey($stage): string
    {
        $stage = strtolower(trim((string) $stage));
        $tabs = $this->webOnboardingStageTabs();

        return array_key_exists($stage, $tabs) ? $stage : 'all';
    }

    private function webOnboardingStageKeyFromLabel(string $label): string
    {
        if ($label === 'OTP login only') {
            return 'otp';
        }
        if ($label === 'Profile completed') {
            return 'profile';
        }
        if ($label === 'Documents uploaded') {
            return 'documents';
        }
        if ($label === 'Trial started') {
            return 'trial';
        }
        if (str_starts_with($label, 'Paid plan')) {
            return 'paid';
        }

        return 'otp';
    }

    public function markAsViewed()
    {
        User::where(['user_from' => 'web', 'is_viewed_by_admin' => 0])->update(['is_viewed_by_admin' => 1]);
        Session::flash('success' , 'Success|Users marked as viewed');
        return back();

    }
}
