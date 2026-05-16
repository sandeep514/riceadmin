<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\DataTables\AllUsersDatatable;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\User;
use App\UserInterestedMap;
use App\ChatStatus;
use App\Services\UserInterestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;
use Mail;


class UsersController extends Controller
{

    public function index($role )
    {
        $users = User::where('role' , $role)->get();
        return View('users.users',compact('users'));
        // return $dataTable->render('users.index');
    }

    // public function index(UsersDataTable $dataTable )
    // {
    //     return $dataTable->render('users.index');
    // }

    public function getVendors()
    {
        $vendorUsers = User::where('bagCategory' , '!=' , 0 )->with('bagVendor')->get();
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
        $userModel = User::with(['getWebPersonalDetails', 'getWebBusinessDetails' => function ($q) {
            return $q->with(['cityRel:id,city_name', 'stateRel:id,state_name', 'getCategoryDetails:id,category']);
        }, 'getWebUserAttachment', 'getWebUserSubscription', 'role_rel:id,role_name'])->find($userId);

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

        return view('users.view', [
            'user' => $userArray,
            'interestedMaps' => $interestedMaps,
            'interestEditRows' => $interestEditRows,
            'canAdminManageInterests' => $canAdminManageInterests,
            'canEditByAdmin' => $canEditByAdmin,
        ]);
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
        $userId = $request->userId;
        $mailmessage = $request->message ?? 'No reason added by admin';

        User::where( ['id' => $userId  ])->update([ 'message' => $mailmessage,'has_validation' => $mailmessage , 'status' => 0 ]);
        $userDetail = User::where( ['id' => $userId  ])->first();

        $data = [ 'userName' => $userDetail['name'] , 'mailmessage' => $mailmessage ] ; 

        $mailTo = $userDetail->email;
        $subject = 'Account on Hold';
        $mailFrom = 'info@sntcgroup.com';
        $mailFromName = 'SNTC Team - India';

        $respose = Mail::send('mail.rejectedUserMail', $data, function ($message) use ($mailTo, $mailmessage, $subject, $mailFrom, $mailFromName) {
            $message->to($mailTo, $mailmessage)->subject($subject);
            $message->from($mailFrom, $mailFromName);
        });
        Session::flash('success','Success|User rejected successfully!');
        return back();

    }

    public function listWebChangeSttausUser($userId)
    {
        $user = User::where('id' , $userId);
        $userDetail = $user->first();

        $user->update([ 'is_active_by_admin' => ($userDetail->is_active_by_admin) ? 0 : 1]);

        if($userDetail->is_active_by_admin == 0){
            $data = [];
            $data['user_name'] = $userDetail->name;

            $mailTo = $userDetail->email;
            $mailMessage = '';
            $subject = 'User Activated';
            $mailFrom = 'info@sntcgroup.com';
            $mailFromName = 'SNTC Team - India';

            $user->update([ 'has_validation' => '']);

            $respose = Mail::send('mail.activeUserMail', $data, function ($message) use ($mailTo, $mailMessage, $subject, $mailFrom, $mailFromName) {
                $message->to($mailTo, $mailMessage)->subject($subject);
                $message->from($mailFrom, $mailFromName);
            });
        }

        Session::flash('success','Success|User status updated successfully!');
        return back();
    }

    public function webusers()
    {
        $vendorUsers = User::where('user_from' , 'web')->with(['getWebPersonalDetails' , 'getWebBusinessDetails' => function($q){
            return $q->with(['getCategoryDetails:id,category']);
        }])->get();
        return view('users.webUser' , compact('vendorUsers'));
    }

    public function markAsViewed()
    {
        User::where(['user_from' => 'web', 'is_viewed_by_admin' => 0])->update(['is_viewed_by_admin' => 1]);
        Session::flash('success' , 'Success|Users marked as viewed');
        return back();

    }
}
