<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PaytmWallet;
use Auth;
use App\PayGIntegration;
use App\User;
use App\SubscriptionPlan;
use App\PaymentTransaction;
use App\Membership;
use App\Business;
use App\NotificationList;
use PaytmWalletProvider;
use Validator;
use Log;
use Carbon\Carbon;
use Helpers;

class OrderPayGController extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->loggedInUser = Auth::guard();
    }

    /**
    * Creates Order and Sends the request to paytm.
    * Parameters: Token, Plan ID
    * Date: 24/07/2019
    * Developed By: Jaydeep Rajgor
    */
	public function order(Request $request)
	{

//         $payg = new PayGIntegration();
//         $resonseOrderData1 = $payg->orderDetail('201222M7990Ua22e06034e');
//         $resonseOrderData1 = json_decode($resonseOrderData1, false);
//         $resonseOrderData1 = collect($resonseOrderData1);
// dd($resonseOrderData1);
        $validator=Validator::make($request->all(),[
            'plan_id'=>'required|numeric|exists:subscription_plans,id'
        ]);
        if($validator->fails())
        {
            return response()->json([
                    'status'=>0,
                    'message'=>$validator->messages()->all()[0]
                ],400);
        }
		$input=$request->all();
		$input['order_id']='RYUVA'.uniqid();
		$input['user']=Auth::id();
        $input['mobile_no']=User::where('id',$input['user'])->pluck('phone')->first();
		$input['business_id']=Business::where('user_id',$input['user'])->pluck('id')->first();        
		$input['email']=User::where('id',$input['user'])->pluck('email')->first();
		$input['plan_amount']=SubscriptionPlan::where('id',$request->plan_id)->pluck('price')->first();
        $transaction=new PaymentTransaction();
        $transaction->user_id=$input['user'];
        $transaction->plan_id=$request->plan_id;
        $transaction->order_id=$input['order_id'];
        $transaction->business_id=$input['business_id'];


        $payg = new PayGIntegration();        
        // call class object to order create function
        $resonseOrderData = $payg->orderCreate([
          'email' => $input['email'],
          'OrderAmount' => $input['plan_amount'],
          'callback_url' => url('api/payment/status'),
          'OrderId' => $input['order_id']
        ]);

        
        // handle the json object response
        $resonseOrderData = json_decode($resonseOrderData, false);
        $resonseOrderData = collect($resonseOrderData);

        $transaction->OderKeyId = $resonseOrderData['OrderKeyId'];
        $transaction->UniqueRequestId = $resonseOrderData['UniqueRequestId'];
        $transaction->save();


        // $payg = new PayGIntegration();
        // $resonseOrderData1 = $payg->orderUpdate($resonseOrderData, '201222M7990Uf23fb1d51b');
        // $resonseOrderData1 = json_decode($resonseOrderData1, false);
        // $resonseOrderData1 = collect($resonseOrderData1);
        

        // return $resonseOrderData['OrderKeyId'];
       // echo "<pre>";  print_r($resonseOrderData);
        // return response()->json($resonseOrderData);
         return response()->json([
                    'status'=>1,
                    'message'=>'Checksum and order id generated successfully',
                    'data'=> $resonseOrderData
                ],200);
         // return $resonseOrderData;
	}

    public function orderUpdate(Request $request){

        
        $orderDetail = PaymentTransaction::where('order_id',$request->order_id)->first();

        $payg = new PayGIntegration();
        $resonseOrderData = $payg->orderDetail($orderDetail->OderKeyId);
        $resonseOrderData = json_decode($resonseOrderData, false);
        $resonseOrderData = collect($resonseOrderData);
        // return  $resonseOrderData;
        // $payg = new PayGIntegration();
        // $resonseOrderData1 = $payg->orderUpdate($resonseOrderData, $orderDetail->OderKeyId);
        // $resonseOrderData1 = json_decode($resonseOrderData1, false);
        // $resonseOrderData1 = collect($resonseOrderData1);

        $updatePyamentStatus = PaymentTransaction::where('order_id',$request->order_id)->first();
        $updatePyamentStatus->transaction_id = $resonseOrderData['PaymentTransactionId'];
        $updatePyamentStatus->status_message = $request->status;

        if($resonseOrderData['OrderStatus'] == 1){
             
            $updatePyamentStatus->status = 1;
                
            $outputArray['status']=1;
            $outputArray['transaction_status']='Successful Transaction';

        }else if($resonseOrderData['OrderStatus'] == 2){
            $updatePyamentStatus->status = 2;
                
            $outputArray['status']=2;
            $outputArray['transaction_status']='Declined Transaction';

        }else if($resonseOrderData['OrderStatus'] == 3){

            $updatePyamentStatus->status = 3;
                
            $outputArray['status']=3;
            $outputArray['transaction_status']='Partial Approved transactions';
        }else if($resonseOrderData['OrderStatus'] == 4){

            $updatePyamentStatus->status = 4;
                
            $outputArray['status']=4;
            $outputArray['transaction_status']='Pending Payment';
        }else if($resonseOrderData['OrderStatus'] == 5){

            $updatePyamentStatus->status = 5;
                
            $outputArray['status']=5;
            $outputArray['transaction_status']='Cancelled Payment';
        }else if($resonseOrderData['OrderStatus'] == 6){

            $updatePyamentStatus->status = 6;
                
            $outputArray['status']=6;
            $outputArray['transaction_status']='Cancelled';
        }else if($resonseOrderData['OrderStatus'] == 7){

            $updatePyamentStatus->status = 7;
                
            $outputArray['status']=7;
            $outputArray['transaction_status']='Expired';
        }

        $updatePyamentStatus->update();

        return response()->json([
                    'status'=>1,
                    'message'=>'successfully',
                    'data'=> $outputArray
                ],200);
    }

        // }
		// 
		// $input['order_id']='RAJASTHAN'.uniqid();
		// $input['user']=Auth::id();
		// $input['mobile_no']=User::where('id',$input['user'])->pluck('phone')->first();
		// $input['email']=User::where('id',$input['user'])->pluck('email')->first();
		// $input['plan_amount']=Subscription  

public function payg_payment(Request $request)
    {


        $validator=Validator::make($request->all(),[
            'plan_id'=>'required|numeric|exists:subscription_plans,id'
        ]);
        if($validator->fails())
        {
            return response()->json([
                    'status'=>0,
                    'message'=>$validator->messages()->all()[0]
                ],400);
        }
        $input=$request->all();
        $input['order_id']='RYUVA'.uniqid();
        $input['user']=Auth::id();
        $input['mobile_no']=User::where('id',$input['user'])->pluck('phone')->first();
        $input['email']=User::where('id',$input['user'])->pluck('email')->first();
        $input['plan_amount']=SubscriptionPlan::where('id',$request->plan_id)->pluck('price')->first();
        $transaction=new PaymentTransaction();
        $transaction->user_id=$input['user'];
        $transaction->plan_id=$request->plan_id;
        $transaction->order_id=$input['order_id'];
        $transaction->save();


        // $validator=Validator::make($request->all(),[
            // 'plan_id'=>'required|numeric|exists:subscription_plans,id'
        // ]);
        // if($validator->fails())
        // {
            // return response()->json([
                    // 'status'=>0,
                    // 'message'=>$validator->messages()->all()[0]
                // Plan::where('id',$request->plan_id)->pluck('price')->first();
        // $transaction=new PaymentTransaction();
        // $transaction->user_id=$input['user'];
        // $transaction->plan_id=$request->plan_id;
        // $transaction->order_id=$input['order_id'];
        // $transaction->save();

		// $payment = PaytmWallet::with('receive');
  //       $payment->prepare([
  //         'order' => $input['order_id'],
  //         'user' => $input['user'],
  //         'mobile_number' => $input['mobile_no'],
  //         'email' => $input['email'],
  //         'amount' => $input['plan_amount'],
  //         'callback_url' => url('api/payment/status')
  //       ]);
  //$input=$request->all();
	 //echo "<pre>"; print_r($request->all());die();

//echo "<pre>"; print_r($input); die();
        $payg = new PayGIntegration();        
        // call class object to order create function
       



             
        $resonseOrderData = $payg->orderCreate([
          'email' => $input['email'],
          'OrderAmount' => $input['plan_amount'],
          'callback_url' => url('api/payment/status')
        ]);
        // handle the json object response
        $resonseOrderData = json_decode($resonseOrderData, false);
       //echo "<pre>";  print_r(  $resonseOrderData);
        //return $payment->receive();
		
		$outputArray['status']=1;
        $outputArray['message']='Checksum and order id generated successfully';
        $outputArray['data']=array();
        $outputArray['data']['checksum_hash']=$resonseOrderData;
       
        return response()->json($outputArray);
	}

  
}
