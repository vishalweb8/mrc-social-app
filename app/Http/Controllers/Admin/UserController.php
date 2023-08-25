<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\BusinessRequest;
use Auth;
use Input;
use Config;
use Redirect;
use App\User;
use App\UserRegisterOTP;
use App\Business;
use App\UserRole;
use App\UserMetaData;
use App\AgentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Request as RequestsRequest;
use Crypt;
use Helpers;
use Image;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objUser = new User();
        $this->objUserMetaData = new UserMetaData();
        $this->objAgentUser = new AgentUser();
        $this->objUserRole = new UserRole();
        $this->objBusiness = new Business();
        $this->BUSINESS_BANNER_IMAGE_PATH = Config::get('constant.BUSINESS_BANNER_IMAGE_PATH');
        $this->USER_ORIGINAL_IMAGE_PATH = Config::get('constant.USER_ORIGINAL_IMAGE_PATH');
        $this->USER_THUMBNAIL_IMAGE_PATH = Config::get('constant.USER_THUMBNAIL_IMAGE_PATH');
        $this->USER_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.USER_THUMBNAIL_IMAGE_HEIGHT');
        $this->USER_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.USER_THUMBNAIL_IMAGE_WIDTH');
        
        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('user-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));
    }

    public function index(Request $request)
    {   
        $postData = $request->all();

        if((isset($postData['searchtext']) && $postData['searchtext'] != '') || (isset($postData['usertype']) && $postData['usertype'] != '') || 
            (isset($postData['fieldname']) && $postData['fieldname'] != '' && isset($postData['fieldtype']) && $postData['fieldtype'] != '') ||
            (isset($postData['country_code']) && $postData['country_code'] != '') )
        {   
            $this->log->info('Admin user listing page', array('admin_user_id' => Auth::id()));
            if(Auth::user()->agent_approved == 1)
            {
                $postData['created_by'] = Auth::id();
                
                $mainArray = [];
                $agentAssignUsers = Business::where('agent_user',Auth::id())->select('user_id')->get();
                if(!empty($agentAssignUsers))
                {
                   foreach ($agentAssignUsers as $user) 
                    {
                        $mainArray[] = $user->user_id;
                    }
                    $postData['user_ids'] = $mainArray;
                }
            }
            $userList = $this->objUser->userFilter($postData);
        }
        else
        {
            $this->log->info('Admin user listing page without filter', array('admin_user_id' => Auth::id()));
            $filters['deleted'] = 'all';
            $filters['status'] = 'all';
            if(Auth::user()->agent_approved == 1)
            {
                $mainArray = [];
                $agentAssignUsers = Business::where('agent_user',Auth::id())->select('user_id')->get();
                if(!empty($agentAssignUsers) && count($agentAssignUsers) > 0)
                {
                    foreach ($agentAssignUsers as $user) 
                    {
                        $mainArray[] = $user->user_id;
                    }
                }
                
                $filters['created_by'] = Auth::id();
                $filters['user_ids'] = $mainArray;
            }
            $userList = $this->objUser->getAll($filters,true);            
        }
        //dd($userList);
        return view('Admin.ListUsers', compact('userList','postData'));
    }

    public function add()
    {
        $this->log->info('Admin user add page', array('admin_user_id' => Auth::id()));
        return view('Admin.EditUser');
    }

    public function addAgent()
    {
        $agent_approved = '1';
        $this->log->info('Admin agent add page', array('admin_user_id' => Auth::id()));
        return view('Admin.EditUser',compact('agent_approved'));
    }

    public function save(UserRequest $request)
    {
        try {
            $filters['sql_query'] = $request->sql_query;
            $this->objUser->getAll($filters,true);
        } catch (\Throwable $th) {
            $this->log->error("error while storing sql query in add/edit user page");
            $this->log->error($th);
            return redirect()->back()->withInput()->withErrors('Invalid sql query');
        }
        $postData = $request->all();
       
        unset($postData['_token']);
        
        if(isset($postData['password']) && $postData['password'] != '') 
        {
            $postData['password'] = bcrypt($postData['password']);
        } else {
            unset($postData['password']);
        }
        
        // upload user profile picture
        if ($request->profile_pic) 
        {  
            $profile_pic = $request->profile_pic;

            if (!empty($profile_pic)) 
            {
                $fileName = 'user_' . uniqid() . '.' . $profile_pic->getClientOriginalExtension();
                $pathThumb = (string) Image::make($profile_pic->getRealPath())->resize($this->USER_THUMBNAIL_IMAGE_WIDTH, $this->USER_THUMBNAIL_IMAGE_HEIGHT)->encode();

                if(isset($postData['old_profile_pic']) && $postData['old_profile_pic'] != '')
                {
                    $originalImageDelete = Helpers::deleteFileToStorage($postData['old_profile_pic'], $this->USER_ORIGINAL_IMAGE_PATH, "s3");
                    $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_profile_pic'], $this->USER_THUMBNAIL_IMAGE_PATH, "s3");
                }

                //Uploading on AWS
    			$originalImage = Helpers::addFileToStorage($fileName, $this->USER_ORIGINAL_IMAGE_PATH, $profile_pic, "s3");
    			$thumbImage = Helpers::addFileToStorage($fileName, $this->USER_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");

                $postData['profile_pic'] = $fileName;
            }
        }
        
        $subscription =  (!isset($postData['subscription'])) ? 0 : 1;
        $postData['subscription'] = $subscription;

        $isRajput =  (!isset($postData['isRajput'])) ? 0 : 1;
        $postData['isRajput'] = $isRajput;
        
        if($postData['id'] == 0)
        {
            $userData = $this->objUser->where('phone',$postData['phone'])->where('country_code',$postData['country_code'])->get();
        }
        else
        {
            $userData = $this->objUser->where('phone',$postData['phone'])->where('id','<>',$postData['id'])->where('country_code',$postData['country_code'])->get();
        }
        if(count($userData) > 0)
        {
            return Redirect::back()->withErrors('Country code and phone number\'s combination must be unique')->withInput();
        }
        else
        {
            if(isset($postData['action']) && $postData['action'] == 1)
            {
                $postData['agent_approved'] = 1;
            }elseif (isset($postData['action']) && $postData['action'] == 0) {
                $postData['agent_approved'] = 0;

            }
            $response = $this->objUser->insertUpdate($postData);
        }

        $userId = (isset($postData['id']) && $postData['id'] == 0) ? $response->id : $postData['id'];

        if(isset($postData['action']) && $postData['action'] == 1)
        {
            $agentDetail = AgentUser::firstOrCreate(['user_id' =>$userId]);
            $agentDetail->user_id = $userId;

            if(isset($postData['agent_city']) && !empty($postData['agent_city']))
            {
                $agentDetail->city = implode(',',$postData['agent_city']);
            }
            if(isset($postData['agent_bank_detail']) && $postData['agent_bank_detail'] != '')
            {
                $agentDetail->bank_detail = $postData['agent_bank_detail'];
            }
            
            $agentDetail->save();
            
        }
        elseif (isset($postData['action']) && $postData['action'] == 0) {

            $agentDetail = AgentUser::where('user_id',$userId)->first();
            if(!empty($agentDetail))
            {
                $agent = AgentUser::find($agentDetail->id);
                $agent->delete();
            }
            
        }

        if ($response) 
        {           
            //UserRole::firstOrCreate(['user_id' =>  ($postData['id'] == 0)?$response->id:$postData['id'], 'role_id' => Config::get('constant.USER_ROLE_ID')]);
            if(($postData['id'] == 0)) {
                $response->syncRoles($request->input('roles',[]));
            } else {
                $user = User::find($postData['id']);
                $user->syncRoles($request->input('roles',[]));
            }
            if(isset($postData['agent_approved']) && $postData['agent_approved'] == 1)
            {
                $this->log->info('Admin user added/updated successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId));
                return Redirect::to("admin/users")->with('success', trans('labels.agentsuccessmsg'));
            }
            else
            {
                $this->log->info('Admin user added/updated successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId));
                return Redirect::to("admin/users")->with('success', trans('labels.usersuccessmsg'));
            }
        } else {
            $this->log->error('Admin something went wrong while adding/updating user', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId));
            return Redirect::to("admin/users")->with('error', trans('labels.usererrormsg'));
        }
    }

    public function edit($id)
    {
        try 
        {
            $id = Crypt::decrypt($id);
            $data = $this->objUser->find($id);
            $isVendor = Helpers::userIsVendorOrNot($id);
            
            if($data) 
            {
                $this->log->info('Admin user edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return view('Admin.EditUser', compact('data','isVendor'));
            } else {
                $this->log->error('Admin something went wrong while user edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return Redirect::to("admin/users")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            $this->log->error('Admin something went wrong while user edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
                
        
    }

    public function editAgent($id)
    {
        try 
        {
            $id = Crypt::decrypt($id);
            $data = $this->objUser->find($id);
            $isVendor = Helpers::userIsVendorOrNot($id);
           
            if($data) 
            {
                $this->log->info('Admin user edit agent page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return view('Admin.EditUser', compact('data','isVendor'));
            } else {
                $this->log->error('Admin something went wrong while agent edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return Redirect::to("admin/users")->with('error', trans('labels.recordnotexist'));
            }
        }   
        catch (DecryptException $e) 
        {
            $this->log->error('Admin something went wrong while agent edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
    }

    public function delete($id)
    {
        $data = $this->objUser->find($id); 
        $response = $data->delete();
        if ($response) 
        {
            $this->log->info('Admin user deleted successfully', array('admin_user_id' => Auth::id(),'user_id' => $id));
            return Redirect::to("admin/users")->with('success', trans('labels.userdeletesuccessmsg'));
        }
    } 

    public function hardDelete($id)
    {
        try {
            $data = $this->objUser->withTrashed()->find($id); 
            $advertisements = DB::table('advertisements')->where('user_id',$id)->get()->pluck('id');
            if(!empty($advertisements)) {
                DB::table('advertisement_categories')->whereIn('advertisement_id',$advertisements)->delete();
                DB::table('advertisement_images')->whereIn('advertisement_id',$advertisements)->delete();
                DB::table('advertisement_videos')->whereIn('advertisement_id',$advertisements)->delete();
                DB::table('user_interest_in_advertisement')->whereIn('advertisement_id',$advertisements)->delete();
            }
            DB::table('user_interest_in_advertisement')->where('user_id',$id)->delete();
            DB::table('advertisements')->where('user_id',$id)->delete();
            DB::table('site_contents')->where('shared_by',$id)->delete();
            DB::table('site_users')->where('user_id',$id)->delete();
            DB::table('site_requests')->where('user_id',$id)->delete();

            Business::where('user_id',$id)->delete();

            $data->forceDelete();
            
            $this->log->info('Admin user have hard deleted successfully', array('admin_user_id' => Auth::id(),'user_id' => $id));
            return Redirect::to("admin/users")->with('success', trans('labels.userharddeletesuccessmsg'));
        } catch (\Throwable $th) {
            \Log::error($th);
            return Redirect::to("admin/users")->with('error', $th->getMessage());
        }
        
        
    }

    public function setUserActive($id)
    {
        $data = $this->objUser->withTrashed()->find($id);
        $data->deleted_at = NULL;
        $response = $data->save();
        if ($response) 
        {
           return 1;
        }
    }

    public function getOtpList()
    {
        $otpList = UserRegisterOTP::orderBy('id','desc')->limit(2000)->get();
        $users = User::select('id','phone','email','reset_password_otp','reset_password_otp_date','country_code')->orderBy('reset_password_otp_date','desc')->limit(2000);
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $users->whereHas('singlebusiness', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $users = $users->get();
        return view('Admin.ListOtp', compact('otpList','users'));
    }
    
    public function editOtp($id)
    {
        try
        {
            $id = Crypt::decrypt($id);
            $data = UserRegisterOTP::find($id);
           
            if($data) 
            {
                $this->log->info('Admin user otp edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return view('Admin.EditOtp', compact('data'));
            } else {
                $this->log->error('Admin something went wrong while user otp edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id));
                return Redirect::to("admin/otp")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            $this->log->error('Admin something went wrong while user otp edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
    }

    public function saveOtp(Request $request)
    {
        $postData = Input::all();
        $otpDetail = UserRegisterOTP::find($postData['id']);
        $otpDetail->otp = $postData['otp'];
        $otpDetail->save();
        if($request->input('action') == 'sendotp')
        {            
            $message = getRegMsg($otpDetail->otp);
            $response = Helpers::sendMessage($otpDetail->phone,$message);

            if($response) 
            {
                
                $this->log->info('API send OTP for registration successfully');
                return Redirect::to("admin/otp")->with('success', trans('labels.otpsendsuccessmsg')); 
            }
            else
            {
                $this->log->info('Something went wrong while send OTP for User registration');
                return Redirect::to("admin/otp")->with('error', trans('labels.otpsenderrormsg'));
            }
        }
        else
        {
            return Redirect::to("admin/otp")->with('success', trans('labels.otpsavesuccessmsg'));
        }
        
    }


    public function sendOtp($id,$type)
    {
        $appName = config('constant.APP_SHORT_NAME');

        if($type == 'single')
        {
            $id = Crypt::decrypt($id);

            $otpDetail = UserRegisterOTP::find($id);
            $msg = getRegMsg($otpDetail->otp);
            Helpers::sendMessage($otpDetail->phone,$msg);
        }
        elseif ($type == 'all') {
            
            $otpList = UserRegisterOTP::get();
            if(count($otpList) > 0)
            {
                foreach($otpList as $otp)
                {
                    $msg = getRegMsg($otp->otp);
                    Helpers::sendMessage($otp->phone,$msg);
                }
            }
        }

        $this->log->info('API send OTP for registration successfully');
        
        return Redirect::to("admin/otp")->with('success', trans('labels.otpsend')); 
        
    }

	/**
	 * for delete register OTP
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function deleteOtp($id)
	{
		try {
			UserRegisterOTP::whereId($id)->delete();
			return redirect()->route('otp.list')->with("success","OTP deleted successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while deleting OTP: ".$e);
			return redirect()->route('otp.list')->with("error",$e->getMessage());
		}
    }

    public function autoCompleteUser(Request $request)
    {
        $query = User::where('status',1)->where('name','<>','');

        if(Auth::check() && !Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $query->whereHas('singlebusiness', function ($q) {
                $q->whereRaw(Auth::user()->sql_query);
            });         
        }

        if(!empty($request->ids)) {
            $query->whereIn('id',$request->ids);
        }

        if(!empty($request->q)) {
            $postData['q'] = strtolower($request->q);
            $query->where(DB::raw('LOWER(name)'), 'LIKE', $postData['q']."%");
        }
        $users = $query->orderBy('name')
                            ->limit(10)
                            ->get(['id', DB::raw('name AS text')]);
        return response()->json($users, 200);
    }
}
