<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\BuyQuery;
use App\Bid;
use App\RiceName;
use App\RiceForm;
use App\Notification;
use Illuminate\Http\Request;
use App\LivePrice;
use Carbon\Carbon;
use Excel;
use App\Export\LivePricesExport;
use Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function riceQualityMaster()
    {
        $buyQueries = BuyQuery::with(['getBids' => function($query){
            return $query->with(['seller'])->get();
        }])->orderBy('id' , 'DESC')->get();

        
        
        return view('riceQueries.index' , compact('buyQueries'));
    }
    public function updateRiceQualityMaster(Request $request)
    {   
        BuyQuery::where('id' , $request->buyqueryid)->update(['length' => $request->Length,
            'purity' => $request->Purity,
            'moisture' => $request->Moisture,
            'broken' => $request->Broken,
            'kett' => $request->Kett,
            'dd' => $request->DDs
        ]);
        return back()->withMessage('success' , 'Specs added successfully');
    }
    public function riceQualityMasterAccept($id)
    {
        $bidDetail = Bid::where('id' , $id)->first();
        $seller_id = $bidDetail->seller_id;

        $updateBid = Bid::where('id' , $id)->update(['accept_status' => 1]);

        Notification::create([
            'user_id' => $seller_id,
            'title' => 'Offer accepted',
            'message' => 'Admin accepted your offer'
        ]);

        return back();
    }
    public function activateQuery($id)
    {
        $updateQueryStatus = BuyQuery::where('id' , $id)->update(['status' => 1]);
        return back()->withMessage('success' , 'Query Activated successfully');

    }

    public function updateQueryMasterStatusAsSold($id)
    {
        $updateQueryStatus = BuyQuery::where('id' , $id)->update(['status' => 2]);
        return back()->withMessage('success' , 'Status Updated successfully');
    }

    public function striprcheckout(Request $request)
    {

        require_once(asset('stripephp/init.php'));

        $stripe = new \Stripe\StripeClient('pk_test_TYooMQauvdEDq54NiTphI7jx');
            $charge = $stripe->charges->create([
                'amount' => 2000,
                'currency' => 'usd',
                'source' => 'tok_visa',
                'description' => 'My First Test Charge (created for API docs at https://www.stripe.com/docs/api)',
            ]);
            echo $charge;
    }
    public function riceNameOrder()
    {
        $data = RiceName::all();
        return view('changeOrders.rice.index' , compact('data'));
    }
    public function updateRiceNameOrder(Request $request)
    {
        $order = $request['order'];
        $nameId = $request['nameId'];
        RiceName::where(['id' => $nameId])->update(['order' => $order]);
        return back()->withMessage('success' , 'Order updated successfully');

    }


    public function riceFormOrder()
    {
        $data = RiceForm::orderBy('order')->get();
        return view('changeOrders.riceForm.index' , compact('data'));
    }
    public function updateRiceFormOrder(Request $request)
    {
        $order = $request['order'];
        $nameId = $request['nameId'];
        RiceForm::where(['id' => $nameId])->update(['order' => $order]);
        return back()->withMessage('success' , 'Order updated successfully');
    }
    public function getLatestPrices()
    {   
        $fileName = Carbon::now()->format('dmY')."_pricelist.xlsx";
        Excel::store(new LivePricesExport, $fileName, 'publicexcel');

        $date= Carbon::now()->format('d-m-Y');

        // $emails = ['sandy.singh51480@gmail.com'];
        $emails = ['sandy.singh51480@gmail.com','enquiry@sntcgroup.com'];
        // $emails = ['sandy.singh51480@gmail.com','rbajaj@sntcgroup.com','enquiry@sntcgroup.com'];

        Mail::send('mail.mailToVendor', ['date' => $date], function($message) use ($emails,$fileName)
        {
            $message->from('info@sntcgroup.com');
            $message->to($emails)->subject('SNTC : Indian Rice Live Pricing');
            $message->attach(public_path('/excel/'.$fileName));
           
        });
    }
   
}
 