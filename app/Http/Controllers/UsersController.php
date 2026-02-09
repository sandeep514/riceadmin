<?php

namespace App\Http\Controllers;

use App\DataTables\UsersDataTable;
use App\DataTables\AllUsersDatatable;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\User;
use App\ChatStatus;
use Illuminate\Http\Request;
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
        $user = User::with(['getWebPersonalDetails','getWebBusinessDetails' => function($q){
            return $q->with(['cityRel:id,city_name' , 'stateRel:id,state_name']);
        } , 'getWebUserAttachment' , 'getWebUserSubscription'])->find($userId)->toArray();

        return view('users.view',['user' => $user]);
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
