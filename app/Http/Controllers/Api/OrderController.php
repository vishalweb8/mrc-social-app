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
use App\MembershipRequest;
use Illuminate\Support\Facades\DB;
use Config;

class OrderController extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->loggedInUser = Auth::guard();
        $this->objPaymentTransaction = new PaymentTransaction();
        $this->objMembershipRequest = new MembershipRequest();
        $this->objBusiness = new Business();
    }
    
    /**
     * for create payG payment form android
     *
     * @param  mixed $request
     * @return void
     */
    public function payg_payment_android(Request $request)
    { 
        $validator=Validator::make($request->all(),[
            'plan_id'=>'required|numeric|exists:subscription_plans,id',
            'user_id'=>'required',
            //'membership_type'=>'required',
            //'business_type'=>'required',
            // 'geo_location'=>'required',
            // 'ip_address'=>'required',
        ]);
        if($validator->fails())
        {
            return response()->json([
                    'status'=>0,
                    'message'=>$validator->messages()->all()[0]
                ],400);
        }

        try { 
        $input=$request->all();
        
        $user_id = $request->user_id;
        $plan_id = $request->plan_id;
        $paymentType = $request->input('payment_type','');

        //Log::info("Pay Payment Input Data:".$user_id." ".$plan_id);

        //$redirect_url = $request->redirecturl;
        $insertId = MembershipRequest::firstOrCreate(['subscription_plans_id' =>  $plan_id, 'user_id' => $user_id]);
        $insertId->status = 0;
        $insertId->save();
        $business = $this->objMembershipRequest->getMembershipRequestDetailsById($insertId->id);
        
        $input['plan_amount']=SubscriptionPlan::where('id',$plan_id)->pluck('price')->first();


        $payment_request_id = uniqid().'$'.time().'-'.$user_id;
        
        $users_data = DB::table('users')->find($user_id);

        Log::info("Pay Payment User Data".$users_data->name." ".$users_data->phone);
        $names = $this->getFirstLastName($users_data->name);
        $payg = new PayGIntegration();        
        // call class object to order create function
        $post_data =  array();
        $post_data['OrderAmount'] = $input['plan_amount'];
        $post_data['OrderType'] = 'MOBILE';
        $post_data['Source'] = 'MobileSDK';
        $post_data['IntegrationType'] = '11';
        //$post_data['OrderStatus'] = 'Initiating';
        $post_data['CustomerData']['CustomerId'] = $users_data->id;
        $post_data['CustomerData']['CustomerNotes'] = "";
        $post_data['CustomerData']['FirstName'] = (isset($names[0])) ? $names[0] : '' ;
        $post_data['CustomerData']['LastName'] = (isset($names[1])) ? $names[1] : '' ;
        $post_data['CustomerData']['MobileNo'] = $users_data->phone;
        $post_data['CustomerData']['Email'] = $users_data->email;
        $post_data['CustomerData']['EmailReceipt'] = "";
        $post_data['CustomerData']['BillingAddress'] = "";
        $post_data['CustomerData']['BillingCity'] = "";
        $post_data['IntegrationData']['UserName'] = "";
        $post_data['IntegrationData']['Source'] = "MobileSDK";
        $post_data['IntegrationData']['IntegrationType'] = "11";
        $post_data['IntegrationData']['HashData'] = "";
        $post_data['IntegrationData']['PlatformId'] = "";

        if($request->membership_type == 2) {
            $membershipType = "Lifetime";
        } elseif($request->membership_type == 1) {
            $membershipType = "Premium";
        } else {
            $membershipType = "Basic";
        }

        $productData = [
            'membershipType' => $membershipType,
            //'businessType' => $request->business_type,
            'geoLocation' => $request->geo_location,
            'ipAddress' => $request->ip_address,
        ];
        $post_data['ProductData']= json_encode($productData);
        $post_data['callback_url']= url('api/payment/status');
        //$resonseOrderData = $payg->orderCreateAndroid($post_data,$payment_request_id);  
        $resonseOrderData = $payg->orderCreate($post_data,config('app.Redirect_Url_Mobile'),$paymentType);  
        /* 
        echo "<pre>";
        print_r($resonseOrderData);
        die; */

        // handle the json object response
        $resonseOrderData = json_decode($resonseOrderData, false);
        Log::info("Mobile Order Create Pay Response : ".json_encode($resonseOrderData));
                 
                 /*  echo "<pre>";  
          print_r( $resonseOrderData); 
die; */

        $transaction=new PaymentTransaction();
        $transaction->user_id=$user_id;
        $transaction->plan_id= $plan_id;
        $transaction->order_id=$resonseOrderData->OrderKeyId;
        $transaction->transaction_id=$resonseOrderData->UniqueRequestId;
        $transaction->OderKeyId=$payment_request_id;
        $transaction->save();   
        // start- send mail by helpers function
        $replaceArray = array();
        $replaceArray['SUBJECT'] = trans('constant.membershipRequestSubject');
        $replaceArray['USERNAME'] = $users_data->name;
        $replaceArray['PHONE'] = $users_data->phone;
        $replaceArray['PLAN'] = $business[0]->subscriptionPlan->name;
        $replaceArray['DATE'] = date("d M Y",strtotime($business[0]->created_at));
        
        /*if(isset(Auth::user()->singlebusiness->name) && !empty(Auth::user()->singlebusiness->name))
        {
            $replaceArray['BUSINESSNAME'] = Auth::user()->singlebusiness->name;
        }
        else
        {
            $replaceArray['BUSINESSNAME'] = 'No Business';
        }*/

        /*

        $et_templatepseudoname = 'membership-request';     
        $emailParametersArray = [
                                    'toEmail' => Config::get('constant.ADMIN_EMAIL')
                                ];
        $toName = 'Ryuva club - Admin';

        Helpers::sendMailByTemplate($replaceArray,$et_templatepseudoname,$emailParametersArray,$toName);
        */
        info("upi link :- ".str_replace(" ","%20",$resonseOrderData->UpiLink));
        
        $responseData['status'] = 1;
        $responseData['payment_status'] = 1;
        $responseData['payment_status_up_link'] = $resonseOrderData->PaymentProcessUrl;     
        $responseData['payment_upi_link'] = str_replace(" ","%20",$resonseOrderData->UpiLink);     
        $responseData['message'] = 'Membership Plan Payment initiated successfully.'; 

        //trans('apimessages.membership_plan_sent_successfully');
        $statusCode = 200;
        } catch (Exception $e) {
             Log::info("Payment request creation failed:".$e->getMessage());
             $responseData = ['status' => 0, 'message' => "Payment request creation failed".$e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        
        return response()->json($responseData);
    }

/**
    Web Order Create Pay Response : {"OrderKeyId":"210129M11186
U31c197e30c","MerchantKeyId":11186,"UniqueRequestId":"31c197e30c","OrderType":"PAYMENT","Orde
rAmount":1,"OrderId":null,"OrderStatus":"Initiating","OrderPaymentStatus":0,"OrderPaymentStat
usText":null,"PaymentStatus":0,"PaymentTransactionId":null,"PaymentResponseCode":0,"PaymentAp
provalCode":null,"PaymentTransactionRefNo":null,"PaymentResponseText":null,"PaymentMethod":nu
ll,"PaymentAccount":null,"OrderNotes":null,"PaymentDateTime":null,"UpdatedDateTime":null,"Pay
mentProcessUrl":"https:\/\/payg.in\/payment\/payment?orderid=210129M11186U31c197e30c","OrderP
aymentCustomerData":{"FirstName":"Mahipalsinh Rana","LastName":null,"Address":null,"City":nul
l,"State":null,"ZipCode":null,"Country":null,"MobileNo":"9099937890","Email":"mahipalsinh.ran
a@gmail.com","UserId":null,"IpAddress":null},"UpiLink":"upi:\/\/pay?pa=ryuvaclub@yesbank&pn=R
ajputYuva Entrepreneur Club Pvt Ltd&mc=8398&am=1&mam=null&cu=INR&mode=01&orgid=00000&mid=YES0
000001561796&sign=MGZiMTRjZjQ4ZjUyMDViMDUyOWI4ZGY5ODI3OTI3ZDZiNDlhYmQ1ZjZjZjFhYjhhZWU0Y2U2Y2I
3NTBjNDliZGU3YzFlOTBkYjg4ZWY1ZmEyMjczNWQyOWYyMGU5NDkzYWZkNTJlZWUwNGY3MzQyZjk1YjdjNjkxNjUzZGFh
N2M="}
[2021-01-29 14:07:31] local.INFO: Pay G Android Response for Payment Order Id: 6013c8a3e7cbf-
796-1611909283
[2021-01-29 14:07:32] local.INFO: Pay G Response for Order Id: 210129M11186U31c197e30c
[2021-01-29 14:07:32] local.INFO: Pay G Response {"OrderKeyId":"210129M11186U31c197e30c","Mer
chantKeyId":11186,"UniqueRequestId":"31c197e30c","OrderType":"PAYMENT","OrderAmount":1,"Order
Id":"","OrderStatus":"1","OrderPaymentStatus":0,"OrderPaymentStatusText":null,"PaymentStatus"
:1,"PaymentTransactionId":"123658","PaymentResponseCode":1,"PaymentApprovalCode":"096409","Pa
ymentTransactionRefNo":"6119094213766950606099","PaymentResponseText":"Approved","PaymentMeth
od":"CreditCard","PaymentAccount":"463917 - XXXX - 5901","OrderNotes":"","PaymentDateTime":"1
\/29\/2021 2:07:03 PM","UpdatedDateTime":"1\/29\/2021 2:07:05 PM","PaymentProcessUrl":null,"O
rderPaymentCustomerData":null,"UpiLink":null}
**/

    public function payg_payment_android_reponse(Request $request){
        $data = $request->all();
        if(!empty($data['user_id']))
        {

            Log::info("Pay G Android Response for User Id: ".$data['user_id']);

            $users_data = DB::table('payment_transactions')
            ->where('user_id','=',$data['user_id'])
            ->orderByRaw('id desc')
            ->take(1)
            ->first();

            // Log::info("User Payment Data Get: ".);

            if(empty($users_data)){
                $responseData['status'] = 0;
                $responseData['message'] = 'Order key not found.'; 
                trans('apimessages.membership_plan_sent_successfully');
                $statusCode = 201;          
                return response()->json($responseData);
            }          
            $users_order_data = DB::table('payment_transactions')->where('order_id','=',$users_data->order_id)->first();
            
            if(!empty($users_order_data) && $users_order_data->status != 0) {
                $status = $users_order_data->status;
            } else {
                $payg = new PayGIntegration();      
                // call class object to order create function

                $resonseOrderData = $payg->orderDetail($users_order_data->order_id);
                // Log::info("Pay G Response for Order Id: ".$resonseOrderData);
                $resonseOrderData = json_decode($resonseOrderData, false);

                Log::info("Pay G Response for Order Id: ".$users_order_data->order_id);

                Log::info("Pay G Response ".json_encode($resonseOrderData));

                $status = $resonseOrderData->PaymentResponseCode;
            }

            if ($status == 1) {

                Log::info("Pay G - Success");

                $responseData['status'] = 1;
                $statusCode = 200;
                $responseData['message'] = 'Payment Success';
            } else if ($status == 2) {
                Log::info("Pay G - Failed");
                $responseData['status'] = 2;
                $statusCode = 200;
                $responseData['message'] = 'Payment Failed';
            } else if ($status == 4) {
                Log::info("Pay G - Waiting");
                $responseData['status'] = 4;
                $statusCode = 200;
                $responseData['message'] = 'Payment Request waiting to complete';
            } else if ($status == 0) {
                Log::info("Pay G - Ongoing");

                $responseData['status'] = 0;
                $statusCode = 200;
                $responseData['message'] = 'Payment Request Ongoing';
            }
            return response()->json($responseData, $statusCode);                   
            
        }
        else
        {
            $responseData['status'] = 1;
            $responseData['payment_status'] = 1;
            $responseData['message'] = 'User not found in with us.'; 
            trans('apimessages.membership_plan_sent_successfully');
            $statusCode = 201;          
            return response()->json($responseData);
        }
        
    }
    
    /**
     * for use payG payment form web
     *
     * @param  mixed $request
     * @return void
     */
    public function payg_payment(Request $request)
    {
        try {

            $validator=Validator::make($request->all(),[
                'plan_id'=>'required|numeric|exists:subscription_plans,id',
                //'membership_type'=>'required',
                // 'business_type'=>'required',
                // 'geo_location'=>'required',
                // 'ip_address'=>'required',
            ]);
            if($validator->fails())
            {
                return response()->json([
                        'status'=>0,
                        'message'=>$validator->messages()->all()[0]
                    ],400);
            }
            $input=$request->all();
            if(config('app.name') == 'RYEC') {
                $input['order_id']='RYUVA'.uniqid();
            } else {
                $input['order_id']='RAJASTHAN'.uniqid();
            }
            $paymentType = $request->input('payment_type','');
            $user = Auth::user();
            $insertId = MembershipRequest::firstOrCreate(['subscription_plans_id' =>  $request->plan_id, 'user_id' => $user->id]);
            $insertId->status = 0;
            $insertId->save();
            
            $input['plan_amount']=SubscriptionPlan::where('id',$request->plan_id)->pluck('price')->first();
            
            info("Web Pay Payment User Data:".$user->name." ".$user->phone);

            $names = $this->getFirstLastName($user->name);
            $post_data =  array();
            $post_data['OrderAmount'] = $input['plan_amount'];
            $post_data['OrderType'] = 'PAYMENT';
            $post_data['CustomerData']['CustomerId'] = $user->id;
            $post_data['CustomerData']['CustomerNotes'] = "";
            $post_data['CustomerData']['FirstName'] = (isset($names[0])) ? $names[0] : '' ;
            $post_data['CustomerData']['LastName'] = (isset($names[1])) ? $names[1] : '' ;
            $post_data['CustomerData']['MobileNo'] = $user->phone;
            $post_data['CustomerData']['Email'] = $user->email;
            $post_data['CustomerData']['EmailReceipt'] = "";
            $post_data['CustomerData']['BillingAddress'] = "";
            $post_data['CustomerData']['BillingCity'] = "";
            $post_data['IntegrationData']['UserName'] = "";
            $post_data['IntegrationData']['Source'] = "";
            $post_data['IntegrationData']['IntegrationType'] = "";
            $post_data['IntegrationData']['HashData'] = "";
            $post_data['IntegrationData']['PlatformId'] = "";

            if(isset($user->singlebusiness) && $user->singlebusiness->membership_type == 2) {
                $membershipType = "Lifetime";
            } elseif(isset($user->singlebusiness) && $user->singlebusiness->membership_type == 1) {
                $membershipType = "Premium";
            } else {
                $membershipType = "Basic";
            }

            $productData = [
                'membershipType' => $membershipType,
                //'businessType' => $request->business_type,
                'geoLocation' => $request->geo_location,
                'ipAddress' => $request->ip_address,
            ];
            $post_data['ProductData']= json_encode($productData);
            $post_data['callback_url']= url('api/payment/status');
            $redirect = config('app.Redirect_Url_Web').'/'.$user->id;
            $payg = new PayGIntegration();        
            // call class object to order create function

            $resonseOrderData = $payg->orderCreate($post_data,$redirect,$paymentType); 

            
            $resonseOrderData = json_decode($resonseOrderData, false);

            Log::info("Web Order Create Pay Response : ".json_encode($resonseOrderData));

            $transaction=new PaymentTransaction();
            $transaction->user_id=$user->id;
            $transaction->plan_id= $request->plan_id;
            $transaction->order_id=$resonseOrderData->OrderKeyId;
            $transaction->transaction_id=$resonseOrderData->UniqueRequestId;
            $transaction->OderKeyId=$input['order_id'];
            //$transaction->business_id=$business->id;
            $transaction->save();   
            
            $responseData['status'] = 1;
            $responseData['payment_status'] = 1;
            $responseData['payment_status_up_link'] = $resonseOrderData->PaymentProcessUrl;
            $responseData['message'] = 'Membership Plan Payment initiated successfully.';
        
            return response()->json($responseData,200);
        } catch (\Exception $e) {
            Log::info("Payment request creation failed".$e->getMessage());
            $responseData = ['status' => 0, 'message' => "Payment request creation failed".$e->getMessage()];
           return response()->json($responseData, 400);
       }
    }

   /**
    * Callbacks when payment process is completed.
    * Date: 24/07/2019
    * Developed By: Jaydeep Rajgor
    */
    public function paygCallback(Request $request)
    {
        info("payg callback");
        info($request->all());
        info("order key id:- ".data_get($request,'OrderKeyId'));
        info("order key id array:- ".$request['OrderKeyId']);
        info("requestData => order key id array:- ".$request->input('OrderKeyId'));


       // Log::info("Pay G Response ".json_encode($request->all()));

        //$resonseOrderData = json_decode($request->all(), false);
        $users_order_data = DB::table('payment_transactions')->where('order_id','=',data_get($request,'OrderKeyId'))->first();
        

        
        DB::table('payment_transactions')->where('id', $users_order_data->id)->update(array('status_message' =>  data_get($request,'PaymentResponseText')));
        $responseCode = data_get($request,'PaymentResponseCode');
        if($responseCode == 1)
        {

            Log::info("Pay G - Success");
        
            DB::table('payment_transactions')->where('id', $users_order_data->id)->update(array('status' => 1));
        
            $subscribed_plan = SubscriptionPlan::find($users_order_data->plan_id);

            $businessDetail = $this->objBusiness::where('user_id',$users_order_data->user_id)->first();
            $affected = DB::table('business')
            ->where('id', $businessDetail->id)
            ->update(['membership_type' => $subscribed_plan->months == 300 ? 2 : 1]);

            $Membership = new Membership;
            $Membership->subscription_plan_id = $subscribed_plan->id;
            $Membership->business_id = $businessDetail->id;
            $Membership->start_date = Carbon::now();
            $Membership->end_date = Carbon::now()->addMonths($subscribed_plan->months);
            $Membership->actual_payment = $subscribed_plan->price;
            $Membership->payment_transactions_id = $users_order_data->order_id;
            $Membership->status =  1;
            $Membership->agent_commision = 0;
            $Membership->net_payment = $subscribed_plan->price;
            $Membership->save();

            // for approved membership request
            MembershipRequest::where('user_id',$users_order_data->user_id)->update(['status'=>1]);

            $responseData['status'] = 1;
            $statusCode = 200;
            $responseData['message'] = 'Payment Successful';        
        
        } else if($responseCode == 2)
        {
            Log::info("Pay G - Failed");
            DB::table('payment_transactions')->where('id', $users_order_data->id)->update(array('status' => 2));
            $responseData['status'] = 2;
            $statusCode = 200;
            $responseData['message'] = 'Payment Failed';  
        }
        else if($responseCode == 4)
        {
            Log::info("Pay G - Waiting");
            DB::table('payment_transactions')->where('id', $users_order_data->id)->update(array('status' => 4));
            $responseData['status'] = 4;
            $statusCode = 200;
            $responseData['message'] = 'Payment Request waiting to complete';  
        }
        else if($responseCode == 0)
        {
            Log::info("Pay G - Ongoing");
            
            $responseData['status'] = 0;
            $statusCode = 200;
            $responseData['message'] = 'Payment Request Ongoing';  
        }           
            
        return response()->json($responseData, $statusCode);


        // $transaction = PaytmWallet::with('receive');
        // $response = $transaction->response();
        // $order_id = $transaction->getOrderId();
        // if($transaction->isSuccessful()){
        //     PaymentTransaction::where('order_id',$order_id)->update(['status'=>1, 'transaction_id'=>$transaction->getTransactionId()]);
        //     $outputArray['status']=1;
        //     $outputArray['message']='Transaction Successful';
        // }else if($transaction->isFailed()){
        //   PaymentTransaction::where('order_id',$order_id)->update(['status'=>2, 'transaction_id'=>$transaction->getTransactionId()]);
        //   $outputArray['status']=2;
        //   $outputArray['message']='Transaction Failed';
        // }
        // return response()->json($outputArray);
    }

    // public function AndroidOrder(Request $request)
    // {
        // $validator=Validator::make($request->all(),[
        //     'plan_id'=>'required|numeric|exists:subscription_plans,id'
        // ]);
        // if($validator->fails())
        // {
        //     return response()->json([
        //             'status'=>0,
        //             'message'=>$validator->messages()->all()[0]
        //         ],400);
        // }
        // $order_id='RYUVA'.uniqid();
        // $transaction=new PaymentTransaction();
        // $transaction->user_id=Auth::user()->id;
        // $transaction->plan_id=$request->plan_id;
        // $transaction->order_id=$order_id;
        // $transaction->save();
        // $paramList=array();
        // $paramList["MID"]=config('services.paytm-wallet.merchant_id');
        // $paramList["WEBSITE"]=config('services.paytm-wallet.merchant_app');
        // $paramList["INDUSTRY_TYPE_ID"]=config('services.paytm-wallet.industry_type');
        // $paramList["CHANNEL_ID"]=config('services.paytm-wallet.app_channel');
        // $paramList["ORDER_ID"]=$order_id;
        // $paramList["CUST_ID"]=Auth::user()->id;
        // $paramList["MOBILE_NO"]=Auth::user()->phone;
        // $paramList["EMAIL"]=Auth::user()->email;
        // $paramList["TXN_AMOUNT"]=SubscriptionPlan::where('id',$request->plan_id)->pluck('price')->first();
        // $paramList["CALLBACK_URL"]= config('services.paytm-wallet.callback_url')."?ORDER_ID=".$order_id;
        // $wallet=PaytmWallet::with('app');
        // Log::info("checksum data:".json_encode($paramList));
        // $checksum = getChecksumFromArray($paramList,config('services.paytm-wallet.merchant_key'));
        // $outputArray['status']=1;
        // $outputArray['message']='Checksum and order id generated successfully';
        // $outputArray['data']=array();
        // $outputArray['data']['checksum_hash']=$checksum;
        // $outputArray['data']['order_id']=$order_id;
        // return response()->json($outputArray);
    //}

    /* public function AndroidOrderVerfiy(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'order_id'=>'required',
            'checksum_hash'=>'required'
        ]);
        if($validator->fails())
        {
            return response()->json([
                    'status'=>0,
                    'message'=>$validator->messages()->all()[0]
                ],400);
        }
        $transaction=PaytmWallet::with('app');
        return $call=$this->verify($request);
    } */

    // public function verify(Request $request, $success = null, $error = null){
    //     $paramList = $request->all();
    //     $return_array = $request->all();
    //     $paytmChecksum = $request->get('checksum_hash');
    //     $isValidChecksum = verifychecksum_e($paramList, config('services.paytm-wallet.merchant_key'), $paytmChecksum);
        
    //     if ($isValidChecksum) {
    //         if ($success != null && is_callable($success)) {
    //             $success();
    //         }
    //     }else{
    //         if ($error != null && is_callable($error)) {
    //             $error();
    //         }
    //     }
    //     $IS_CHECKSUM_VALID=$isValidChecksum ? "Y" : "N";
    //     if(strcmp($IS_CHECKSUM_VALID,"Y")==0)
    //     {
    //         $order_id=$request->order_id;
    //         header("Pragma: no-cache");
    //         header("Cache-Control: no-cache");
    //         header("Expires: 0");


    //         /* initialize an array */
    //         $paytmParams = array();

    //         /* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
    //         $paytmParams["MID"] = config('services.paytm-wallet.merchant_id');

    //         /* Enter your order id which needs to be check status for */
    //         $paytmParams["ORDERID"] = $order_id;

    //         $checksum = getChecksumFromArray($paytmParams, config('services.paytm-wallet.merchant_key'));

    //         /* put generated checksum value here */
    //         $paytmParams["CHECKSUMHASH"] = $checksum;

    //         /* prepare JSON string for request */
    //         $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

    //         /* for Staging */
    //         $url = config('services.paytm-wallet.status_url');
    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_POST, 1);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
    //         $response = curl_exec($ch);
    //         $response_decode=json_decode($response,false);
    //         Log::info("Response Code:".json_encode($response_decode));
    //         if(strcmp($response_decode->STATUS,"TXN_SUCCESS")==0)
    //         {
    //             $paymenttransaction = PaymentTransaction::where('order_id',$order_id)->first();
    //             $paymenttransaction->update(['status'=>1, 'transaction_id'=>$response_decode->TXNID]);
    //             $subscribed_plan = SubscriptionPlan::find($paymenttransaction->plan_id);
    //             $businessDetail = Business::where('user_id',$paymenttransaction->user_id)->first();
    //             $businessDetail->update(['membership_type' => $subscribed_plan->months == 300 ? 2 : 1]);
    //             $Membership = new Membership;
    //             $Membership->subscription_plan_id = $subscribed_plan->id;
    //             $Membership->business_id = $businessDetail->id;
    //             $Membership->start_date = Carbon::now();
    //             $Membership->end_date = Carbon::now()->addMonths($subscribed_plan->months);
    //             $Membership->actual_payment = $subscribed_plan->price;
    //             $Membership->agent_commision = 0;
    //             $Membership->net_payment = $subscribed_plan->price;
    //             $Membership->save();

    //             //Send push notification to Business User & Agent
    //             $notificationData = [];
    //             $notificationData['title'] = 'Membership Upgrade';
    //             $notificationData['message'] = 'Dear '.$businessDetail->user->name.',   Congratulations! Your membership has been upgraded to Premium. You will now be able to utilize all premium features.';
    //             $notificationData['type'] = '9';
    //             $notificationData['business_id'] = $businessDetail->id;
    //             $notificationData['business_name'] = $businessDetail->name;
    //             Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

    //             // for pushnotification list
    //             $notificationListArray = [];
    //             $notificationListArray['user_id'] = $businessDetail->user_id;
    //             $notificationListArray['business_id'] = $businessDetail->id;
    //             $notificationListArray['title'] = 'Membership Upgrade';
    //             $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',   Congratulations! Your membership has been upgraded to Premium. You will now be able to utilize all premium features.';
    //             $notificationListArray['type'] = '9';
    //             $notificationListArray['business_name'] = $businessDetail->name;
    //             $notificationListArray['user_name'] = $businessDetail->user->name;

    //             NotificationList::create($notificationListArray);

    //             $outputArray['status']=1;
    //             $outputArray['data']=array();
    //             $outputArray['data']['transaction_status']='success';
    //         }
    //         else if(strcmp($response_decode->STATUS,"TXN_FAILURE")==0)
    //         {
    //             $paymenttransaction = PaymentTransaction::where('order_id',$order_id)->first();
    //             $paymenttransaction->update(['status'=>2, 'transaction_id'=>$response_decode->TXNID]);

    //             //Send push notification to Business User & Agent
    //             $businessDetail = Business::where('user_id',$paymenttransaction->user_id)->first();
    //             $notificationData = [];
    //             $notificationData['title'] = 'Membership Upgrade';
    //             $notificationData['message'] = 'Dear '.$businessDetail->user->name.',   Your transactions has been failed, Please try again.';
    //             $notificationData['type'] = '9';
    //             $notificationData['business_id'] = $businessDetail->id;
    //             $notificationData['business_name'] = $businessDetail->name;
    //             Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

    //             // for pushnotification list
    //             $notificationListArray = [];
    //             $notificationListArray['user_id'] = $businessDetail->user_id;
    //             $notificationListArray['business_id'] = $businessDetail->id;
    //             $notificationListArray['title'] = 'Membership Upgrade';
    //             $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',   Your transactions has been failed, Please try again.';
    //             $notificationListArray['type'] = '9';
    //             $notificationListArray['business_name'] = $businessDetail->name;
    //             $notificationListArray['user_name'] = $businessDetail->user->name;

    //             NotificationList::create($notificationListArray);

    //             $outputArray['status']=1;
    //             $outputArray['data']=array();
    //             $outputArray['data']['transaction_status']='failure';
    //         }
    //         else if(strcmp($response_decode->STATUS,"PENDING")==0)
    //         {
    //             $outputArray['status']=1;
    //             $outputArray['data']=array();
    //             $outputArray['data']['transaction_status']='pending';
    //         }
    //         else
    //         {
    //             $outputArray['status']=0;
    //             $outputArray['data']=array();
    //             $outputArray['data']['transaction_status']='Status not available';
    //         }
    //         return response()->json($outputArray);
    //     }
    //     else
    //     {
    //         $outputArray['status']=1;
    //         $outputArray['data']=array();
    //         $outputArray['data']['message']='Invalid Checksum';
    //         return response()->json($outputArray);
    //     }
    // }

    /*****
    This is not used
    ***/
    

    /**
    * Creates Order and Sends the request to paytm.
    * Parameters: Token, Plan ID
    * Date: 24/07/2019
    * Developed By: Jaydeep Rajgor
    */
//     public function order(Request $request)
//     {
//         $validator=Validator::make($request->all(),[
//             'plan_id'=>'required|numeric|exists:subscription_plans,id'
//         ]);
//         if($validator->fails())
//         {
//             return response()->json([
//                     'status'=>0,
//                     'message'=>$validator->messages()->all()[0]
//                 ],400);
//         }
//         $input=$request->all();
//         $input['order_id']='RYUVA'.uniqid();
//         $input['user']=Auth::id();
//         $input['mobile_no']=User::where('id',$input['user'])->pluck('phone')->first();
//         $input['email']=User::where('id',$input['user'])->pluck('email')->first();
//         $input['plan_amount']=SubscriptionPlan::where('id',$request->plan_id)->pluck('price')->first();
//         $transaction=new PaymentTransaction();
//         $transaction->user_id=$input['user'];
//         $transaction->plan_id=$request->plan_id;
//         $transaction->order_id=$input['order_id'];
//         $transaction->save();

//         // $payment = PaytmWallet::with('receive');
//   //       $payment->prepare([
//   //         'order' => $input['order_id'],
//   //         'user' => $input['user'],
//   //         'mobile_number' => $input['mobile_no'],
//   //         'email' => $input['email'],
//   //         'amount' => $input['plan_amount'],
//   //         'callback_url' => url('api/payment/status')
//   //       ]);


//         $payg = new PayGIntegration();        
//         // call class object to order create function
//         $resonseOrderData = $payg->orderCreate([
//           'email' => $input['email'],
//           'OrderAmount' => $input['plan_amount'],
//           'callback_url' => url('api/payment/status')
//         ]);
//         // handle the json object response
//         $resonseOrderData = json_decode($resonseOrderData, false);
//        echo "<pre>";  print_r(  $resonseOrderData);
//         return $payment->receive();
//     }
    
    
    
}
