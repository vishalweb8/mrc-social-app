<?php                                     

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionRequest;
use App\MembershipRequest;
use App\SubscriptionPlan;
use Auth;
use Helpers;
use Config;
use Image;
use Crypt;
use File;

use Input;
use Illuminate\Contracts\Encryption\DecryptException;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Redirect;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objSubscription = new SubscriptionPlan();
        $this->SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH = Config::get('constant.SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT');
    }

    public function index()
    {
        $subscriptionList = $this->objSubscription->getAll();
        return view('Admin.ListSubscriptions', compact('subscriptionList'));
    }

    public function add()
    {
        return view('Admin.EditSubscriptions');
    }

    public function save(SubscriptionRequest $request)
    {
        $postData = Input::all();
        unset($postData['_token']);
        
        if (Input::file('logo')) 
        {  
            $logo = Input::file('logo'); 

            if (!empty($logo)) 
            {
                $fileName = 'subscription_plan_logo_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $pathThumb = (string) Image::make($logo->getRealPath())->resize($this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH, $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT)->encode();
                
                if(isset($postData['old_logo']) && $postData['old_logo'] != '')
                {
                    $originalImageDelete = Helpers::deleteFileToStorage($postData['old_logo'], $this->SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH, "s3");
                    $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_logo'], $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH, "s3");
                }
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH, $logo, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");

                $postData['logo'] = $fileName;
            }
        }

        $response = $this->objSubscription->insertUpdate($postData);
        if ($response) {
            return Redirect::to("admin/subscriptions")->with('success', trans('labels.subscriptionsuccessmsg'));
        } else {
            return Redirect::to("admin/subscriptions")->with('error', trans('labels.subscriptionerrormsg'));
        }
    }

    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $data = $this->objSubscription->find($id);
            if($data) {
                return view('Admin.EditSubscriptions', compact('data'));
            } else {
                return Redirect::to("admin/subscriptions")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            return view('errors.404');
        }
        
    }

    public function delete($id)
    {
        $data = $this->objSubscription->find($id);
        $response = $data->delete();
        if ($response) {
            return Redirect::to("admin/subscriptions")->with('success', trans('labels.subscriptiondeletesuccessmsg'));
        }
    }

    public function membershipRequest(Request $request)
    {
        if ($request->ajax()) {
            $membershipRequests = MembershipRequest::select('membership_requests.*')->has('user')->with('user.singlebusiness:id,name,user_id','subscriptionPlan');

            if($request->status != '') {
                $membershipRequests->where('membership_requests.status',$request->status);
            }

            $user = auth()->user();
            if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
                $membershipRequests->whereHas('user.singlebusiness', function ($query) use ($user) {
                    $query->whereRaw($user->sql_query);
                });
            }
            return DataTables::of($membershipRequests)
                ->addColumn('user_name', function($membershipRequest) { 
                    $name = '';                   
                    if(!empty($membershipRequest->user)) {
                        $name = "<a href='".url('/admin/edituser',\Crypt::encrypt($membershipRequest->user->id))."' target='_blank'>".$membershipRequest->user->name."</a>";
                    }
                    return $name;
                })
                ->addColumn('entity_name', function($membershipRequest) { 
                    $name = '';                   
                    if(!empty($membershipRequest->user->singlebusiness)) {
                        $name = "<a href='".route('entity.show',\Crypt::encrypt($membershipRequest->user->singlebusiness->id))."' target='_blank'>".$membershipRequest->user->singlebusiness->name."</a>";
                    }
                    return $name;
                })
                ->editColumn('created_at', function($membershipRequest) {
                    return $membershipRequest->created_at->format('Y-m-d, H:m');
                })
                ->editColumn('status', function($membershipRequest) {                 
                    if($membershipRequest->status == 1) {
                        $status = 'Approved';
                    } else if($membershipRequest->status == 2) {
                        $status = 'Rejected';
                    } else {
                        $status = 'Pending';
                    }
                    $reasons = '';
                    if(!empty($membershipRequest->reasons)) {
                        $reasons = '<span style="cursor:pointer;" data-toggle="tooltip" data-placement="bottom" data-original-title="Reason: '.$membershipRequest->reasons.'">
                            <i class="fa fa-caret-square-o-down"></i>
                        </span>';
                    }
                    return $status . $reasons;
                })
                ->addColumn('action', function($membershipRequest) use($user) {
                    $class = $approveBtn = $rejectBtn = $pendingBtn = $comment = '';
                    $encriptId = Crypt::encrypt($membershipRequest->id);                    

                    if($user->can(config('perm.approveMemberReq')) && ($membershipRequest->status == 0 || $membershipRequest->status == 2)) {
                        $approveUrl = route('membership.update.status',[$encriptId,1]);
                        $approveBtn = getApproveBtn($approveUrl);
                    }

                    if($user->can(config('perm.rejectMemberReq')) && ($membershipRequest->status == 0 || $membershipRequest->status == 1)) {
                        $attributes = [
                            "onclick" => "return confirm('Are you sure you want to reject?')"
                        ];
                        $rejectUrl = route('membership.update.status',[$encriptId,2]);
                        $rejectBtn = getRejectBtn($rejectUrl, $class, $attributes);
                    }

                    if($user->can(config('perm.pendingMemberReq')) && ($membershipRequest->status == 1 || $membershipRequest->status == 2)) {
                        $pendingUrl = route('membership.update.status',[$encriptId,0]);
                        $pendingBtn = getPendingBtn($pendingUrl);
                    }

                    if($membershipRequest->status == 1) {
                        $commentFunction = 'approveComment('.$membershipRequest->id.')';
                    } else if($membershipRequest->status == 2) {
                        $commentFunction = 'rejectComment('.$membershipRequest->id.')';
                    } else {
                        $commentFunction = 'pendingComment('.$membershipRequest->id.')';
                    }
                    if($user->can(config('perm.commentMemberReq'))) {
                        $comment = '<i style="cursor:pointer;" onclick="'.$commentFunction.'" data-toggle="tooltip" data-original-title="Add Comment" class="fa fa-comment-o"></i>';
                    }

                    $input = '<input type="hidden" name="reasons" id="reason_'.$membershipRequest->id.'" value="'.$membershipRequest->reasons.'"/>';

                    return $input.$approveBtn.$rejectBtn.$pendingBtn.$comment;
                })
                ->rawColumns(['user_name','entity_name','status','action'])
                ->make(true);
        }
        
        return view('Admin.Membership.request');
    }

   
    public function membershipApprove($id,$status)
    {
        try {
                $id = Crypt::decrypt($id);
                $membershipRequests = MembershipRequest::find($id);
                $membershipRequests->status = $status;
                $membershipRequests->save();   
              
                return Redirect::to("admin/membershiprequest")->with('success', trans('labels.membershipupdatesuccessfully'));
        } catch (DecryptException $e) {
            return view('errors.404');
        }
    }

    public function membershipReject()
    {
        try {
                $postData = Input::all();
                
                if(isset($postData))
                {
                    $id = $postData['request_id'];
                    $status = $postData['status'];
                    $reasons = $postData['reasons'];
                }
                
                $membershipRequests = MembershipRequest::find($id);
                $membershipRequests->status = $status;
                $membershipRequests->reasons = $reasons;
                $membershipRequests->save();   
                
                return Redirect::to("admin/membershiprequest")->with('success', trans('labels.membershipupdatesuccessfully'));
        } catch (DecryptException $e) {
            return view('errors.404');
        }
    }

}
