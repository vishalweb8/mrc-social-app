<?php

namespace App\Http\Controllers\Admin;

use App\AgentUser;
use App\AssetTypeField;
use App\Business;
use App\BusinessActivities;
use App\BusinessAddressAttributes;
use App\BusinessDoc;
use App\BusinessImage;
use App\BusinessWorkingHours;
use App\Category;
use App\EntityCustomField;
use App\EntityDescriptionLanguage;
use App\EntityDescriptionSuggestion;
use App\EntityKnowMore;
use App\EntityNearbyFilter;
use App\EntityReport;
use App\EntityVideo;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessAddRequest;
use App\Http\Requests\BusinessContactRequest;
use App\Http\Requests\BusinessRequest;
use App\Http\Requests\UserRequest;
use App\Metatag;
use App\NotificationList;
use App\OnlineStore;
use App\Owners;
use App\User;
use App\UserMetaData;
use App\UserRole;
use Auth;
use Cache;
use Config;
use Crypt;
use Cviebrock\EloquentSluggable\Services\SlugService;
use DB;
use File;
use Helpers;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Image;
use Input;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Redirect;
use Yajra\DataTables\Facades\DataTables;

class BusinessController extends Controller
{                   
    public function __construct()
    {
        $this->middleware('auth');
        $this->objUser = new User();
        $this->objOwners = new Owners();
        $this->objUserMetaData = new UserMetaData();
        $this->objUserRole = new UserRole();
        $this->objCategory = new Category();
        $this->objBusiness = new Business();
        $this->objBusinessWorkingHours = new BusinessWorkingHours();
        $this->objBusinessActivities = new BusinessActivities();
        $this->objBusinessAddressAttributes = new BusinessAddressAttributes();
        $this->objBusinessImage = new BusinessImage();
        $this->BUSINESS_ORIGINAL_IMAGE_PATH = Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH');
        $this->BUSINESS_THUMBNAIL_IMAGE_PATH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH');
        $this->BUSINESS_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_WIDTH');
        $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_HEIGHT');
        $this->BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH');

        
        $this->USER_ORIGINAL_IMAGE_PATH = Config::get('constant.USER_ORIGINAL_IMAGE_PATH');
        $this->USER_THUMBNAIL_IMAGE_PATH = Config::get('constant.USER_THUMBNAIL_IMAGE_PATH');
        $this->USER_PROFILE_PIC_WIDTH = Config::get('constant.USER_PROFILE_PIC_WIDTH');
        $this->USER_PROFILE_PIC_HEIGHT = Config::get('constant.USER_PROFILE_PIC_HEIGHT');
        
        $this->OWNER_ORIGINAL_IMAGE_PATH = Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_PATH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_WIDTH');
        $this->OWNER_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.OWNER_THUMBNAIL_IMAGE_HEIGHT');
        
        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('business-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));
        
        $this->catgoryTempImage = Config::get('constant.CATEGORY_TEMP_PATH');  
        ini_set('post_max_size', '1200M');
        ini_set('upload_max_filesize', '1200M');
        ini_set('memory_limit', '2400M');
        ini_set('max_execution_time', '1800');   
    }

    public function index($userId)
    {   
        try 
        {
            $userId = Crypt::decrypt($userId);
            $userDetail = User::find($userId); 
            $this->log->info('Admin business listing page', array('admin_user_id' => Auth::id(), 'user_id' => $userId));
            return view('Admin.ListBusiness', compact('userId','userDetail'));
        } catch (DecryptException $e){
            $this->log->error('Admin something went wrong while business listing page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'error' => $e->getMessage()));
            return view('errors.404');
        }        
    }

    public function add($userId)
    {
        try 
        {
            $userId = Crypt::decrypt($userId);
            $userDetail = User::find($userId);
            $parentCategories = $this->objCategory->getAll(array('parent' => '0'));
            
            $this->log->info('Admin business add page', array('admin_user_id' => Auth::id(), 'user_id' => $userId));
            
            return view('Admin.AddBusiness', compact('userId', 'parentCategories', 'userDetail'));
        } catch (DecryptException $e){
            $this->log->error('Admin something went wrong while business add page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'error' => $e->getMessage()));
            return view('errors.404');
        }        
    }

    public function edit($id)
    {
        try 
        {           
            $id = Crypt::decrypt($id);
            $data = $this->objBusiness->find($id);

            $businessImages = (isset($data->businessImages) && !empty($data->businessImages)) ? $data->businessImages->toArray() : [];
            
            $userId = $data->user_id;
            $userDetail = User::find($userId);
            $parentCategories = $this->objCategory->getAll(array('parent' => '0'));
            
            $agentUsers = AgentUser::with('user')->get();
            $businessDoc = DB::table('business_doc')->where('business_id',$id)->where('deleted_at',null)->get();
            $businessWorkingHoursData = '';
            $stores = OnlineStore::whereStatus(1)->orderBy('name')->get();
            if($data) 
            {
                if(Auth::user()->agent_approved == 0)
                {
                    $this->log->info('Admin business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id));
                    return view('Admin.Entity.create', compact('userId','parentCategories','data','businessImages','userDetail','businessWorkingHoursData','agentUsers','businessDoc','stores'));
                }
                elseif (Auth::user()->agent_approved == 1) {
                    if($data->agent_user == Auth::id())
                    {
                        $this->log->info('Admin business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id));
                        return view('Admin.Entity.create', compact('userId','parentCategories','data','businessImages','userDetail','businessWorkingHoursData','agentUsers','businessDoc','stores'));
                    }
                    else
                    {
                       $this->log->error('Admin something went wrong while business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id, 'error' => 'not allow to open business'));
                        return view('errors.404');
                    }
                }
            } 
            else 
            {
                $this->log->error('Admin something went wrong while business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id));
                return Redirect::to("admin/users")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e){
            $this->log->error('Admin something went wrong while business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
    }

    public function save(BusinessAddRequest $request)
    {

        $postData = Input::all();

        $this->validate($request,[
                    'latitude'=>'required',
                    'longitude' => 'required',
                    'category_id' => 'required',
                ],
                [
                    'latitude.required'=>'Please enter proper address to get proper latitude',
                    'longitude.required' => 'Please enter proper address to get proper longitude',
                    'category_id.required' => 'Please select category'
                ]);
        
        if($postData['id'] == 0 || !isset($postData['id']))
        {
            $businessSlug = SlugService::createSlug(Business::class, 'business_slug', $postData['name']);
            $postData['business_slug'] = (isset($businessSlug) && !empty($businessSlug)) ? $businessSlug : NULL;
        } 
        
        if(isset($postData['category_id']) && !empty($postData['category_id']))
        {
            
            $postData['category_id'] = array_filter(array_unique($postData['category_id']));

            $postData['category_id'] = implode(',',$postData['category_id']);
            
            $postData['category_hierarchy'] = Helpers::getCategoryHierarchy($postData['category_id']);

            $postData['parent_category'] = Helpers::getParentCategoryIds($postData['category_id']);
        }

        $promoted =  (!isset($postData['promoted'])) ? 0 : 1;
        $postData['promoted'] = $promoted;
        if(isset($postData['metatags']))
        {
            $explodeTags = explode(',',$postData['metatags']);
            if(count($explodeTags) > 0){
                foreach($explodeTags as $tag)
                {
                    Metatag::firstOrCreate(array('tag' => $tag));
                }
            }
        }
        
        if (Input::file('business_logo')) 
        {  
            $logo = Input::file('business_logo'); 

            if (!empty($logo)) 
            {
                $fileName = 'business_logo_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $pathThumb = (string) Image::make($logo->getRealPath())->resize($this->BUSINESS_THUMBNAIL_IMAGE_WIDTH, $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT)->encode();
                
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $pathOriginal, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");

                $postData['business_logo'] = $fileName;
            }
        }

         

        if(Auth::user()->agent_approved == 1)
        {
            $postData['agent_user'] = Auth::id();
        }
        $response = $this->objBusiness->insertUpdate($postData);
        
        if($postData['id'] == 0 && $response)
        {
            $userData = User::find($postData['user_id']); 
            if($userData)
            {
                $ownerInsert = [];
                $ownerInsert['id'] = 0;
                $ownerInsert['business_id'] = $response->id;
                $ownerInsert['full_name'] = $userData->name;
                $ownerInsert['gender'] = $userData->gender;
                $ownerInsert['dob'] = $userData->dob;
                $ownerInsert['email_id'] = $userData->email;
                if($userData->profile_pic && !empty($userData->profile_pic))
                {
                    $imageArray = Helpers::getProfileExtraFields($userData);   
                    if($imageArray && !empty($imageArray['profile_pic_original']) && !empty($imageArray['profile_pic_original']))
                    {
                        $userOriginalImgPath = $imageArray['profile_pic_original'];
                        $userThumbnailImgPath = $imageArray['profile_pic_thumbnail'];
                        
                        $userOriginalImageInfo = pathinfo($userOriginalImgPath);
                        $userThumbnailImageInfo = pathinfo($userThumbnailImgPath);
                        
                        $ownereExtension = $userOriginalImageInfo['extension'];
                        $ownerFileName = 'owner_'.uniqid().'.'.$ownereExtension;
                        
                        $ownerOriginalImgPath = public_path($this->OWNER_ORIGINAL_IMAGE_PATH.$ownerFileName);
                        $ownerThumbnailImgPath = public_path($this->OWNER_THUMBNAIL_IMAGE_PATH.$ownerFileName);
                                                
                        File::copy($userOriginalImgPath, $ownerOriginalImgPath);
                        File::copy($userThumbnailImgPath, $ownerThumbnailImgPath);

                        //Uploading on AWS
                        $originalOwnerImage = Helpers::addFileToStorage($ownerFileName, $this->OWNER_ORIGINAL_IMAGE_PATH, $ownerOriginalImgPath, "s3");
                        $thumbOwnerImage = Helpers::addFileToStorage($ownerFileName, $this->OWNER_THUMBNAIL_IMAGE_PATH, $ownerThumbnailImgPath, "s3");
                        
                        //Deleting Local Files
                        File::delete($ownerOriginalImgPath, $ownerThumbnailImgPath);
                        $ownerInsert['photo'] = $ownerFileName;
                    }
                    else{
                        $ownerInsert['photo'] = NULL;
                    }
                }
                else
                {
                     $ownerInsert['photo'] = NULL;
                }
                 $ownerSave = $this->objOwners->insertUpdate($ownerInsert);
            }
        }
        
        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        $businessId = ($postData['id'] == 0) ? $response->id : $postData['id'];

        //Store business Attributes
        if($postData['latitude'] != $postData['hidden_latitude'] && $postData['longitude'] != $postData['hidden_longitude']) {
            $business_address_attributes = Helpers::getAddressAttributes($postData['latitude'], $postData['longitude']);
            $business_address_attributes['business_id'] = $businessId;
            $businessObject = Business::find($businessId);
            if (!$businessObject->business_address) {
                $businessObject->business_address()->create($business_address_attributes);
            } else {
                $businessObject->business_address()->update($business_address_attributes);
            }
        }


        if(isset($postData['id']) && $postData['id'] == 0)
        {
            $this->validate($request,
                ['business_images' => 'required',
                'business_images.*' => 'image|mimes:jpeg,png,jpg|max:5120'],
                ['business_images.*.max' => 'File size must be less than 5 MB']
            );
        }
        else
        {
            $this->validate($request,
                ['business_images.*' => 'image|mimes:jpeg,png,jpg|max:5120'],
                ['business_images.*.max' => 'File size must be less than 5 MB']
            );
        }

        if (Input::file()) 
        {  
            $business_images = Input::file('business_images');

            $imageArray = [];

            if (!empty($business_images) && count($business_images) > 0) 
            {
                foreach($business_images as $business_image)
                {
                    $fileName = 'business_' . uniqid() . '.' . $business_image->getClientOriginalExtension();

                    $pathThumb = (string) Image::make($business_image->getRealPath())->resize(100, 100)->encode();
                    
                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $business_image, "s3");
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                    
                    $this->log->info('Admin business image original and thumb image deleted successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId, 'imageName' => $fileName));
                    
                    BusinessImage::firstOrCreate(['business_id' => $businessId , 'image_name' => $fileName]);

                }
            }
        }

        if ($response) 
        {
            $this->log->info('Admin business added/updated successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            //insert/update working hours
            $businessWorkingHoursData = Helpers::setWorkingHours($postData);
            $businessWorkingHoursData['business_id'] = $businessId;
            $businessWorkingHoursData['id'] = $postData['working_hours_id'];
            $businessWorkingHoursData['timezone'] = $postData['timezone'];

            $this->objBusinessWorkingHours->insertUpdate($businessWorkingHoursData);
            
            //insert/update activities
            
            if(isset($postData['add_activity_title']) && !empty($postData['add_activity_title']))
            {
                foreach(array_filter($postData['add_activity_title']) as $key=>$activity)
                {
                    $activityArray = [];
                    $activityArray['business_id'] = $businessId;
                    $activityArray['activity_title'] = $activity;
                    $this->objBusinessActivities->insertUpdate($activityArray);
                }
            }

            if(isset($postData['deleted_activities']) && !empty($postData['deleted_activities']))
            {
                foreach($postData['deleted_activities'] as $activity)
                {
                    $data = $this->objBusinessActivities->find($activity);
                    $data->delete();
                }
            }

            if(isset($postData['update_activity_title']) && !empty($postData['update_activity_title']))
            {
                foreach($postData['update_activity_title'] as $key=>$activity)
                {
                    $activityArray = [];
                    $activityArray['business_id'] = $businessId;
                    $activityArray['activity_title'] = $activity;
                    $activityArray['id'] = $postData['update_activity_id'][$key];
                    $this->objBusinessActivities->insertUpdate($activityArray);
                }
            }
            
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('success', trans('labels.businesssuccessmsg'));
        } else {
            $this->log->error('Admin something went wrong while adding/updating business', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $businessId));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }
    }

    public function saveContactInfo(BusinessContactRequest $request)
    {
        $postData = Input::all();

        $this->validate($request,[
                    'latitude'=>'required',
                    'longitude' => 'required'
                ],
                [
                    'latitude.required'=>'Please enter proper address to get proper latitude',
                    'longitude.required' => 'Please enter proper address to get proper longitude'
                ]);

        $response = $this->objBusiness->insertUpdate($postData);

        $businessId = ($postData['id'] == 0) ? $response->id : $postData['id'];

        //Store business Attributes
        if($postData['latitude'] != $postData['hidden_latitude'] && $postData['longitude'] != $postData['hidden_longitude']) 
        {
            $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $postData['latitude'] . "," . $postData['longitude'] . '&sensor=false&libraries=places');
            $sample_array = ["premise", "street_number", "route", "neighborhood", "sublocality_level_3", "sublocality_level_2", "sublocality_level_1", "locality", "administrative_area_level_2", "administrative_area_level_1", "country", "postal_code"];
            $output = json_decode($geocode);
            $premise = $sublocality_level_1 = $locality = $administrative_area_level_1 = $route = $street_number = $sublocality_level_2 = $sublocality_level_3 = $administrative_area_level_2 = $country = $postal_code = $neighborhood = $address = '';
            $business_address_attributes = [];
            if (!empty($output->results)) {
                for ($j = 0; $j < count($output->results[0]->address_components); $j++) {
                    for ($i = 0; $i < count($sample_array); $i++) {
                        if ($sample_array[$i] == $output->results[0]->address_components[$j]->types[0]) {
                            $set = $sample_array[$i];
                            //Getting value from associative variable premise, country etc all attribute using $$set
                            $$set = $output->results[0]->address_components[$j]->long_name;
                            $business_address_attributes[$set] = $output->results[0]->address_components[$j]->long_name;
                        }
                    }

                }
            }

            $business_address_attributes['business_id'] = $businessId;
            $businessObject = Business::find($businessId);
            if (!$businessObject->business_address) {
                $businessObject->business_address()->create($business_address_attributes);
            } else {
                $businessObject->business_address()->update($business_address_attributes);
            }
        }
        
        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        if ($response) 
        {
            $this->log->info('Admin business contact info save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.contactdetailsuccessmsg'));
        } 
        else 
        {
            $this->log->error('Admin something went wrong while save business contact info', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }
    }

    public function saveWorkingHours()
    {
        $postData = Input::all();

         //insert/update working hours
        $businessWorkingHoursData = Helpers::setWorkingHours($postData);
        $businessWorkingHoursData['business_id'] = $postData['id'];
        $businessWorkingHoursData['id'] = $postData['working_hours_id'];
        $businessWorkingHoursData['timezone'] = $postData['timezone'];

        $response = $this->objBusinessWorkingHours->insertUpdate($businessWorkingHoursData);

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        if ($response) 
        {
            $this->log->info('Admin business working hours save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.workinghourssuccessmsg'));
        } 
        else 
        {
            $this->log->error('Admin something went wrong while save business working hours', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }
    }

    public function saveSocialProfiles(Request $request)
    {
        $postData = $request->all();

        // if($postData['url_slug']){
            
        //      $title = Str::slug($postData['url_slug'], '-');
        //      $postData['url_slug']  =  $title;
        // }

        $response = $this->objBusiness->insertUpdate($postData);

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        if ($response) 
        {
            $this->log->info('Admin business social profiles save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.socialprofilesuccessmsg'));
        } else {
            $this->log->error('Admin something went wrong while save business social profiles', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }

    }
    
    /**
     * for save online store detail
     *
     * @param  mixed $request
     * @return void
     */
    public function saveOnlineStores(Request $request)
    {
        $postData = $request->all();
        try {
            $stores = [];
            $onlineStoreIds = $request->input('online_store_id',[]);
            foreach ($onlineStoreIds as $key => $value) {
                $stores[$key]['id'] = $value;
                $stores[$key]['url'] = isset($request->online_store_url[$key]) ? $request->online_store_url[$key] : "";
            }
            if(!empty($stores)) {
                $onlineStore = json_encode($stores);
            } else {
                $onlineStore = null;
            }
            $business = Business::findOrFail($request->id);
            $business->online_store_url = $onlineStore;
            $business->save();

            $this->log->info('Admin business online stores save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.online_store_success_message'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while save business online stores', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id']));
            $this->log->error($th);
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', $th->getMessage());
        }
    }
    
    /**
     * for upload entity video
     *
     * @param  mixed $request
     * @return void
     */
    public function saveEntityVideo(Request $request)
    {
        ini_set('post_max_size', '1200M');
        ini_set('upload_max_filesize', '1200M');
        ini_set('memory_limit', '2400M');
        ini_set('max_execution_time', '1800');   
        $request->validate([
            'title'=>'required',
            'thumbnail.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'video.*' => 'mimes:mp4,mov,wmv,avi,avchd,flv,f4v,swf,mkv|max:1048576'
        ],[
            'thumbnail.*.max' => 'File size must be less than 2 MB',
            'thumbnail.*.image' => 'The thumbnail must be an image.',
            'thumbnail.*.mimes' => 'The thumbnail must be a file of type: jpeg,png,jpg.',
            'video.*.mimes' => 'The video must be a file of type: mp4,mov,wmv,avi,avchd,flv,f4v,swf,mkv.',
            'video.*.max' => 'File size must be less than 1 GB'
        ]);
        try {
            $titles = $request->input('title',[]);
            $deletedVideos = $request->input('deletedVideos',[]);
            $createdBy = auth()->id();
            foreach ($titles as $key => $title) {
                $id = isset($request->id[$key]) ? $request->id[$key] : 0;
                $query = ['id' => $id];
                $entityVideo = EntityVideo::firstOrNew($query);
                $entityVideo->entity_id = $request->entity_id;
                $entityVideo->created_by = $createdBy;
                $entityVideo->title = $title;
                $entityVideo->description = isset($request->description[$key]) ? $request->description[$key] : "";

                if(isset($request->thumbnail[$key]) && !empty($request->thumbnail[$key])) {
                    $thumbnail = $request->file('thumbnail')[$key];
                    $fileName = 'thumbnail_' . uniqid() . '.' . $thumbnail->getClientOriginalExtension();
                    $path = config('constant.VIDEO_PATH').'thumbnail/';
                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $path, $thumbnail, "s3");
                    $oldFilePath = $entityVideo->thumbnail;
                    $entityVideo->thumbnail  = $path.$originalImage;
                    if($id) {
                        deleteFile($oldFilePath);
                    }
                }
                if(isset($request->video[$key]) && !empty($request->video[$key])) {
                    $video = $request->file('video')[$key];
                    $fileName = 'video_' . uniqid() . '.' . $video->getClientOriginalExtension();
                    $path = config('constant.VIDEO_PATH').'video/';
                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $path, $video, "s3");
                    $oldFilePath = $entityVideo->video;
                    $entityVideo->video = $path.$originalImage;                    
                    if($id) {
                        deleteFile($oldFilePath);
                    }
                }
                $entityVideo->save();
            }

            // for delete entity videos
            if(!empty($deletedVideos)) {
                $deletedVideos = EntityVideo::select('id','thumbnail','video')->whereIn('id',$deletedVideos)->get();
                foreach($deletedVideos as $value) {
                    deleteFile($value->thumbnail);
                    deleteFile($value->video);
                    $value->delete();
                }
            }

            $this->log->info('Admin entity video save successfully', array('admin_user_id' =>  Auth::id(),  'business_id' => $request->entity_id));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('success', trans('labels.videos_success_message'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while save entity videos', array('admin_user_id' =>  Auth::id()));
            $this->log->error($th);
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('error', $th->getMessage());
        }
    }
    
    /**
     * for save know more of entity
     *
     * @param  mixed $request
     * @return void
     */
    public function saveKnowMore(Request $request)
    {
        try {
            $titles = $request->input('title',[]);
            $language = $request->input('language','english');
            EntityKnowMore::where(['entity_id' => $request->entity_id])->where('language',$language)->delete();
            foreach ($titles as $key => $title) {
                $data = [
                    'entity_id' => $request->entity_id,
                    'language' => $language,
                    'title' => $title,
                    'description' => isset($request->description[$key]) ? $request->description[$key] : "",
                ];
                EntityKnowMore::create($data);
            }

            $this->log->info('Admin entity know more save successfully', array('admin_user_id' =>  Auth::id(),  'business_id' => $request->entity_id));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('success', trans('labels.know_more_success_message'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while save entity know more', array('admin_user_id' =>  Auth::id()));
            $this->log->error($th);
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('error', $th->getMessage());
        }
    }
    
    /**
     * for save custom detail of entity
     *
     * @param  mixed $request
     * @return void
     */
    public function saveCustomDetail(Request $request)
    {
        try {
            $titles = $request->input('title',[]);
            $language = $request->input('language','english');
            EntityCustomField::where(['entity_id' => $request->entity_id])->where('language',$language)->delete();
            foreach ($titles as $key => $title) {
                $data = [
                    'entity_id' => $request->entity_id,
                    'language' => $language,
                    'title' => $title,
                    'description' => isset($request->description[$key]) ? $request->description[$key] : "",
                ];
                EntityCustomField::create($data);
            }

            $this->log->info('Admin entity custom detail save successfully', array('admin_user_id' =>  Auth::id(),  'business_id' => $request->entity_id));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('success', trans('labels.custom_detail_success_message'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while save entity custom detail', array('admin_user_id' =>  Auth::id()));
            $this->log->error($th);
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('error', $th->getMessage());
        }
    }
    
    /**
     * store near by filter for entity
     *
     * @param  mixed $request
     * @return void
     */
    public function saveNearByFilter(Request $request)
    {
        try {
            $titles = $request->input('title',[]);
            EntityNearbyFilter::where(['entity_id' => $request->entity_id])->delete();
            foreach ($titles as $key => $title) {
                if(isset($request->asset_type_id[$key]) && !empty($request->asset_type_id[$key])) {
                    $assetIds = implode(",",$request->asset_type_id[$key]);
                } else {
                    $assetIds = null;
                }
                $data = [
                    'entity_id' => $request->entity_id,
                    'title' => $title,
                    'asset_type_id' => $assetIds,
                    'sql_query' => isset($request->sql_query[$key]) ? $request->sql_query[$key] : null,
                   // 'is_enable_filter' => isset($request->is_enable_filter[$key]) ? 1 : 0,
                    'top_limit' => isset($request->top_limit[$key]) ? $request->top_limit[$key] : 5,
                ];
                EntityNearbyFilter::create($data);
            }

            $this->log->info('Admin entity near by filter save successfully', array('admin_user_id' =>  Auth::id(),  'business_id' => $request->entity_id));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('success', trans('labels.nearby_detail_success_message'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while save entity near by filter', array('admin_user_id' =>  Auth::id()));
            $this->log->error($th);
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($request->entity_id))->with('error', $th->getMessage());
        }
    }

    public function savePublicWebsite(Request $request)
    {
        $postData = $request->all();

        if($postData['url_slug']){
            
             $title = Str::slug($postData['url_slug'], '-');
             $postData['url_slug']  =  $title;
        }

        $response = $this->objBusiness->insertUpdate($postData);

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        if ($response) 
        {
            $this->log->info('Admin business social profiles save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.socialprofilesuccessmsg'));
        } else {
            $this->log->error('Admin something went wrong while save business social profiles', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }

    }

    public function saveBusinessInfo(BusinessRequest $request)
    {
        $postData = Input::all();
        if(isset($postData['id']) && $postData['id'] == 0)
        {
            $this->validate($request,
                ['business_images' => 'required',
                'business_images.*' => 'image|mimes:jpeg,png,jpg|max:5120'],
                ['business_images.*.max' => 'File size must be less than 5 MB']
            );
        }
        else
        {
            $this->validate($request,
                ['business_images.*' => 'image|mimes:jpeg,png,jpg|max:5120'],
                ['business_images.*.max' => 'File size must be less than 5 MB']
            );
        }

        $promoted =  (!isset($postData['promoted'])) ? 0 : 1;
        $postData['promoted'] = $promoted;

        if($postData['id'] == 0 || !isset($postData['id']))
        {
            $businessSlug = SlugService::createSlug(Business::class, 'business_slug', $postData['name']);
            $postData['business_slug'] = (isset($businessSlug) && !empty($businessSlug)) ? $businessSlug : NULL;
        }

        if($postData['user_id'] == 0 && !empty($request->business_user))
        {
            $isExists = Business::where('user_id',$request->business_user)->exists();
            if($isExists) {
                return redirect()->back()->withErrors('Business already assigned to this user.');
            }
            $postData['user_id']  = $request->business_user;
        }

        if (Input::file('business_logo')) 
        {  
            $logo = Input::file('business_logo'); 

            if (!empty($logo)) 
            {
                $fileName = 'business_logo_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $pathThumb = (string) Image::make($logo->getRealPath())->resize($this->BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH, $this->BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT)->encode();
                
                if(isset($postData['old_business_logo']) && $postData['old_business_logo'] != '')
                {
                    $originalImageDelete = Helpers::deleteFileToStorage($postData['old_business_logo'], $this->BUSINESS_ORIGINAL_IMAGE_PATH, "s3");
                    $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_business_logo'], $this->BUSINESS_THUMBNAIL_IMAGE_PATH, "s3");
                }
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $logo, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");

                $this->log->info('Image Upload', array('Image' => $originalImage));

                $postData['business_logo'] = $fileName;
            }
        }

        $response = $this->objBusiness->insertUpdate($postData);

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        $businessId = ($postData['id'] == 0) ? $response->id : $postData['id'];

        if($postData['membership_type'] == 1 && $postData['membership_type'] != $postData['old_membership_type']) {
            //Send push notification to Business User & Agent
            $businessDetail = Business::find($businessId);
            if(!empty($businessDetail->user)) {
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

                if($businessDetail->user_id != $businessDetail->created_by) {
                    if($businessDetail->created_by != '' && $businessDetail->created_by > 1)
                    {
                        $notificationData['message'] = 'Dear '.$businessDetail->businessCreatedBy->name.',   Congratulations! Your Customer\'s membership has been upgraded to Premium. Your customer will now be able to utilize all premium features.';
                        Helpers::sendPushNotification($businessDetail->created_by, $notificationData);

                        $notificationListArray = [];
                        $notificationListArray['user_id'] = $businessDetail->created_by;
                        $notificationListArray['business_id'] = $businessDetail->id;
                        $notificationListArray['title'] = 'Membership Upgrade';
                        $notificationListArray['message'] =  'Dear '.$businessDetail->businessCreatedBy->name.',   Congratulations! Your Customer\'s membership has been upgraded to Premium. Your customer will now be able to utilize all premium features.';
                        $notificationListArray['type'] = '9';
                        $notificationListArray['business_name'] = $businessDetail->name;
                        $notificationListArray['user_name'] = $businessDetail->businessCreatedBy->name;

                        NotificationList::create($notificationListArray);
                    }
                    
                }
            }
        }
        if(!empty($request->descriptions)) {
            foreach($request->descriptions as $key => $description) {
                $data = [
                    'entity_id' => $businessId,
                    'language' => $key,
                    'description' => $description
                ];
                EntityDescriptionLanguage::updateOrCreate([
                    'entity_id' => $businessId,
                    'language' => $key
                ],$data);
            }
        }

        if(!empty($request->short_descriptions)) {
            foreach($request->short_descriptions as $key => $description) {
                $data = [
                    'entity_id' => $businessId,
                    'language' => $key,
                    'short_description' => $description
                ];
                EntityDescriptionLanguage::updateOrCreate([
                    'entity_id' => $businessId,
                    'language' => $key
                ],$data);
            }
        }

        if (Input::file()) 
        {  
            $business_images = Input::file('business_images');

            $imageArray = [];

            if (!empty($business_images) && count($business_images) > 0) 
            {
                foreach($business_images as $business_image)
                {
                    $fileName = 'business_' . uniqid() . '.' . $business_image->getClientOriginalExtension();
                    $pathThumb = (string) Image::make($business_image->getRealPath())->resize(100, 100)->encode();

                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $business_image, "s3");
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                    
                    BusinessImage::firstOrCreate(['business_id' => $businessId , 'image_name' => $fileName]);

                }
            }
        }

        if ($response) 
        {
            $this->log->info('Admin business info save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($businessId))->with('success', trans('labels.businesssuccessmsg'));
        } else {
            $this->log->error('Admin something went wrong while save business info', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $businessId));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }
    }

    public function saveSocialActivities()
    {
        $postData = Input::all();
        
//      insert/update activities
            
        if(isset($postData['add_activity_title']) && !empty($postData['add_activity_title']))
        {
            foreach(array_filter($postData['add_activity_title']) as $key=>$activity)
            {
                $activityArray = [];
                $activityArray['business_id'] = $postData['id'];
                $activityArray['activity_title'] = $activity;
                $this->objBusinessActivities->insertUpdate($activityArray);
            }
        }

        if(isset($postData['deleted_activities']) && !empty($postData['deleted_activities']))
        {
            foreach($postData['deleted_activities'] as $activity)
            {
                $data = $this->objBusinessActivities->find($activity);
                $data->delete();
            }
        }

        if(isset($postData['update_activity_title']) && !empty($postData['update_activity_title']))
        {
            foreach($postData['update_activity_title'] as $key=>$activity)
            {
                $activityArray = [];
                $activityArray['business_id'] = $postData['id'];
                $activityArray['activity_title'] = $activity;
                $activityArray['id'] = $postData['update_activity_id'][$key];
                $this->objBusinessActivities->insertUpdate($activityArray);
            }
        }

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.socialactivitiessuccessmsg'));
    }

    public function saveCategryHierarchy()
    {
        $postData = Input::all();

        if(isset($postData['category_id']) && !empty($postData['category_id']))
        {

            $postData['category_id'] = array_filter(array_unique($postData['category_id']));

            $postData['category_id'] = implode(',',$postData['category_id']);
            
            $postData['category_hierarchy'] = Helpers::getCategoryHierarchy($postData['category_id']);

            $postData['parent_category'] = Helpers::getParentCategoryIds($postData['category_id']);

        }

        if(isset($postData['metatags']))
        {
            $explodeTags = explode(',',$postData['metatags']);
            if(count($explodeTags) > 0){
                foreach($explodeTags as $tag)
                {
                    Metatag::firstOrCreate(array('tag' => $tag));
                }
            }
        }
        
        $response = $this->objBusiness->insertUpdate($postData);

        Cache::forget('membersForApproval');
        Cache::forget('businessesData');

        if ($response) {
            $this->log->info('Admin business categry hierarchy save', array('admin_user_id' => Auth::id(), 'user_id' => $postData['user_id']));
            return Redirect::to("admin/user/business/edit/".Crypt::encrypt($postData['id']))->with('success', trans('labels.categorysuccessmsg'));
        } else {
            $this->log->error('Admin something went wrong while save business categry hierarchy', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/user/business/".Crypt::encrypt($postData['user_id']))->with('error', trans('labels.businesserrormsg'));
        }
    }

    public function delete($id)
    {
        $id = Crypt::decrypt($id);
        $data = $this->objBusiness->find($id);
        $businessImageData = $this->objBusinessImage->getBusinessImagesByBusinessId($id)->toArray();
        
        $response = $data->delete();

        if ($response) 
        {
            if(!empty($businessImageData))
            { 
                foreach ($businessImageData as $businessImage) 
                {
                    $originalImageDelete = Helpers::deleteFileToStorage($businessImage['image_name'], $this->BUSINESS_ORIGINAL_IMAGE_PATH, "s3");
                    $thumbImageDelete = Helpers::deleteFileToStorage($businessImage['image_name'], $this->BUSINESS_THUMBNAIL_IMAGE_PATH, "s3");
                }
            }
            $this->log->info('Admin business delete', array('admin_user_id' => Auth::id(), 'user_id' => $data->user_id, 'business_id' => $id));
            return Redirect::back()->with('success', trans('labels.businessdeletesuccessmsg'));
        }
    }

    public function addCategotyHierarchy()
    {
        $catId = Input::get('catId');
        $categoryHierarchy = array_reverse(Helpers::getCategoryReverseHierarchy($catId));
        return view('Admin.BusinessCategorySelectionTemplate', compact('catId','categoryHierarchy'));
    }

    public function getSubCategotyById()
    {
        $categoryIds = Input::get('categoryIds');
        $level = Input::get('level');
        $categoryArray = [];
        $categoryHierarchy = [];
       
        
        if(!empty(array_filter($categoryIds))) {

            $categoryArray = $this->objCategory->getAll(['parentIn' => $categoryIds]);
            
            if(isset($categoryIds[0]) && $categoryIds[0] != ''){
                $categoryHierarchy = array_reverse(Helpers::getCategoryReverseHierarchy($categoryIds[0]));
            }
        } 
        
        if(isset($categoryArray) && count($categoryArray) > 0){            
            return view('Admin.CategoriesTemplate', compact('categoryArray', 'level','categoryHierarchy'));
        }
    }
    public function getBusinessMetaTags()
    {
        $categoryId = Input::get('categoryId');
        if(is_array($categoryId) && !empty($categoryId)) {
            $categories = Category::whereIn('id',$categoryId)->get();
            $metaTags = [];
            foreach($categories as $category) {
                if(!empty($category->metatags)) {
                    $metaTags = array_merge($metaTags,explode(',', $category->metatags));
                }
            }
            return $metaTags;
        } else {        
            $metatags = Category::find($categoryId)->metatags;

            if($metatags != '')
            {
                return explode(',', $metatags);
            }
            else
            {
                return [];
            }
        }       
    }

    public function setStatusBusinessApproved()
    {
        $businessId = Input::get('businessId');
        $postData['id'] = $businessId;
        $postData['approved'] = 1;
        $this->objBusiness->insertUpdate($postData);

        //Send push notification to Business User
        $businessDetail = Business::find($businessId);
        if(!empty($businessDetail->user)) {

            $notificationData = [];
            $notificationData['title'] = 'Business Approved';
            $notificationData['message'] = 'Dear '.$businessDetail->user->name.',  Good News! Your Business Profile just got approved. Close and Open App to see changes.';
            $notificationData['type'] = '8';
            $notificationData['business_id'] = $businessDetail->id;
            $notificationData['business_name'] = $businessDetail->name;
            Helpers::sendPushNotification($businessDetail->user_id, $notificationData);
            
            // notification list 
            $notificationListArray = [];
            $notificationListArray['user_id'] = $businessDetail->user_id;
            $notificationListArray['business_id'] = $businessDetail->id;
            $notificationListArray['title'] = 'Business Approved';
            $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',  Good News! Your Business Profile just got approved. Close and Open App to see changes.';
            $notificationListArray['type'] = '8';
            $notificationListArray['business_name'] = $businessDetail->name;
            $notificationListArray['user_name'] = $businessDetail->user->name;
            
            NotificationList::create($notificationListArray);
        }

        if($businessDetail->user_id != $businessDetail->created_by && !empty($businessDetail->businessCreatedBy)) {
            $notificationData['message'] = 'Dear '.$businessDetail->businessCreatedBy->name.',  Good News! Your Customer\'s Business Profile just got approved';
            Helpers::sendPushNotification($businessDetail->created_by, $notificationData); 

            // notification list 
            $notificationListArray = [];
            $notificationListArray['user_id'] = $businessDetail->created_by;
            $notificationListArray['business_id'] = $businessDetail->id;
            $notificationListArray['title'] = 'Business Approved';
            $notificationListArray['message'] =  'Dear '.$businessDetail->businessCreatedBy->name.',  Good News! Your Customer\'s Business Profile just got approved';
            $notificationListArray['type'] = '8';
            $notificationListArray['business_name'] = $businessDetail->name;
            $notificationListArray['user_name'] = $businessDetail->businessCreatedBy->name;

            NotificationList::create($notificationListArray);   
        }

        Cache::forget('membersForApproval');
        return 1;
    }

    public function businessRejected()
    {
        $businessId = Input::get('businessId');
        $data = $this->objBusiness->find($businessId);
        // $response = $data->delete();
        /**
         * @date: 22nd Aug, 2018
         * We don't want to delete the rejected business, instead of that we like to show that business as rejected flag in admin
         * It was requested by Mr. Mahipalsihn
         */
        $postData['id'] = $businessId;        
        /**
         * 0 = Pending
         * 1 = Approved
         * 2 = Rejected
         */
        $postData['approved'] = 2; 
        $this->objBusiness->insertUpdate($postData);

        Cache::forget('membersForApproval');
        return 1;
    }
    public function removeBusinessImage()
    {
        $businessImageId = Input::get('businessImageId');
        $data = $this->objBusinessImage->find($businessImageId);
        if($data)
        {
            $response = $data->delete();
            $originalImageDelete = Helpers::deleteFileToStorage($data->image_name, $this->BUSINESS_ORIGINAL_IMAGE_PATH, "s3");
            $thumbImageDelete = Helpers::deleteFileToStorage($data->image_name, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, "s3");
        }
        return 1;
    }

    public function getAllPromotedBusinesses()
    {
       if (Cache::has('businessesData')){
            $businessesData = Cache::get('businessesData');
        } else {
            $businessesData = $this->objBusiness->getAll(['approved' => 1,'promoted' => 1]);
            Cache::put('businessesData', $businessesData, 60);
        }
       return view('Admin.ListPromotedBusinesses', compact('businessesData'));
    }
    
    public function updatePromotedBusinesses(Request $request) 
    {
        if(isset($request->businessId) && isset($request->promotedBusiness))
        {
            $response = Business::where('id', $request->businessId)->update(['promoted' => $request->promotedBusiness]);
            Cache::forget('businessesData');
            echo ($response) ? 1 : 0;
        }
        else
        {
            $response = '';
            echo 0;
        }
//      return $response;
    }

    public function getAllBusinesses(Request $request)
    {   
        $postData = $request->all();        
        $postData = array_map('trim', $postData);

        if(Auth::user()->agent_approved == 1)
        {
           // $businessList = Business::has('user')->where('agent_user', Auth::id())->get();
           if((isset($postData['searchText']) && $postData['searchText'] != '') || (isset($postData['type']) && $postData['type'] != ''))
            {   
                $businessList = $this->objBusiness->businessFilter($postData, $pagination=true);
            }
            else
            {
                $businessList = $this->objBusiness->getAllForAdmin(['agent_user' => Auth::id()],true);
            }
        }
        else
        {
            $filters = [];
            if((isset($postData['fieldtype']) && $postData['fieldtype'] != '') || 
               (isset($postData['fieldcheck']) && $postData['fieldcheck'] != ''))
            {
                $filters['fieldtype'] = (isset($postData['fieldtype']) && $postData['fieldtype'] != '') ? $postData['fieldtype'] : '';
                $filters['searchText'] = (isset($postData['searchText']) && $postData['searchText'] != '') ? $postData['searchText'] : '';
                $filters['approved'] = (isset($postData['approved']) && $postData['approved'] != '') ? $postData['approved'] : '';

                // if($postData['fieldtype'] == 'city')
                //     $filters['fieldtype'] = $postData['fieldtype'];
                // if($postData['fieldtype'] == 'category_id')
                //     $filters['fieldtype'] = $postData['fieldtype'];

                if(isset($postData['fieldcheck']) && $postData['fieldcheck'] != '')
                    $filters['isNull'] = $postData['fieldcheck'];

                if(isset($postData['searchText']) && $postData['searchText'] != '')
                    $filters['searchText'] = $postData['searchText'];

                if(isset($postData['country_code']) && $postData['country_code'] != '')
                    $filters['country_code'] = $postData['country_code'];
                    
                $businessList = $this->objBusiness->businessFilter($filters, $pagination=true, $search=true);
            }
            else
            {
                // $businessList = Business::has('user')->get();
                $businessList = $this->objBusiness->getAllForAdmin($postData, true);
            }
        }
        $categoryId = '';
        $whatsappMessage = Helpers::isOnSettings('whatsapp_message');

        $this->log->info('Admin business listing page', array('admin_user_id' => Auth::id()));
        return view('Admin.ListAllBusiness', compact('businessList', 'postData', 'categoryId','whatsappMessage'));   
    }

    public function getAllBusinessesByCategory($id)
    {
        $categoryId = Crypt::decrypt($id);
        $businessList = Helpers::categoryHasBusinesses($categoryId);
        $whatsappMessage = Helpers::isOnSettings('whatsapp_message');
        $this->log->info('Admin business listing page', array('admin_user_id' => Auth::id()));
        return view('Admin.ListAllBusiness', compact('businessList', 'categoryId','whatsappMessage')); 
    }

    public function getPremiumBusinesses(Request $request)
    {
        if ($request->ajax()) {
            $entities = Business::select('business.*')->has('user')->with('user:id,name,phone','businessMembershipPlans')->where('business.membership_type','<>',0);
            $user = auth()->user();
            if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
                $entities->whereRaw(Auth::user()->sql_query);
            }
            return DataTables::of($entities)
                ->addColumn('user_name', function($entity) { 
                    $name = '';                   
                    if(!empty($entity->user)) {
                        $name = "<a href='".url('/admin/edituser',\Crypt::encrypt($entity->user->id))."' target='_blank'>".$entity->user->name."</a>";
                    }
                    return $name;
                })
                ->editColumn('country_code', function($entity) { 
                    $mobile = '';                   
                    if(!empty($entity->country_code)) {
                        $mobile = '('.$entity->country_code.')'.$entity->mobile ; 
                    } else {
                        $mobile = $entity->mobile;
                    }
                    return $mobile;
                })
                ->editColumn('membership_type', function($entity) {
                    $type = '';
                    if($entity->membership_type == 1) {
                        $type = 'Premium';
                    } else if($entity->membership_type == 2) {
                        $type = 'Lifetime Premium';
                    }
                    return $type;
                })
                ->addColumn('plan', function($entity) {
                    $plans = $entity->businessMembershipPlans;
                    $date = '';
                    if (!$plans->isEmpty()) {
                        $mostRecent= 0;
                        foreach($plans as $plan)
                        {
                            $curDate = strtotime($plan->end_date);
                            if ($curDate > $mostRecent) {
                                $mostRecent = $curDate;
                            }
                        }
                        if($mostRecent != 0) {
                            $date = Date('Y-m-d',$mostRecent);
                        }
                    }
                    return $date;
                })
                ->addColumn('action', function($entity) use($user) {
                    $class = $editBtn = $deleteBtn = $serviceBtn = $productBtn = $ownerBtn = $memberBtn = $websiteBtn = '';
                    $encriptId = Crypt::encrypt($entity->id);
                    $attributes = [
                        "onclick" => "return confirm('Are you sure you want to delete ?')"
                    ];
                    if($user->can(config('perm.editEntity'))) {
                        $editUrl = route('entity.edit',$encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteEntity'))) {
                        $url = route('entity.destroy',$encriptId);
                        $deleteBtn = getDeleteBtn($url,$class, $attributes);
                    }
                    if($user->can(config('perm.manageService'))) {
                        $serviceBtn = '<a href="'.url('/admin/user/business/service',$encriptId).'" class="mr5">
                        <span  class="badge bg-light-blue" data-toggle="tooltip" data-original-title="Manage Service" style="margin-bottom: 3px;">S</span>
                        </a>';
                    }
                    if($user->can(config('perm.manageProduct'))) {
                        $productBtn = '<a href="'.url('/admin/user/business/product',$encriptId).'" class="mr5">
                            <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Product" style="margin-bottom: 3px;">P</span>
                        </a>';
                    }
                    if($user->can(config('perm.manageOwner'))) {
                        $ownerBtn = '<a href="'.url('/admin/user/business/owner',$encriptId).'" class="mr5">
                            <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Owner" style="margin-bottom: 3px;">O</span>
                        </a>';
                    }
                    if($user->can(config('perm.manageMembership'))) {
                        $memberBtn = '<a href="'.url('/admin/user/business/membership',$encriptId).'" class="mr5">
                            <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Manage Membership" style="margin-bottom: 3px;">M</span>
                        </a>';
                    }
                    if($user->can(config('perm.manageWebsite'))) {
                        $websiteBtn = '<a href="'.url('/admin/allpublicwebsite/add',$encriptId).'" class="mr5">
                            <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="Public Website" style="margin-bottom: 3px;">W</span>
                        </a>';
                    }

                    return $editBtn.$deleteBtn.$serviceBtn.$productBtn.$ownerBtn.$memberBtn.$websiteBtn;
                })
                ->rawColumns(['user_name','action'])
                ->make(true);
        }
        $this->log->info('Admin business listing page', array('admin_user_id' => Auth::id()));
        return view('Admin.Entity.premium');   
    }

    public function bulkStatusBusinessApproved()
    {
        $tmpBusinessIds = Input::get('businessIds');
        $businessIds = explode(',', $tmpBusinessIds);

        foreach($businessIds as $businessId) {
            $postData['id'] = $businessId;
            $postData['approved'] = 1;
            try {

                DB::beginTransaction();
                $this->objBusiness->insertUpdate($postData);

                //Send push notification to Business User
                $businessDetail = Business::find($businessId);     
                if($businessDetail->user) {       
                    $notificationData = [];
                    $notificationData['title'] = 'Business Approved';
                    $notificationData['message'] = 'Dear '.$businessDetail->user->name.',  Good News! Your Business Profile just got approved. Close and Open App to see changes.';
                    $notificationData['type'] = '8';
                    $notificationData['business_id'] = $businessDetail->id;
                    $notificationData['business_name'] = $businessDetail->name;
                    Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

                    // notification list 
                    $notificationListArray = [];
                    $notificationListArray['user_id'] = $businessDetail->user_id;
                    $notificationListArray['business_id'] = $businessDetail->id;
                    $notificationListArray['title'] = 'Business Approved';
                    $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',  Good News! Your Business Profile just got approved. Close and Open App to see changes.';
                    $notificationListArray['type'] = '8';
                    $notificationListArray['business_name'] = $businessDetail->name;
                    $notificationListArray['user_name'] = $businessDetail->user->name;

                    NotificationList::create($notificationListArray);

                    if($businessDetail->user_id != $businessDetail->created_by) {
                        $notificationData['message'] = 'Dear '.$businessDetail->businessCreatedBy->name.',  Good News! Your Customer\'s Business Profile just got approved';
                        Helpers::sendPushNotification($businessDetail->created_by, $notificationData); 

                        // notification list 
                        $notificationListArray = [];
                        $notificationListArray['user_id'] = $businessDetail->created_by;
                        $notificationListArray['business_id'] = $businessDetail->id;
                        $notificationListArray['title'] = 'Business Approved';
                        $notificationListArray['message'] =  'Dear '.$businessDetail->businessCreatedBy->name.',  Good News! Your Customer\'s Business Profile just got approved';
                        $notificationListArray['type'] = '8';
                        $notificationListArray['business_name'] = $businessDetail->name;
                        $notificationListArray['user_name'] = $businessDetail->businessCreatedBy->name;

                        NotificationList::create($notificationListArray);   
                    }
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
            }
        }
        Cache::forget('membersForApproval');
        return 1;
    }

    public function bulkStatusBusinessRejected()
    {
        $tmpBusinessIds = Input::get('businessIds');
        $businessIds = explode(',', $tmpBusinessIds);

        foreach($businessIds as $businessId) {
            
            $data = $this->objBusiness->find($businessId);
            try {
                if($data) {
                    DB::beginTransaction();
                    // $response = $data->delete();
                    /**
                     * @date: 22nd Aug, 2018
                     * We don't want to delete the rejected business, instead of that we like to show that business as rejected flag in admin
                     * It was requested by Mr. Mahipalsihn
                     */
                    $postData['id'] = $businessId;
                    /**
                     * 0 = Pending
                     * 1 = Approved
                     * 2 = Rejected
                     */
                    $postData['approved'] = 2; 
                    $this->objBusiness->insertUpdate($postData);
                    DB::commit();
                }
            } catch (Exception $e) {
                DB::rollback();
            }
        }

        Cache::forget('membersForApproval');
        return 1;
    }

    public function createEntity(Request $request)
    {
        try 
        {
            $data = [];
            $userId = 0;
            $businessImages = [];
            $businessDoc = [];
            $parentCategories = $this->objCategory->getAll(array('parent' => '0'));
            
            $agentUsers = AgentUser::with('user')->get();
            $stores = OnlineStore::whereStatus(1)->orderBy('name')->get();
            if(empty($request->id)) {
                return view('Admin.Entity.create', compact('userId','parentCategories','data','businessImages','agentUsers','businessDoc','stores'));
            }          
            return view('errors.404');
        } catch (\Exception $e){
            $this->log->error('Admin something went wrong while business edit page', array('admin_user_id' =>  Auth::id(), 'error' => $e->getMessage()));
            return view('errors.404');
        }
    }
    
    /**
     * show detail of entity    
     *
     * @param  mixed $id
     * @return void
     */
    public function show($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $entity = $this->objBusiness->findOrFail($id);
            if(!empty($entity->parent_category)) {
                $categoryIds = explode(",",$entity->parent_category);
                $categoryId = $categoryIds[0];
            } else {
                $categoryId = 0;
            }
            $assetType = AssetTypeField::where('asset_type_id',$entity->asset_type_id)->where('category_id',$categoryId)->first();
            $enableComponent = null;
            if($assetType && !empty($assetType->selected_fields)) {
                $enableComponent = json_decode($assetType->selected_fields);
            }
            $stores = OnlineStore::whereStatus(1)->orderBy('name')->get();
            $knowMoresInOtherLang = EntityKnowMore::where('entity_id',$id)->where('language','<>','english')->get();
            $customDetailsInOtherLang = EntityCustomField::where('entity_id',$id)->where('language','<>','english')->get();
            return view('Admin.Entity.show', compact('entity','enableComponent','stores','knowMoresInOtherLang','customDetailsInOtherLang'));
        } catch (\Throwable $th) {
            $this->log->error('Admin something went wrong while entity show page', array('admin_user_id' =>  Auth::id(), 'business_id' => $id));
            $this->log->error($th);
            return view('errors.404');
        }
    }
    
    /**
     * get description suggestions of entity and know more
     *
     * @return void
     */
    public function getDescSuggestions(Request $request)
    {
        $suggestions = EntityDescriptionSuggestion::with('user');
        if($request->type == 'entity') {
            $suggestions->where('entity_id',$request->entity_id);
        } else {
            $suggestions->where('entity_know_more_id',$request->entity_id);
        }
        return DataTables::of($suggestions)
            ->make(true);
    }
    
    /**
     * for get all reports of entity
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityReports(Request $request)
    {
        $reports = EntityReport::with('reportBy')->where('entity_id',$request->entity_id);
        
        return DataTables::of($reports)
            ->editColumn('comment', function($report) {                
                return Str::limit($report->comment,150);
            })
            ->addColumn('action', function($report) {
                $url = route('entity.view.report',$report->id);
                $btn = getViewBtn($url);
                return $btn;
            })
            ->make(true);
    }
    
    /**
     * for get show report of entity
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityReport(Request $request)
    {
        $report = EntityReport::with('reportBy','reasons')->findOrFail($request->id);
        return view('Admin.Entity.report-view', compact('report'));
        
    }
}
