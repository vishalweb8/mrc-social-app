<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PaytmWallet;
use App\User;
use App\SubscriptionPlan;
use App\PaymentTransaction;
use App\Membership;
use App\Business;
use App\NotificationList;
use PaytmWalletProvider;
use Log;
use Carbon\Carbon;
use Helpers;

class VerifyPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify status of paytm payment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $transaction=PaytmWallet::with('app');
        $pending_orders = PaymentTransaction::where('status',0)->get();
        //Log::info("Process Start At: ".Carbon::now());
        //Log::info("Total transactions: ".$pending_orders->count());
        foreach ($pending_orders as $request) {
            $order_id=$request->order_id;
            // header("Pragma: no-cache");
            // header("Cache-Control: no-cache");
            // header("Expires: 0");


            /* initialize an array */
            $paytmParams = array();

            /* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
            $paytmParams["MID"] = config('services.paytm-wallet.merchant_id');

            /* Enter your order id which needs to be check status for */
            $paytmParams["ORDERID"] = $order_id;

            $checksum = getChecksumFromArray($paytmParams, config('services.paytm-wallet.merchant_key'));

            /* put generated checksum value here */
            $paytmParams["CHECKSUMHASH"] = $checksum;

            /* prepare JSON string for request */
            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

            /* for Staging */
            $url = config('services.paytm-wallet.status_url');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
            $response = curl_exec($ch);
            $response_decode=json_decode($response,false);
            if(strcmp($response_decode->STATUS,"TXN_SUCCESS")==0)
            {
                $paymenttransaction = PaymentTransaction::where('order_id',$order_id)->first();
                $paymenttransaction->update(['status'=>1, 'transaction_id'=>$response_decode->TXNID]);
                $subscribed_plan = SubscriptionPlan::find($paymenttransaction->plan_id);
                $businessDetail = Business::where('user_id',$paymenttransaction->user_id)->first();
                $businessDetail->update(['membership_type' => $subscribed_plan->months == 300 ? 2 : 1]);
                $Membership = new Membership;
                $Membership->subscription_plan_id = $subscribed_plan->id;
                $Membership->business_id = $businessDetail->id;
                $Membership->start_date = Carbon::now();
                $Membership->end_date = Carbon::now()->addMonths($subscribed_plan->months);
                $Membership->actual_payment = $subscribed_plan->price;
                $Membership->agent_commision = 0;
                $Membership->net_payment = $subscribed_plan->price;
                $Membership->save();
                
                //Send push notification to Business User & Agent
                $notificationData = [];
                $notificationData['title'] = 'Membership Upgrade';
                $notificationData['message'] = 'Dear '.$businessDetail->user->name.',   Congratulations! Your membership has been upgraded to Premium. You will now be able to utilize all premium features.';
                $notificationData['type'] = '9';
                $notificationData['business_id'] = $businessDetail->id;
                $notificationData['business_name'] = $businessDetail->name;
                Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

                // for pushnotification list
                $notificationListArray = [];
                $notificationListArray['user_id'] = $businessDetail->user_id;
                $notificationListArray['business_id'] = $businessDetail->id;
                $notificationListArray['title'] = 'Membership Upgrade';
                $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',   Congratulations! Your membership has been upgraded to Premium. You will now be able to utilize all premium features.';
                $notificationListArray['type'] = '9';
                $notificationListArray['business_name'] = $businessDetail->name;
                $notificationListArray['user_name'] = $businessDetail->user->name;

                NotificationList::create($notificationListArray);
                //Log::info($order_id." Transaction Success.");

            }
            else if(strcmp($response_decode->STATUS,"TXN_FAILURE")==0)
            {
                $paymenttransaction = PaymentTransaction::where('order_id',$order_id)->first();
                $paymenttransaction->update(['status'=>2, 'transaction_id'=>$response_decode->TXNID]);
                $outputArray['status']=1;
                $outputArray['data']=array();
                $outputArray['data']['transaction_status']='failure';

                //Send push notification to Business User & Agent
                $businessDetail = Business::where('user_id',$paymenttransaction->user_id)->first();
                $notificationData = [];
                $notificationData['title'] = 'Membership Upgrade';
                $notificationData['message'] = 'Dear '.$businessDetail->user->name.',   Your transactions has been failed, Please try again.';
                $notificationData['type'] = '9';
                $notificationData['business_id'] = $businessDetail->id;
                $notificationData['business_name'] = $businessDetail->name;
                Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

                // for pushnotification list
                $notificationListArray = [];
                $notificationListArray['user_id'] = $businessDetail->user_id;
                $notificationListArray['business_id'] = $businessDetail->id;
                $notificationListArray['title'] = 'Membership Upgrade';
                $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',   Your transactions has been failed, Please try again.';
                $notificationListArray['type'] = '9';
                $notificationListArray['business_name'] = $businessDetail->name;
                $notificationListArray['user_name'] = $businessDetail->user->name;

                NotificationList::create($notificationListArray);

                //Log::info($order_id." Transaction Fail.");
            }
            else if(strcmp($response_decode->STATUS,"PENDING")==0)
            {
                $paymenttransaction = PaymentTransaction::where('order_id',$order_id)->first();
                $paymenttransaction->update(['status'=>0, 'transaction_id'=>$response_decode->TXNID]);
                $outputArray['status']=1;
                $outputArray['data']=array();
                $outputArray['data']['transaction_status']='pending';
            }
            else
            {
                $outputArray['status']=0;
                $outputArray['data']=array();
                $outputArray['data']['transaction_status']='Status not available';
            }
        }
    }
}
