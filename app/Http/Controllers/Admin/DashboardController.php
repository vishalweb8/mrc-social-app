<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Input;
use Redirect;
use App\Business;
use App\Branding;
use App\Notification;
use App\NotificationGroup;
use Cache;
use Illuminate\Contracts\Encryption\DecryptException;
use JWTAuth;
use JWTAuthException;
use Validator;
use Helpers;
use Config;
use \stdClass;
use DB;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objBusiness = new Business();
        $this->objNotificatGroup = new NotificationGroup();

        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('business-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));
    }

    public function index()
    {
        if (false){
            $membersForApproval = Cache::get('membersForApproval');
        } else {
            $filters = [
                'approved' => 0,
                'take' => 100,
            ];
            $membersForApproval = $this->objBusiness->getAll($filters, true, true);
            Cache::put('membersForApproval', $membersForApproval, 60);
        }

        // if(Auth::user()->agent_approved == 1)
        // {
        //    $this->log->error('Admin something went wrong while open dashboard', array('admin_user_id' =>  Auth::id(), 'error' => 'not allow to open Dashboard'));
        //     return view('errors.404');
        // }

        return view('Admin.Dashboard', compact('membersForApproval'));
    }

    public function getLogout()
    {
        Auth::logout();

        return Redirect::to('admin/login');
    }

    public function siteAnalytics()
    {
        return view('Admin.Analytics');
    }

    public function notifications()
    {
        $postData =[];
        $postData = Input::all();
        $notificationgroup = [];
        $business =array();
       if(isset($postData['_token']))
       {
       $category =  $postData['category'];

       $business =  DB::table('business')
         ->where('name','like','%'.$postData['name'].'%')

        ->Where('country',$postData['country'])
        ->Where('state',$postData['state'])
        ->Where('city',$postData['city'])
        ->where('approved',1)
        ->when($category, function ($query) use ($category) {
                    return $query->where('parent_category','like','%'. $category.'%');
                })
        ->where('deleted_at',null)
        ->get();
       }
       $country =  DB::table('country')->get();
       $state =  DB::table('state')->get();
       $city =  DB::table('city')->get();
       $categories =  DB::table('categories')->where('parent','0')->get();
 
       $notificationgroup =   $this->objNotificatGroup->get();
        return view('Admin.ListNotifications',compact( 'notificationgroup','country','categories','state','city','business','postData'));
    }
    public function sendNotification(Request $request)
    {
         
         $validator = Validator::make($request->all(),
            [
                'title' => 'required',
                'group_id' => 'required',
                'description' => 'required'
            ]
        );
        if ($validator->fails())
            {
                return redirect()->back()->withErrors([$validator->messages()->all()[0]]);
            }
            $postData = $request->all();
            $id =  $postData['group_id'];
            $brandingDetail = $this->objNotificatGroup->where('id',$id)->first();
 
            if(isset($brandingDetail) &&  count((array)explode(',', $brandingDetail->business_id)) > 0)
            {
                $arrayBusinessId = explode(',', $brandingDetail->business_id);
                 $type = '';
                foreach ($arrayBusinessId as $key => $value) {
                    $mainArray['business_id']  = $value;
                     $type = 'text';
                     $mainArray['name'] = $brandingDetail->group_title;
                     $mainArray['id'] = $brandingDetail->id;
                     $mainArray['branding_type'] = $type;
                     $mainArray['type'] = '10';
                    $mainArray['title'] =$postData['title'];
                    $mainArray['message'] = $postData['description'];
                    $token = "/topics/ryec";

                     
                    $response = Helpers::topicPushNotification($token, $mainArray);             
                }
               
               
                if($response)
                {
                   return Redirect::to('admin/notifications')->with('success', 'Notification sent successfully');
                }
                else
                {
                   return Redirect::to('admin/notifications')->withErrors(['Invalid Notification']);
                }
            }
            else
            {

                return Redirect::to('admin/notifications')->withErrors(['Invalid Notification Group id']);
            }
        }
    public function notificationdelete($id)
    {

        $response =  $this->objNotificatGroup->where('id',$id)->delete();

        if($response)
                {
                   return Redirect::to('admin/notifications')->with('success', 'Notification sent successfully');
                }
                else
                {
                   return Redirect::to('admin/notifications')->withErrors(['Invalid Notification']);
                }


    }
     
    public function notificationsave(Request $request)
    {

         $validator = Validator::make($request->all(),
            [
                'group_title' => 'required',
                'business_id' => 'required'
            ]
        );
        if ($validator->fails())
            {
                return redirect()->back()->withErrors([$validator->messages()->all()[0]]);
            }
        $postData = $request->all();

        $brandingArr = [];
        $brandingArr['group_title'] = $postData['group_title'];
        $collection = '';
        $i = 0;
        foreach ($postData['business_id'] as $key => $value) {
            if($i == 0){
                 $collection .= $value;
            }else{
                 $collection .= ','.$value;

            }
           

            $i++;
        }
         $brandingArr['business_id'] =   $collection;

       $this->objNotificatGroup->create($brandingArr);
        return Redirect::to('admin/notifications')->with('success', trans('labels.notificationsavesuccessmsg'));
    }
    public function getPushNotification()
    {
        return view('Admin.TopicPushNotification');
    }

    public function sendPushNotification(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
                'description' => 'required'
            ]
        );
        if ($validator->fails())
        {
            return redirect()->back()->withErrors([$validator->messages()->all()[0]]);
        }
        $brandingDetail = Branding::first();
        if($brandingDetail)
        {
            $type = '';
            $mainArray['business_id'] = ((isset($brandingDetail->business_id) && $brandingDetail->business_id != null && $brandingDetail->business_id != '') ? $brandingDetail->business_id : 0);
            $mainArray['id'] = $brandingDetail->id;
            $mainArray['name'] = ($brandingDetail->type == 1) ? url('images/branding_image.png') : $brandingDetail->name;
            if($brandingDetail->type == 1)
            {
                $type = 'image';
            }
            elseif($brandingDetail->type == 2)
            {
                $type = 'video';
                $videoId = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $brandingDetail->name, $match))
                {
                    $videoId = $match[1];
                }
                $mainArray['videoId'] = $videoId;
            }
            else
            {
                $type = 'text';
            }
            $mainArray['branding_type'] = $type;
            $mainArray['type'] = '10';
            $mainArray['title'] = $request->title;
            $mainArray['message'] = $request->description;
            $token = "/topics/ryec"; 
//          $response = Helpers::pushNotificationForAndroid($token, $mainArray);
            $response = Helpers::topicPushNotification($token, $mainArray);
            if($response)
            {
                return redirect()->back()->with('success', 'Notification sent successfully');
            }
            else
            {
                return redirect()->back()->withErrors(['Invalid Notification']);
            }
        }
        else
        {
            return redirect()->back()->withErrors(['Record not found']);
        }
    }
}
