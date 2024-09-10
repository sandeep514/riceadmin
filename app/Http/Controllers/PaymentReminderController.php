<?php

namespace App\Http\Controllers;

use App\DataTables\PaymentReminderDataTable;
use App\Http\Requests\PaymentReminderRequest;
use Illuminate\Http\Request;

class PaymentReminderController extends Controller
{
    public function index(PaymentReminderDataTable $dataTablet){
        return $dataTablet->render('payment-reminder.index');
    }

    public function create(){
        return view('payment-reminder.create');
    }

    public function save(PaymentReminderRequest $request){

    }
}
