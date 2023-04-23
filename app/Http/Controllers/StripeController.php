<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe;
use Session;
use App\User;

class StripeController extends Controller
{
    public function handleGet()
    {
        return view('home');
    }
  
    public function checkIfCustomer(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $user = User::where([ 'email' => $request->email ])->first();
        if($user != null){
            if( $user->stripe_customer_id != null ){
                $source = \Stripe\Customer::createSource(
                    $user->stripe_customer_id,
                    ['source' => $request->stripeToken]
                );
                return response()->json(['' => true , 'stripeCustomer' => $user->stripe_customer_id,'source' => $source]) ;
            }else{
                // $paymentMethodClassObject = \Stripe\PaymentMethod;
                // $PaymentMethodObject = $paymentMethodClassObject->attach(
                //     $request->paymentMethodId,
                //     ['customer' => $customer->id ]
                // );
                // print_r($PaymentMethodObject);
                // exit();

                $customer = \Stripe\Customer::create(array(
                    'email' => $request->email,
                    'source' => $request->stripeToken
                ));
                //attaching a source with customer
                $source = \Stripe\Customer::createSource(
                    $customer->id,
                    ['source' => $request->source]
                );
                User::where([ 'email' => $request->email ])->update(['stripe_customer_id' => $customer->id ,'stripe_payment_method' => $request->paymentMethodId ]);

                return response()->json(['' => true , 'stripeCustomer' => $customer->id , 'source' => $source]) ;
            }
        }
    }

    public function handlePost(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $user = User::where([ 'email' => $request->email ])->first();


        $stripeStatus = Stripe\Charge::create ([
            "amount" => ($request->amount*100),
            "currency" => "USD",
            'customer' => $user->stripe_customer_id,
            "source" => $request->source,
            "description" => "Making test payment."
        ]);
                return response()->json(['data' => $stripeStatus]);
        
        print_r($stripeStatus);
        // exit();
  
        // Session::flash('success', 'Payment has been successfully processed.');
          
        // return back();
    }
}