<?php


namespace App\Repositories;

use App\Buyer;
use App\FieldRunner;
use App\Seller;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository
{
    public static function saveUser($request){
       if(request()->role == 4 || request()->role == 5){
            $userModel = new User;
            $userModel->fill($request->except(['password']));
            $userModel->password = Hash::make('password');
            $userModel->role = request()->role;
            $userModel->api_token = Hash::make($request->name);
            $userModel->state = $request->state;
            $userModel->save();
            if(request()->role == 4){
                self::createSellerAndBuyer($request,$userModel,'seller');
            }else{
                self::createSellerAndBuyer($request,$userModel,'buyer');
            }
            return $userModel;
        }elseif(request()->role == 3){
            $userModel = new User;
            $userModel->fill($request->except(['password','email']));
            if(isset($request->email)){
                $userModel->email = $request->email;
            }else{
                $userModel->email = $request->name.'@'.Str::random(5).'.com';
            }
            $userModel->password = Hash::make($request->password);
            $userModel->role = request()->role;
            $userModel->api_token = Hash::make($request->name);
            $userModel->save();
            self::createFieldRunner($request,$userModel);
            return $userModel;
        }else{
           $userModel = new User;
           $userModel->fill($request->except(['password']));
           $userModel->password = Hash::make($request->password);
           $userModel->role = request()->role;
           $userModel->api_token = Hash::make($request->name);
           $userModel->save();
           return $userModel;
       }
    }

    public static function createSellerAndBuyer($request,$userModel,$type){
        if($type == 'seller'){
            $sellerOrBuyerModel = new Seller;
        }else{
            $sellerOrBuyerModel = new Buyer;
        }
        $sellerOrBuyerModel->user_id = $userModel->id;
        $sellerOrBuyerModel->company_name = $request->company;
        $sellerOrBuyerModel->contact_person = $request->contact_person;
        $sellerOrBuyerModel->email_ids = json_encode(array_combine($request->email_of,$request->email_id));
        $sellerOrBuyerModel->save();
    }

    public static function updateSellerAndBuyer($request, $userModel, $type){
        if($type == 'seller'){
            $sellerOrBuyerModel = Seller::whereUserId($userModel->id)->first();
        }else{
            $sellerOrBuyerModel = Buyer::whereUserId($userModel->id)->first();
        }
        $sellerOrBuyerModel->user_id = $userModel->id;
        $sellerOrBuyerModel->company_name = $request->company;
        $sellerOrBuyerModel->contact_person = $request->contact_person;
        $sellerOrBuyerModel->email_ids = json_encode(array_combine($request->email_of,$request->email_id));
        $sellerOrBuyerModel->save();
    }

    public static function updateUser($request,$id){

        if(request()->role == 4 || request()->role == 5){
            $userModel = User::find($id);
            $userModel->fill($request->except(['password']));
            $userModel->password = Hash::make('password');
            $userModel->role = request()->role;
            $userModel->api_token = Hash::make($request->name);
            $userModel->state = $request->state;
            $userModel->save();
            if(request()->role == 4){
                self::createSellerAndBuyer($request,$userModel,'seller');
            }else{
                self::createSellerAndBuyer($request,$userModel,'buyer');
            }
            return $userModel;
        }elseif(request()->role == 3){
            $userModel = User::find($id);
            $userModel->fill($request->except(['password']));
            if($request->has('password')){
                $userModel->password = Hash::make($request->password);
            }
            $userModel->role = request()->role;
            $userModel->api_token = Hash::make($request->name);
            $userModel->save();
            self::updateFieldRunner($request,$userModel);
            return $userModel;
        }else{
            $userModel = User::find($id);
            $userModel->fill($request->except(['password']));
            if($request->has('password')) {
                $userModel->password = Hash::make($request->password);
            }
            $userModel->role = request()->role;
            $userModel->api_token = Hash::make($request->name);
            $userModel->save();
            return $userModel;
        }
    }

    public static function createFieldRunner($request,$userModel){
        $fieldRunnerModel = new FieldRunner;
        $fieldRunnerModel->user_id = $userModel->id;
        $fieldRunnerModel->zone = $request->zone;
        $fieldRunnerModel->designation = $request->designation;
        $fieldRunnerModel->save();
    }
    public static function updateFieldRunner($request,$userModel){
        $fieldRunnerModel = FieldRunner::where(['user_id'=>$userModel->id])->first();
        $fieldRunnerModel->user_id = $userModel->id;
        $fieldRunnerModel->zone = $request->zone;
        $fieldRunnerModel->designation = $request->designation;
        $fieldRunnerModel->save();
    }
}
