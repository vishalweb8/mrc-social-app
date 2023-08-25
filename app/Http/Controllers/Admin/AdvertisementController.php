<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Helpers;
use Config;
use Image;
use File;
use DB;
use Input;
use Redirect;
use Crypt;
use Response;
use Carbon\Carbon;
use Mail;
use Session;
use Validator;
use JWTAuth;
use JWTAuthException;
use Cache;
use Storage;
use \stdClass;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Cviebrock\EloquentSluggable\Services\SlugService;
use App\Http\Requests\AdvertisementRequest;
use App\Http\Requests\AdvertisementContactRequest;
use App\Helpers\ImageUpload;
use App\User;
use App\AgentUser;
use App\Category;
use App\Advertisement;
use App\AdvertisementCategory;
use App\AdvertisementImage;
use App\AdvertisementVideo;

class AdvertisementController extends Controller
{
    public function __construct()
    {        
        $this->middleware('auth');
        $this->objAdvertisement = new Advertisement();
        $this->objAdvertisementCategory = new AdvertisementCategory();
        $this->objAdvertisementImage = new AdvertisementImage();
        $this->objAdvertisementVideo = new AdvertisementVideo();
        $this->objUser = new User();
        $this->objCategory = new Category();

        $this->ADVERTISEMENT_ORIGINAL_IMAGE_PATH = Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH');
        $this->ADVERTISEMENT_THUMBNAIL_IMAGE_PATH = Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH');
        $this->ADVERTISEMENT_STORAGE_TYPE = Config::get('constant.ADVERTISEMENT_STORAGE_TYPE');
        $this->ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH');
        $this->ADVERTISEMENT_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_HEIGHT');
        
        $this->categoryLogoOriginalImagePath = Config::get('constant.CATEGORY_LOGO_ORIGINAL_IMAGE_PATH');
        $this->categoryLogoThumbImagePath = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH');
        $this->categoryLogoThumbImageHeight = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->categoryLogoThumbImageWidth = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH');

        $this->categoryBannerImagePath = Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH');

        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('advertisement-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));

        $this->catgoryTempImage = Config::get('constant.CATEGORY_TEMP_PATH');
    }

    public function index(Request $request)
    {    
        
        $postData = Input::all();
        
        if (isset($postData['fieldcheck']) && $postData['fieldcheck'] != '') {
            $filters['isNull'] = $postData['fieldcheck'];
        }

        $advertisementList = $this->objAdvertisement->geAllWithFilterForAdmin($postData, true);

        $this->log->info('Admin Advertisement listing page', array('admin_user_id' => Auth::id()));
        return view('Admin.Advertisement.index', compact('advertisementList','postData'));      
    }
    
    public function edit($id)
    {
        try 
        {           
            $id = Crypt::decrypt($id);
            $data = $this->objAdvertisement->backendFindById($id);
            
            $advertisementImages = (isset($data->advertisementImages) && !empty($data->advertisementImages)) ? $data->advertisementImages->toArray() : [];
            $advertisementVideos = (isset($data->advertisementVideos) && !empty($data->advertisementVideos)) ? $data->advertisementVideos->toArray() : [];
            
            $userId = 0;
            if($data) 
            {                
                $userId = $data->user_id;
                $userDetail = User::find($userId);
                $parentCategories = $this->objCategory->getAll(array('parent' => '0'));
                $agentUsers = AgentUser::get();

                $interestDetails = $data->userInterestInAdvertisement()
                                            ->join('chats', function($join) { 
                                                $join->on('chats.advertisement_id', '=', 'user_interest_in_advertisement.advertisement_id'); 
                                                $join->on('chats.customer_id', '=', 'user_interest_in_advertisement.user_id');
                                            })
                                            ->orderBy('user_interest_in_advertisement.id', 'DESC')
                                            ->limit(Config::get('constant.BUSINESS_DETAILS_RATINGS_LIMIT'))
                                            ->select("user_interest_in_advertisement.*", 'chats.id AS thread_id')
                                            ->get();
                $dataInterest = [];
                foreach ($interestDetails as $interestKey => $interestValue)
                {           
                    $dataInterest[$interestKey]['id'] = $interestValue->id;
                    $dataInterest[$interestKey]['timestamp'] = (!empty($interestValue->updated_at)) ? ($interestValue->updated_at) : '';
                    $dataInterest[$interestKey]['name'] = (isset($interestValue->user) && !empty($interestValue->user->name)) ? $interestValue->user->name : '';
                    $imgThumbUrl = '';
                    $imgThumbUrl = ((isset($interestValue->user) && !empty($interestValue->user->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                    $dataInterest[$interestKey]['image_url'] = $imgThumbUrl;

                    $dataInterest[$interestKey]['country_code'] = $interestValue->user->country_code;
                    $dataInterest[$interestKey]['phone_number'] = $interestValue->user->phone;

                    $dataInterest[$interestKey]['thread_id'] = $interestValue->thread_id;                   
                    $dataInterest[$interestKey]['user_business_id'] = (isset($interestValue->user->singlebusiness) && $interestValue->user->singlebusiness->id != '')? (string)$interestValue->user->singlebusiness->id : '';
                }

                if(Auth::user()->agent_approved == 0)
                {
                    $this->log->info('Admin advertisement edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id));
                    return view('Admin.Advertisement.edit', compact('userId','parentCategories','data','advertisementImages','userDetail','agentUsers', 'dataInterest'));
                }
                elseif (Auth::user()->agent_approved == 1) {
                    if($data->agent_user == Auth::id())
                    {
                        $this->log->info('Admin advertisement edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id));
                        return view('Admin.Advertisement.edit', compact('userId','parentCategories','data','advertisementImages','userDetail','businessWorkingHoursData','agentUsers', 'dataInterest'));
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
                return Redirect::to("admin/advertisement")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            $this->log->error('Admin something went wrong while business edit page', array('admin_user_id' =>  Auth::id(), 'user_id' => $userId, 'business_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
    }

    /**
     * Approve the multiple advertisement
     */
    public function updateAdvertisingToApproved()
    {        
        $outputArray = [];
        $statusCode = 200;
        $tmpAdvertisementIds = Input::get('advertisementIds');
        $advertisementIds = explode(',', $tmpAdvertisementIds);

        foreach($advertisementIds as $advertisementId) {
            
            $data = $this->objAdvertisement->find($advertisementId);
            try {
                if($data) {
                    // DB::beginTransaction();
                    $postData['id'] = $advertisementId;
                    /**
                     * 0 = Pending
                     * 1 = Approved
                     * 2 = Rejected
                     */
                    $postData['approved'] = 1; 
                    $postData['approved_by'] = Auth::id(); 
                    $this->objAdvertisement->insertUpdate($postData);
                    // DB::commit();

                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.ads_added_success');
                }
            } catch (Exception $e) {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $e->getMessage();
                $statusCode = $e->getStatusCode();
            }
        }
        Cache::forget('advertisementForApproval');
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Reject the multiple advertisement
     */
    public function updateAdvertisingToRejected()
    {
        $outputArray = [];
        $statusCode = 200;
        $tmpAdvertisementIds = Input::get('advertisementIds');
        $advertisementIds = explode(',', $tmpAdvertisementIds);

        foreach($advertisementIds as $advertisementId) {
            
            $data = $this->objAdvertisement->find($advertisementId);
            try {
                if($data) {
                    DB::beginTransaction();
                    $postData['id'] = $advertisementId;
                    /**
                     * 0 = Pending
                     * 1 = Approved
                     * 2 = Rejected
                     */
                    $postData['approved'] = 2; 
                    $postData['approved_by'] = Auth::id(); 
                    $this->objAdvertisement->insertUpdate($postData);
                    DB::commit();

                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.ads_added_success');
                }
            } catch (Exception $e) {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $e->getMessage();
                $statusCode = $e->getStatusCode();
            }
        }       
       
        Cache::forget('advertisementForApproval');
        return 1;
        return response()->json($outputArray, $statusCode);
    }
    
    public function saveAdvertisementInfo(AdvertisementRequest $request)
    {
        $postData = Input::all();
        
        $this->validate($request,
            ['image_name.*' => 'image|mimes:jpeg,png,jpg|max:5120'],
            ['image_name.*.max' => 'File size must be less than 5 MB']
        );

        $promoted =  (!isset($postData['promoted'])) ? 0 : 1;
        $postData['promoted'] = $promoted;
        $sponsored =  (!isset($postData['sponsored'])) ? 0 : 1;
        $postData['sponsored'] = $sponsored;

        $advertisementId =  (!isset($postData['id'])) ? 0 : $postData['id'];

        if($postData['approved'] == 1 || $postData['approved'] == 2) {
            $postData['approved_by'] = Auth::id();
        }

        $response = $this->objAdvertisement->insertUpdate($postData);

        Cache::forget('advertisementForApproval');
        Cache::forget('advertisementData');
        
        if ($response) 
        {
            $this->log->info('Admin advertisement info save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'advertisementId' => $advertisementId));
            return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('success', "Advertisement saved successfully");
        } else {
            $this->log->error('Admin something went wrong while save business info', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'advertisementId' => $advertisementId));
            return Redirect::to("admin/advertisement/".Crypt::encrypt($postData['user_id']))->with('error', "Opps. Something went wrong with Advertisement");
        }
    }
   
    public function saveContactInfo(AdvertisementContactRequest $request)
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

        $advertisementId = ($postData['id'] == 0) ? $response->id : $postData['id'];
        Log::info("advertisement Data Get".$advertisementId);
        $response = $this->objAdvertisement->insertUpdate($postData);
        
        Cache::forget('advertisementForApproval');
        Cache::forget('advertisementData');

        if ($response) 
        {
            $this->log->info('Admin advertisement info save successfully', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'advertisementId' => $advertisementId));
            return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('success', "Advertisement Contact info saved successfully");
        } else {
            $this->log->error('Admin something went wrong while save business info', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'advertisementId' => $advertisementId));
            return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('error', "Opps. Something went wrong with Advertisement");
        }
    }

    public function saveVideoLinks()
    {
        $postData = Input::all();            
        if(isset($postData['video_link']) && !empty($postData['video_link']))
        {
            foreach(array_filter($postData['video_link']) as $key=>$video)
            {
                if($video != "") {
                    $videoArray = [];
                    $videoArray['advertisement_id'] = $postData['id'];
                    $videoArray['video_link'] = $video;
                    // $this->objAdvertisementVideo->insertUpdate($videoArray);
                    if ($video != "") {
                        $this->objAdvertisementVideo->AddVideo($postData['id'], $video, 0);
                    }
                }
            }
        }

        if(isset($postData['deleted_video_link']) && !empty($postData['deleted_video_link']))
        {
            foreach($postData['deleted_video_link'] as $video)
            {
                $data = $this->objAdvertisementVideo->find($video);
                $data->delete();
            }
        }

        if(isset($postData['update_video_link_title']) && !empty($postData['update_video_link_title']))
        {
            foreach($postData['update_video_link_title'] as $key=>$video)
            {
                $videoArray = [];
                $videoArray['advertisement_id'] = $postData['id'];
                $videoArray['video_link'] = $video;
                $videoArray['id'] = $postData['update_video_link_id'][$key];
                // $this->objAdvertisementVideo->insertUpdate($videoArray);
                if ($video != "") {
                    $this->objAdvertisementVideo->AddVideo($postData['id'], $video, $postData['update_video_link_id'][$key]);
                }
            }
        }

        Cache::forget('advertisementForApproval');
        Cache::forget('advertisementData');

        return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('success', "Advertisement's video links saved successfully");
    }
    
    public function saveCategory()
    {
        $postData = Input::all();
        $response  = true;
        if(isset($postData['category_id']) && !empty($postData['category_id']))
        {
            $postData['category_id'] = array_filter(array_unique($postData['category_id']));

            try {                
                DB::beginTransaction();
                $this->objAdvertisementCategory->removeCategoriesForAds([["advertisement_id", "=", $postData["id"]]]);

                foreach($postData['category_id'] as $categoryId) {
                    $postData['parent_category'] = Helpers::getParentCategoryIds($categoryId);
                    
                    if($postData['parent_category'] != "") {
                        $postData['parent_category'] = explode(',', $postData['parent_category']);
                        foreach($postData['parent_category'] as $parent_categoryId) {
                            $filters =  [
                                ["category_id", "=", $parent_categoryId],
                                ["advertisement_id", "=", $postData["id"]],
                                ["category_type", "=", 0]
                            ];

                            $savedData = $this->objAdvertisementCategory->filterSingleObect($filters);
                            $savedData["category_id"] = $parent_categoryId;
                            $savedData["advertisement_id"] = $postData["id"];
                            $savedData["category_type"] = 0;                        
                            $this->objAdvertisementCategory->insertUpdate($savedData);
                        }
                    }

                    $filters =  [
                        ["category_id", "=", $categoryId],
                        ["advertisement_id", "=", $postData["id"]],
                        ["category_type", "=", 1]
                    ];

                    $savedData = $this->objAdvertisementCategory->filterSingleObect($filters);
                    $savedData["category_id"] = $categoryId;
                    $savedData["advertisement_id"] = $postData["id"];
                    $savedData["category_type"] = 1;                        
                    $this->objAdvertisementCategory->insertUpdate($savedData);

                    $response  = true;
                }
                
                DB::commit();
            } catch (Exception $e) {
                $response  = false;
                DB::rollback();
            }
        } else {
            try {                
                DB::beginTransaction();
                $this->objAdvertisementCategory->removeCategoriesForAds([["advertisement_id", "=", $postData["id"]]]);
                $response  = true;                
                DB::commit();
            } catch (Exception $e) {
                $response  = false;
                DB::rollback();
            }
        }
        
        Cache::forget('advertisementForApproval');
        Cache::forget('advertisementData');

        if ($response) {
            $this->log->info('Admin business categry hierarchy save', array('admin_user_id' => Auth::id(), 'user_id' => $postData['user_id']));
            return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('success', "Advertisement's category saved successfully");
        } else {
            $this->log->error('Admin something went wrong while save business categry hierarchy', array('admin_user_id' =>  Auth::id(), 'user_id' => $postData['user_id'], 'business_id' => $postData['id']));
            return Redirect::to("admin/advertisement/edit/".Crypt::encrypt($postData['id']))->with('error', "Opps. Something went wrong while saving Advertisement's category");
        }
    }

    /**
     * Delete the Advertisment for the user. If user is the owner of the Advertisement than it is going to delete otherwise not.
     */
    protected function removeAdvertisement($id)
    {
        $filters = [];
        try {
            $id = Crypt::decrypt($id);
            $filters["advertisement_id"] = $id;
            
            $isValidAds = Advertisement::find($id);
            if ($isValidAds) {
                $filters["user_id"] = $isValidAds->user_id;

                DB::beginTransaction();
                $adsSaveData = $this->objAdvertisement->deleteAdvertisement($filters);
                
                if ($adsSaveData) {
                    DB::commit();
                    return Redirect::to("admin/advertisement/")->with('success', trans('labels.advertisement_deleted_successfully'));
                } else {
                    DB::rollback();
                    return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_deleted_error_msg'));
                }         
            } else {
                return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_not_found'));
            }         
        } catch (Exception $e) {
            DB::rollback();
            return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_deleted_error_msg'));
        }
    }
    protected function restoreAdvertisement($id)
    {
        $filters = [];
        try {
            $id = Crypt::decrypt($id);
            $filters["advertisement_id"] = $id;
            
            $isValidAds = Advertisement::withTrashed()->find($id);
            if ($isValidAds) {
                $filters["user_id"] = $isValidAds->user_id;

                DB::beginTransaction();
                $adsSaveData = $this->objAdvertisement->restoreAdvertisement($filters);
                
                if ($adsSaveData) {
                    DB::commit();
                    return Redirect::to("admin/advertisement/")->with('success', trans('labels.advertisement_restored_successfully'));
                } else {
                    DB::rollback();
                    return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_restored_error_msg'));
                }         
            } else {
                return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_not_found'));
            }         
        } catch (Exception $e) {
            DB::rollback();
            return Redirect::to("admin/advertisement/")->with('error', trans('labels.advertisement_restored_error_msg'));
        }
    }
}
