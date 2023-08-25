<?php

namespace App\Http\Controllers\Api;

use App\AssetType;
use App\Branding;
use App\Business;
use App\BusinessActivities;
use App\BusinessImage;
use App\BusinessRatings;
use App\BusinessWorkingHours;
use App\Category;
use App\Http\Controllers\Controller;
use App\Membership;
use App\MembershipRequest;
use App\Metatag;
use App\Notification;
use App\NotificationList;
use App\OwnerChildren;
use App\OwnerSocialActivity;
use App\Owners;
use App\PayGIntegration;
use App\PaymentTransaction;
use App\Product;
use App\ProductImage;
use App\SearchTerm;
use App\Service;
use App\SubscriptionPlan;
use App\TempSearchTerm;
use App\User;
use App\UserAgentOTP;
use App\UserMetaData;
use App\UserRole;
use Auth;
use Cache;
use Carbon\Carbon;
use Config;
use Crypt;
use Cviebrock\EloquentSluggable\Services\SlugService;
use DB;
use File;
use Helpers;
use Illuminate\Http\Request;
use Image;
use Input;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\JWTAuthException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Log;
use Mail;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Redirect;
use Response;
use App\BusinessDoc;
use App\EntityCustomField;
use App\EntityDescriptionLanguage;
use App\EntityDescriptionSuggestion;
use App\EntityKnowMore;
use App\EntityNearbyFilter;
use App\EntityReport;
use App\EntityVideo;
use App\OnlineStore;
use App\PublicPost;
use App\Traits\BusinessTrait;
use App\UserVisitActivity;
use Session;
use Illuminate\Support\Facades\Storage;
use Validator;
use \stdClass;
use Illuminate\Support\Str;


class BusinessController extends Controller
{
    use BusinessTrait;

    public function __construct()
    {
        $this->objBusiness = new Business();
        $this->ObjMembership = new Membership();
        $this->objPaymentTransaction = new PaymentTransaction();
        $this->objMembershipRequest = new MembershipRequest();
        $this->objBusinessImage = new BusinessImage();
        $this->objBusinessDoc = new BusinessDoc();
        $this->objBusinessActivities = new BusinessActivities();
        $this->objBusinessWorkingHours = new BusinessWorkingHours();
        $this->objUser = new User();
        $this->objUserAgentOTP = new UserAgentOTP();
        $this->objUserMetaData = new UserMetaData();
        $this->objUserRole = new UserRole();
        $this->objBusinessRatings = new BusinessRatings();
        $this->objCategory = new Category();
        $this->objProduct = new Product();
        $this->objProductImage = new ProductImage();
        $this->objService = new Service();
        $this->objOwner = new Owners();
        $this->objOwnerChildren = new OwnerChildren();
        $this->objOwnerSocialActivity = new OwnerSocialActivity();
        $this->objMetatag = new Metatag();

        $this->PRODUCT_ORIGINAL_IMAGE_PATH = Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH');
        $this->PRODUCT_THUMBNAIL_IMAGE_PATH = Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH');

        $this->BUSINESS_ORIGINAL_IMAGE_PATH = Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH');
        $this->BUSINESS_THUMBNAIL_IMAGE_PATH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH');

        $this->businessImageThumbImageHeight = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_HEIGHT');
        $this->businessImageThumbImageWidth = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_WIDTH');

        $this->SERVICE_ORIGINAL_IMAGE_PATH = Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH');
        $this->SERVICE_THUMBNAIL_IMAGE_PATH = Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH');

        $this->USER_ORIGINAL_IMAGE_PATH = Config::get('constant.USER_ORIGINAL_IMAGE_PATH');
        $this->USER_THUMBNAIL_IMAGE_PATH = Config::get('constant.USER_THUMBNAIL_IMAGE_PATH');
        $this->USER_PROFILE_PIC_WIDTH = Config::get('constant.USER_PROFILE_PIC_WIDTH');
        $this->USER_PROFILE_PIC_HEIGHT = Config::get('constant.USER_PROFILE_PIC_HEIGHT');

        $this->OWNER_ORIGINAL_IMAGE_PATH = Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_PATH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_WIDTH');
        $this->OWNER_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.OWNER_THUMBNAIL_IMAGE_HEIGHT');

        $this->categoryLogoOriginalImagePath = Config::get('constant.CATEGORY_LOGO_ORIGINAL_IMAGE_PATH');
        $this->categoryLogoThumbImagePath = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH');
        $this->categoryLogoThumbImageHeight = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->categoryLogoThumbImageWidth = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH');

        $this->categoryBannerImagePath = Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH');

        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('business-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));

        $this->catgoryTempImage = Config::get('constant.CATEGORY_TEMP_PATH');
    }


    /*
        State List
    */

    public function stateList(Request $request)
    {
        $outputArray = [];
        $requestData = $request->all();
        $countryName =  trim($request->countryName);
        if(!empty($request->countryName)) {
            $stateList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('state', 'ASC')->groupby("state")->where('country', $countryName)->get();
          
        }  else {
            $stateList = DB::table('locations')->groupby("state")->get();
        }

        $outputArray['status'] = 1;
        $outputArray['message'] = trans('apimessages.get_state');
        $statusCode = 200;
        $outputArray['data'] = $stateList;
        return response()->json($outputArray, $statusCode);
    }

    
    /*
        State List
    */

    public function districtList(Request $request)
    {
        $outputArray = [];
        $requestData = $request->all();
        $stateName =  trim($request->stateName);
        if(!empty($request->stateName)) {
            $districtList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('district', 'ASC')->groupby("district")->where('state', $stateName)->get();
          
        }  else {
            $districtList = DB::table('locations')->groupby("district")->get();
        }

        $outputArray['status'] = 1;
        $outputArray['message'] = trans('apimessages.get_district');
        $statusCode = 200;
        $outputArray['data'] = $districtList;
        return response()->json($outputArray, $statusCode);
    }


    /*
        City List
    */

    public function cityList(Request $request)
    {
        $outputArray = [];
        $stateName =  trim($request->stateName);
        $districtName =  trim($request->district);
        if(!empty($request->searchText)) {
            $cityList = DB::table('locations')->select('id','city')->where('city','LIKE',$request->searchText.'%')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->limit(5)->get();
        } else if(!empty($request->stateNames)) {
            $stateIds =    DB::table('locations')->whereIn('state', $request->stateNames)->pluck('state')->toArray();
            if(!empty($stateIds)) {
                $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->whereIn('state', $stateIds)->get();
            } else {
                $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->get();
            }
        } else if ($stateName) {
            $countrydetails =    DB::table('locations')->where('state', 'like', $stateName)->value('state');

            if ($countrydetails) {

                $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('state', 'ASC')->where('state', $countrydetails)->get();
            } else {

                $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->groupby("city")->get();
            }
        } else if ($districtName) {
            $countrydetails =    DB::table('locations')->where('district', 'like', $districtName)->value('district');

            if ($countrydetails) {
            } else {
                $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->groupby("city")->get();
            }
        } else {
            $cityList = DB::table('locations')->orderByRaw('-position DESC')->orderBy('city', 'ASC')->groupby("city")->get();
        }
        $outputArray['status'] = 1;
        $outputArray['message'] = trans('apimessages.get_city');
        $statusCode = 200;
        $outputArray['data'] = $cityList;
        return response()->json($outputArray, $statusCode);
    }



    /**
     * Get Agent Businesses
     */
    public function getAgentBusinesses(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        $outputArray = [];
        $requestData = array_map('trim', $request->all());
        try
        {
            $validator = Validator::make($requestData, [
                'agent_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $agent_id = $requestData['agent_id'];
            $filters = [];
            $filters['created_by'] = $agent_id;
            if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if (isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif (isset($request->page) && !empty($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                if (isset($request->sortBy) && !empty($request->sortBy))
                {
                    if ($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                }
            }
            else
            {
                if (isset($request->page) && !empty($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                if (isset($request->sortBy) && !empty($request->sortBy))
                {
                    if ($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                }
            }
            if (isset($request->sortBy) && !empty($request->sortBy) && $request->sortBy == 'ratings')
            {
                $filters['sortBy'] = 'ratings';
                $agentBusinessData = $this->objBusiness->getBusinessesByRating($filters);
            }
            else
            {
                if (!isset($request->sortBy) && empty($request->sortBy))
                {
                    $filters['sortBy'] = 'promoted';
                }
                //$agentBusinessData = $this->objBusiness->getAll($filters);
                $agentBusinessData = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            }
            if ($agentBusinessData)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.recently_added_business_fetched_successfully');
                $statusCode = 200;
                $outputArray['businessesTotalCount'] = (isset($agentBusinessData) && count($agentBusinessData) > 0) ? count($agentBusinessData) : 0;

                $outputArray['data'] = array();
                $i = 0;
                foreach ($agentBusinessData as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['user_name'] = (isset($value->user) && !empty($value->user) && !empty($value->user->name)) ? $value->user->name : '';

                    $outputArray['data'][$i]['owners'] = '';

                    if($value->owners)
                    {
                        $owners = [];
                        foreach($value->owners as $owner)
                        {
                            $owners[] = $owner->full_name;
                        }
                        if(!empty($owners))
                        {
                            $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                        }
                    }

                    $parentCatArray = $this->getBusinessParentCategory($value->parent_category);
                    $outputArray['data'][$i]['parent_categories'] = $parentCatArray;
                    $outputArray['data'][$i]['categories_name_list'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';

                    $outputArray['data'][$i]['approved'] = $value->approved;
                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : '';
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : '';
                    $outputArray['data'][$i]['address'] = (!empty($value->address)) ? $value->address : '';
                    $outputArray['data'][$i]['street_address'] = $value->street_address;
                    $outputArray['data'][$i]['locality'] = $value->locality;
                    $outputArray['data'][$i]['country'] = $value->country;
                    $outputArray['data'][$i]['state'] = $value->state;
                    $outputArray['data'][$i]['city'] = $value->city;
                    $outputArray['data'][$i]['taluka'] = $value->taluka;
                    $outputArray['data'][$i]['district'] = $value->district;
                    $outputArray['data'][$i]['pincode'] = $value->pincode;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';
                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                    }

                     if (isset($value->businessImagesById) && !empty($value->businessImagesById->image_name) && Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $value->businessImagesById->image_name) {
                                          $s3url =   Config::get('constant.s3url');
                                          // return $s3url;
                                         $imgThumbUrl = $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $value->businessImagesById->image_name;
                                    }else{
                                        $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }


                    $outputArray['data'][$i]['business_image'] = $imgThumbUrl;

                     if (isset($value->business_logo) && !empty($value->business_logo) && Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo) {
                            $s3url =   Config::get('constant.s3url');
                            $businessLogoThumbImgPath = $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo;
                    }else{
                        $businessLogoThumbImgPath =  $imgThumbUrl;
                    }
                    $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;

                    $i++;
                }

                $this->log->info('API agent businesses get successfully', array('login_user_id' => $user->id));
            } else {
                $this->log->info('API agent businesses not found', array('login_user_id' => $user->id));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while get agent businesses', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete Business Image
     */
    public function deleteBusinessImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'id' => 'required'
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while delete business images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $id = $requestData['id'];
            $businessId = (isset($requestData['business_id']) && $requestData['business_id'] > 0) ? $requestData['business_id'] : 0;
            $businessImageData = BusinessImage::find($id);
            if($businessImageData)
            {
                $response = BusinessImage::where('id', $id)->where('business_id', $businessId)->delete();
                $businessImageName = $businessImageData->image_name;
                $pathOriginal =  $this->BUSINESS_ORIGINAL_IMAGE_PATH.$businessImageName;
                $pathThumb = $this->BUSINESS_THUMBNAIL_IMAGE_PATH.$businessImageName;

                //              Delete Image From Storage
                   $originalImageDelete = Helpers::deleteFileToStorage($businessImageName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, "s3");
              $thumbImageDelete = Helpers::deleteFileToStorage($businessImageName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, "s3");

                //              Deleting Local Files
                File::delete($pathOriginal, $pathThumb);
                if($response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.business_image_deleted_successfully');
                    $statusCode = 200;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.business_image_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while delete business image', array('login_user_id' =>  $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    /**
     * Save Business Images
     */
    public function saveBusinessImages(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'business_id' => 'required',
                'business_images.*' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400'
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while save business images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $businessId = $requestData['business_id'];

            if (Input::file('business_logo'))
            {
                $logo = Input::file('business_logo');

                if (!empty($logo))
                {
                    $fileName = '   ' . uniqid() . '.' . $logo->getClientOriginalExtension();
                    $pathThumb = (string) Image::make($logo->getRealPath())->resize($this->businessImageThumbImageWidth, $this->businessImageThumbImageHeight)->encode();

                    // if logo exist then delete

                    $businessDetail = $this->objBusiness->find($businessId);
                    $oldLogo = $businessDetail->business_logo;

                    if($oldLogo != '')
                    {
                        $originalImageDelete = Helpers::deleteFileToStorage($oldLogo, $this->BUSINESS_ORIGINAL_IMAGE_PATH, "s3");
                        $thumbImageDelete = Helpers::deleteFileToStorage($oldLogo, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, "s3");
                    }

                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $logo, "s3");
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                    $input['imagename'] = $fileName;
 
                    $businessDetail->business_logo = $fileName;
                    $businessDetail->save();
                    $response = $businessDetail;
                }
            }
            if (Input::file('business_images'))
            {
                $fileImagesArray = Input::file('business_images');
                if (isset($fileImagesArray) && count($fileImagesArray) > 0 && !empty($fileImagesArray) )
                {
                    foreach($fileImagesArray as $fileImageKey => $fileImageValue)
                    {
                        $fileImgName = 'business_' . Str::random(10). '.'. $fileImageValue->getClientOriginalExtension();
                        $pathThumb = (string) Image::make($fileImageValue->getRealPath())->resize($this->businessImageThumbImageWidth, $this->businessImageThumbImageHeight)->encode();
                        $businessImageInsert = [];
                        $businessImageInsert['business_id'] = $businessId;
                        $businessImageInsert['image_name'] = $fileImgName;
                        $response = BusinessImage::firstOrCreate($businessImageInsert);
//                      Uploading on AWS
                       $originalImage = Helpers::addFileToStorage($fileImgName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $fileImageValue, "s3");
                       $thumbImage = Helpers::addFileToStorage($fileImgName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                    }
                }
            }
            // if(isset($response) && $response)
            // {
                DB::commit();
                $this->log->info('API business images save successfully', array('login_user_id' => $user->id, 'business_id' => $businessId));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_images_added_successfully');
                $statusCode = 200;
            // }
            // else
            // {
            //     $this->log->error('API something went wrong while save business images', array('login_user_id' => $user->id));
            //     $outputArray['status'] = 0;
            //     $outputArray['message'] = trans('apimessages.default_error_msg');
            //     $statusCode = 200;
            // }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while save business images', array('login_user_id' =>  $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


     /**
     * Save Business Doc
     */
    public function saveBusinessDoc1(Request $request)
    {

             $user = JWTAuth::parseToken()->authenticate();
             $headerData = $request->header('Platform');
             $outputArray = [];
             $requestData = $request->all();
             
        try
        {
             $validator = Validator::make($requestData, [
                'business_id' => 'required',
                
                'doc_name'=> 'required',
                 'front_image.*' => 'required',
                  
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while delete business images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
                        $businessImageInsert = [];
            

                        $fileImageValue = $requestData['front_image'];
                         $fileImgName = 'business_doc_' . str_random(10). '.'. $fileImageValue->getClientOriginalExtension();
                        $pathThumb = (string) Image::make($fileImageValue->getRealPath())->resize($this->businessImageThumbImageWidth, $this->businessImageThumbImageHeight)->encode();

                        $originalImage = Helpers::addFileToStorage($fileImgName, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $fileImageValue, "s3");
                        $thumbImage = Helpers::addFileToStorage($fileImgName, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                         
                        $businessImageInsert['front_image'] = $fileImgName;
                        $businessImageInsert['business_id'] = $requestData['business_id'];
                        $businessImageInsert['doc_name'] =   $requestData['doc_name'];                    
                  //  echo "<pre>"; print_r( $businessImageInsert);die();
                      

            if(!empty($requestData['back_image'])){
                   //$back_image = Input::file('back_image');
                    
                   
                       $back_image = $requestData['back_image'];
                        
                        $fileImgName1 = 'business_back_doc_' . str_random(10). '.'. $back_image->getClientOriginalExtension();
                        $pathThumb = (string) Image::make($back_image->getRealPath())->resize($this->businessImageThumbImageWidth, $this->businessImageThumbImageHeight)->encode();

                        $originalImage = Helpers::addFileToStorage($fileImgName1, $this->BUSINESS_ORIGINAL_IMAGE_PATH, $back_image, "s3");
                        $thumbImage = Helpers::addFileToStorage($fileImgName1, $this->BUSINESS_THUMBNAIL_IMAGE_PATH, $pathThumb, "s3");
                        
                        $businessImageInsert['back_image'] = $fileImgName1;
            }else{
                    $businessImageInsert['back_image'] = '';

            }
            $arrayName = array(
                'doc_name' => $businessImageInsert['doc_name'],
                'business_id' => $businessImageInsert['business_id'],
                'back_image' =>  (($businessImageInsert['back_image'] != '') ) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImageInsert['back_image']):'',
                'front_image' =>  (($businessImageInsert['back_image'] != '') ) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImageInsert['front_image']):''
                 );

            $response = BusinessDoc::firstOrCreate($businessImageInsert);
              DB::commit();
                $this->log->info('API business images save successfully', array('login_user_id' => $user->id, 'business_id' => $requestData['business_id']));
                $outputArray['status'] = 1;
                $outputArray['data'] = $arrayName ;
                $outputArray['message'] = trans('apimessages.business_doc_added_successfully');
                $statusCode = 200;
        } 
        catch (Exception $e) {
            $this->log->error('API something went wrong while save business images', array('login_user_id' =>  $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }    
            return response()->json($outputArray, $statusCode);
    }



    /**
     * Save Business Doc
     */
    public function saveBusinessDoc(Request $request)
    {

             $user = JWTAuth::parseToken()->authenticate();
             $headerData = $request->header('Platform');
             $outputArray = [];
             $requestData = $request->all();
             
        try
        {
             $validator = Validator::make($requestData, [
                'business_id' => 'required',
                
                'doc_name'=> 'required',
                 'front_image.*' => 'required',
                  
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while delete business images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
                        $businessImageInsert = [];
            

                        $fileImageValue = $requestData['front_image'];
                        

                        $fileImageValue = explode(';base64,',  $fileImageValue);
                        $ext = explode('/',$fileImageValue[0]);            
                        $ext = $ext[1];
                        $fileImageValue =$fileImageValue[1];
                       // $fileImageValue = base64_decode( $fileImageValue);
                        
                        $fileImgName = 'business_doc_' . str_random(10). '.'.$ext; 
                         $pathThumb = public_path($this->BUSINESS_THUMBNAIL_IMAGE_PATH . $fileImgName);
                         
                        \File::put($pathThumb, base64_decode($fileImageValue));
                         
                        $businessImageInsert['front_image'] = $fileImgName;
                        $businessImageInsert['business_id'] = $requestData['business_id'];
                        $businessImageInsert['doc_name'] =   $requestData['doc_name'];                    
                  //  echo "<pre>"; print_r( $businessImageInsert);die();
                      

            if(!empty($requestData['back_image'])){
                   //$back_image = Input::file('back_image');
                    
                   
                       $back_image = $requestData['back_image'];
                        

                        $back_image = explode(';base64,',  $back_image);
                        $ext1 = explode('/',$back_image[0]);            
                        $ext1 = $ext1[1];
                        $back_image =$back_image[1];
                       // $fileImageValue = base64_decode( $fileImageValue);
                        
                        $fileImgName1 = 'business_back_doc_' . str_random(10). '.'.$ext1; 
                         $pathThumb = public_path($this->BUSINESS_THUMBNAIL_IMAGE_PATH . $fileImgName1);
                         
                        \File::put($pathThumb, base64_decode($back_image));
                   
                        
                        
                        $businessImageInsert['back_image'] = $fileImgName1;
            }else{
                    $businessImageInsert['back_image'] = '';

            }
            $arrayName = array(
                'doc_name' => $businessImageInsert['doc_name'],
                'business_id' => $businessImageInsert['business_id'],
                'back_image' =>  (($businessImageInsert['back_image'] != '') ) ? url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH'). $businessImageInsert['back_image']):'',
                'front_image' =>url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH'). $businessImageInsert['front_image'])
                 );

            $response = BusinessDoc::firstOrCreate($businessImageInsert);
              DB::commit();
                $this->log->info('API business images save successfully', array('login_user_id' => $user->id, 'business_id' => $requestData['business_id']));
                $outputArray['status'] = 1;
                $outputArray['data'] = $arrayName ;
                $outputArray['message'] = trans('apimessages.business_doc_added_successfully');
                $statusCode = 200;
        } 
        catch (Exception $e) {
            $this->log->error('API something went wrong while save business images', array('login_user_id' =>  $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }    
            return response()->json($outputArray, $statusCode);
    }
 /**
     * Delete Business doc
     */
      public function deleteBusinessDoc(Request $request)
      {
            $user = JWTAuth::parseToken()->authenticate();
             $headerData = $request->header('Platform');
             $outputArray = [];
             $requestData = $request->all();

             try {
                $validator = Validator::make($requestData, [
                'id' => 'required'
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while delete business images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $id = $requestData['id'];
          $businessId =   $requestData['business_id'];
           if( $businessId) {

            $response = BusinessDoc::where('id', $id)->where('business_id', $businessId)->delete();
           
             DB::commit();
                $this->log->info('API business doc delete successfully', array('login_user_id' => $user->id, 'business_id' => $requestData['business_id']));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_doc_delete_successfully');
                $statusCode = 200;
              }   
             } catch (Exception $e) {
                 $this->log->error('API something went wrong while save business images', array('login_user_id' =>  $user->id, 'error' => $e->getMessage()));
                    $outputArray['status'] = 0;
                    $outputArray['message'] = $e->getMessage();
                    $statusCode = $e->getStatusCode();
                    return response()->json($outputArray, $statusCode);
             }
              return response()->json($outputArray, $statusCode);

      }

    /**
     * Save Business
     */
    public function saveBusiness(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();
        try
        {
            // $requestData = array_map('trim', $requestData);
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'user_id' => 'required',
                'name' => ['required', 'max:100'],
                'email_id' => 'email'

            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $businessData = [];
            $businessData['user_id'] = $requestData['user_id'];
            $businessData['id'] = (isset($requestData['id']) && $requestData['id'] > 0) ? $requestData['id'] : 0;

            if(isset($requestData['parent_category']) && $requestData['parent_category'] > 0) {
                $businessData['parent_category'] = $requestData['parent_category'];
            }

            if(isset($requestData['metatags'])) {
                $businessData['metatags'] = $requestData['metatags'];
            }

            if(isset($requestData['category_id']))
            {
                $businessData['category_id'] = (isset($requestData['category_id']) && $requestData['category_id'] > 0) ? $requestData['category_id'] : '';
                if($businessData['category_id'] != '') {
                    $businessData['category_hierarchy'] = Helpers::getCategoryHierarchy($businessData['category_id']);
                }
            }

            if(isset($requestData['name'])) {
                $businessData['name'] = $requestData['name'];
            }

            if(isset($requestData['name']))
            {
                $businessData['name'] = $requestData['name'];
                $businessName = trim($businessData['name']);
                if($businessData['id'] == 0)
                {
                    //    $businessSlug = (!empty($businessName) &&  $businessName != '') ? Helpers::getSlug($businessName) : NULL;
                    //                  $businessData['business_slug'] = $businessSlug;
                    $businessSlug = SlugService::createSlug(Business::class, 'business_slug', $businessName);
                    $businessData['business_slug'] = (isset($businessSlug) && !empty($businessSlug)) ? $businessSlug : NULL;
                }
            }

            if(isset($requestData['description'])) {
                $businessData['description'] = $requestData['description'];
            }

            if(isset($requestData['establishment_year'])) {
                $businessData['establishment_year'] = $requestData['establishment_year'];
            }

            if(isset($requestData['country_code'])) {
                $businessData['country_code'] = $requestData['country_code'];
            }

            if(isset($requestData['location_id'])) {
                $businessData['location_id'] = $requestData['location_id'];
            }

            if(isset($requestData['mobile'])) {
                $businessData['mobile'] = $requestData['mobile'];
            }

            if(isset($requestData['phone'])) {
                $businessData['phone'] = $requestData['phone'];
            }

            if(isset($requestData['address'])) {
                $businessData['address'] = $requestData['address'];
            }

            if(isset($requestData['website_url'])) {
                $businessData['website_url'] = $requestData['website_url'];
            }

            if(isset($requestData['facebook_url'])) {
                $businessData['facebook_url'] = $requestData['facebook_url'];
            }

            if(isset($requestData['twitter_url'])) {
                $businessData['twitter_url'] = $requestData['twitter_url'];
            }

            if(isset($requestData['linkedin_url'])) {
                $businessData['linkedin_url'] = $requestData['linkedin_url'];
            }

            if(isset($requestData['instagram_url'])) {
                $businessData['instagram_url'] = $requestData['instagram_url'];
            }

            if(isset($requestData['latitude']) && isset($requestData['longitude']))
            {
                $businessData['latitude'] = $requestData['latitude'];
                $businessData['longitude'] = $requestData['longitude'];
            }

            if(isset($requestData['street_address'])) {
                $businessData['street_address'] = $requestData['street_address'];
            }

            if(isset($requestData['locality'])) {
                $businessData['locality'] = $requestData['locality'];
            }

            if(isset($requestData['country'])) {
                $businessData['country'] = $requestData['country'];
            }

            if(isset($requestData['state'])) {
                $businessData['state'] = $requestData['state'];
            }

            if(isset($requestData['city'])) {
                $businessData['city'] = $requestData['city'];
            }

            if(isset($requestData['taluka'])) {
                $businessData['taluka'] = $requestData['taluka'];
            }

            if(isset($requestData['district'])) {
                $businessData['district'] = $requestData['district'];
            }

            if(isset($requestData['pincode'])) {
                $businessData['pincode'] = $requestData['pincode'];
            }

            if(isset($requestData['email_id'])) {
                $businessData['email_id'] = $requestData['email_id'];
            }

            if(isset($requestData['suggested_categories'])) {
                $businessData['suggested_categories'] = $requestData['suggested_categories'];
            }


            $businessSaveData = $this->objBusiness->insertUpdate($businessData);
            
            if($businessSaveData)
            {

                if(!isset($businessData['id']) || $businessData['id'] == 0)
                {
                    $appName = config('constant.APP_SHORT_NAME');
                    $useDetail = User::find($requestData['user_id']);
                    Helpers::sendMessage($useDetail->phone, "Dear ".$useDetail->name.", Welcome to ".$appName.", We received your business profile. Our team will review and get in touch with you.");
                }

                DB::commit();
                $bunsinessId = ($businessData['id'] > 0) ? $businessData['id'] : $businessSaveData->id;
                if($bunsinessId > 0)
                {
                    $buisinessDetails = Business::find($bunsinessId);
                    if(isset($requestData['latitude']) && isset($requestData['longitude']))
                    {
                        $business_address_attributes = Helpers::getAddressAttributes($requestData['latitude'], $requestData['longitude']);
                        $business_address_attributes['business_id'] = $bunsinessId;
                        $businessObject = Business::find($bunsinessId);
                        if (!$businessObject->business_address)
                        {
                            $businessObject->business_address()->create($business_address_attributes);
                        }
                        else
                        {
                            $businessObject->business_address()->update($business_address_attributes);
                        }
                    }
                    if(isset($requestData['business_activities']) && !empty($requestData['business_activities']))
                    {
                        foreach($requestData['business_activities'] as $activitiesKey => $activitiesValue)
                        {

                            $activityInsert = [];

                            if($activitiesValue['operation'] == 'delete')
                            {
                                $activity = BusinessActivities::find($activitiesValue['id']);
                                if($activity)
                                    $activity->delete($activitiesValue['id']);
                            }
                            else
                            {
                                $activityInsert['id'] = (isset($activitiesValue['id']) && $activitiesValue['id'] > 0) ? $activitiesValue['id'] : 0;
                                $activityInsert['business_id'] = $bunsinessId;
                                $activityInsert['activity_title'] = (!empty($activitiesValue['activity_title'])) ? $activitiesValue['activity_title'] : NULL;
                                $activitySave = $this->objBusinessActivities->insertUpdate($activityInsert);
                            }
                        }
                    }
                    if(isset($requestData['working_hours']) && !empty($requestData['working_hours']))
                    {
                        $workingHoursDetails = BusinessWorkingHours::where('business_id', $bunsinessId)->orderBy('id', 'DESC')->first();
                        $workingHoursInsert = Helpers::setWorkingHours($requestData['working_hours']);            
                         // $workingHoursInsert['id'] = (count($workingHoursDetails) > 0 && $workingHoursDetails->id > 0) ? $workingHoursDetails->id : 0;
                        $workingHoursInsert['id'] = ($workingHoursDetails && $workingHoursDetails->id > 0) ? $workingHoursDetails->id : 0;
                        $workingHoursInsert['business_id'] = $bunsinessId;
                        if(isset($requestData['working_hours']['timezone']) && $requestData['working_hours']['timezone'] != '')
                        {
                            $workingHoursInsert['timezone'] = $requestData['working_hours']['timezone'];
                        }

                        $workingHoursSave = $this->objBusinessWorkingHours->insertUpdate($workingHoursInsert);
                    }
                    if($businessData['id'] == 0 && !isset($requestData['id']))
                    {
                        $userData = User::find($requestData['user_id']);
                        if($userData)
                        {
                            $ownerInsert = [];
                            $ownerInsert['id'] = 0;
                            $ownerInsert['business_id'] = $bunsinessId;
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
                             $ownerSave = $this->objOwner->insertUpdate($ownerInsert);
                        }
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] =  trans('apimessages.business_added_success');
                    $outputArray['data'] =  array();
                    $outputArray['data']['id'] = $buisinessDetails->id;
                    $outputArray['data']['business_slug'] = $buisinessDetails->business_slug;
                    $statusCode = 200;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.business_id_not_found');
                    $statusCode = 200;
                }
            }
            else
            {
                DB::rollback();
                $this->log->error('API something went wrong while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while save business', array('login_user_id' =>  Auth::id(), 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Agent Save User
     */
    public function agentSaveUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();
        try
        {
            DB::beginTransaction();
            $userData = [];
            $userRequestData = array_map('trim', $requestData);
            if(isset($requestData['id']) && $requestData['id'] > 0)
            {
                $validator = Validator::make($userRequestData, [
                    'name' => ['required', 'max:100'],
                    'phone' => 'required|digits:10',
                    'email' => 'email'
                ]);
                if ($validator->fails())
                {
                    DB::rollback();
                    $this->log->error('API validation failed while agent save user', array('login_user_id' => $user->id));
                    $outputArray['status'] = 0;
                    $outputArray['message'] = $validator->messages()->all()[0];
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                $userData['id'] = $userRequestData['id'];
                $userData['phone'] = $userRequestData['phone'];
                $userData['country_code'] = $userRequestData['country_code'];
                $userData['name'] = $userRequestData['name'];
                $userData['email'] = $userRequestData['email'];
                if(isset($userRequestData['password']) && !empty($userRequestData['password']))
                {
                    $userData['password'] = bcrypt($userRequestData['password']);
                }
            }
            else
            {
                $validator = Validator::make($userRequestData, [
                    'name' => ['required', 'max:100'],
                    'phone' => 'required|digits:10|unique:users,phone',
                    'email' => 'email',
                    'password' =>'required|min:8|max:20',
                ]);
                if ($validator->fails())
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = $validator->messages()->all()[0];
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                $userData['phone'] = $userRequestData['phone'];
                $userData['country_code'] = $userRequestData['country_code'];
                $userData['name'] = $userRequestData['name'];
                $userData['email'] = $userRequestData['email'];
                $userData['password'] = bcrypt($userRequestData['password']);
            }
            $response = $this->objUser->insertUpdate($userData);
            $agentUserId = (isset($userData['id']) && $userData['id'] > 0) ? $userData['id'] : $response->id;
            if($response)
            {
                DB::commit();
                $this->log->info('API agent save user successfully', array('login_user_id' => $user->id, 'user_id' => $agentUserId));
                $outputArray['status'] = 1;
                $outputArray['message'] =  trans('apimessages.user_data_update_successfully');
                $outputArray['data'] =  array();
                $outputArray['data']['id'] = (isset($userData['id']) && $userData['id'] > 0) ? $userData['id'] : $response->id;
                $statusCode = 200;
            }
            else
            {
                DB::rollback();
                $this->log->error('API something went wrong while agent save user', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while agent save user', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Send Add Member OTP
     */
    public function sendAddMemberOTP(Request $request)
    {
        $headerData = $request->header('Platform');
        $user = JWTAuth::parseToken()->authenticate();
        try
        {
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'country_code' => 'required'
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while send add member OTP', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $phoneNumber = $request->phone;
                $userData = User::where('phone', $request->phone)->where('country_code',$request->country_code)->first();
                if(!$userData)
                {
                    //                  If user not exists
                    $agentOtp = UserAgentOTP::firstOrCreate(['agent_id' => $user->id, 'phone' => $request->phone]);
                    $agentOtp->otp = Helpers::genrateOTP();
                    $msg = getRegMsg($agentOtp->otp);
                    $response = Helpers::sendMessage($request->phone,$msg);
                    if($response['status']) {
                        $agentOtp->save();
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.otp_send_successfully');
                        $outputArray['data'] = new \stdClass();
                        $statusCode = 200;
                    } else {
                        $this->log->error('API something went wrong while send add member OTP', array('login_user_id' => $user->id));
                        $outputArray['status'] = 0;
                        $outputArray['message'] = $response['message'];
                        $outputArray['data'] = new \stdClass();
                        $statusCode = 200;
                    }
                    return response()->json($outputArray, $statusCode);
                }
                else
                {
                    if($userData->singlebusiness) {
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.user_with_already_one_business');
                        $statusCode = 200;
                        $outputArray['data'] = array();
                        $outputArray['data']['id'] = $userData->id;
                        $outputArray['data']['name'] = $userData->name;
                        $outputArray['data']['email'] = $userData->email;
                        $outputArray['data']['phone'] = $userData->phone;
                        $outputArray['data']['dob'] = $userData->dob;
                        $outputArray['data']['gender'] = $userData->gender;
                    } else {
                        $agentOtp = UserAgentOTP::firstOrCreate(['agent_id' => $user->id, 'phone' => $request->phone]);
                        $agentOtp->otp = Helpers::genrateOTP();
                        $msg = getRegMsg($agentOtp->otp);
                        $response = Helpers::sendMessage($request->phone,$msg);
                        if($response['status']) {
                            $agentOtp->save();
                            $outputArray['status'] = 1;
                            $outputArray['message'] = trans('apimessages.otp_send_successfully');
                            $statusCode = 200;
                            $outputArray['data'] = array();
                            $outputArray['data']['id'] = $userData->id;
                            $outputArray['data']['name'] = $userData->name;
                            $outputArray['data']['email'] = $userData->email;
                            $outputArray['data']['phone'] = $userData->phone;
                            $outputArray['data']['profile_pic'] = (!empty($userData->profile_pic) && Storage::disk(config('constant.DISK'))->exists($this->USER_THUMBNAIL_IMAGE_PATH .$userData->profile_pic)) ? Storage::disk(config('constant.DISK'))->url($this->USER_THUMBNAIL_IMAGE_PATH .$userData->profile_pic) : url($this->catgoryTempImage);
                            $outputArray['data']['dob'] = $userData->dob;
                            $outputArray['data']['gender'] = $userData->gender;
                        } else {
                            $this->log->error('API something went wrong while send add member OTP', array('login_user_id' => $user->id));
                            $outputArray['status'] = 0;
                            $outputArray['message'] = $response['message'];
                            $outputArray['data'] = new \stdClass();
                            $statusCode = 200;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while send add member OTP', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    public function verifyAgentOTP(Request $request)
    {
        $headerData = $request->header('Platform');
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = Input::all();

        try
        {
            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'phone' => 'required',
            ]);
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $phoneNumber = $request->phone;
                $otp = $request->otp;
                $userData = UserAgentOTP::where('phone', $requestData['phone'])->where('agent_id', $user->id)->where('otp', $requestData['otp'])->first();
                if($userData)
                {
                    $otp_sent = Carbon::parse($userData->updated_at);
                    $now = Carbon::now();
                    $diff = $otp_sent->diffInMinutes($now);
                    if($diff > 5) {
                        $statusCode = 200;
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.otp_expired');
                        $outputArray['data'] = new \stdClass();
                    } else {
                        $userData = User::where('phone', $request->phone)->where('country_code',$request->country_code)->first();
                        if(!$userData)
                        {
                            $outputArray['status'] = 1;
                            $outputArray['message'] = trans('apimessages.new_user');
                            $outputArray['data'] = new \stdClass();
                            $statusCode = 200;
                            $agentUserOTPDelete = UserAgentOTP::where('agent_id', $user->id)->where('phone', $requestData['phone'])->where('otp', $requestData['otp'])->delete();
                            return response()->json($outputArray, $statusCode);
                        }
                        else
                        {
                            if($userData->singlebusiness) {
                                $outputArray['status'] = 0;
                                $outputArray['message'] = trans('apimessages.user_with_already_one_business');
                                $statusCode = 200;
                                $outputArray['data'] = array();
                                $outputArray['data']['id'] = $userData->id;
                                $outputArray['data']['name'] = $userData->name;
                                $outputArray['data']['email'] = $userData->email;
                                $outputArray['data']['phone'] = $userData->phone;
                                $outputArray['data']['dob'] = $userData->dob;
                                $outputArray['data']['gender'] = $userData->gender;
                            } else {
                                $outputArray['status'] = 1;
                                $outputArray['message'] = trans('apimessages.user_verified_success');
                                $statusCode = 200;
                                $outputArray['data'] = array();
                                $outputArray['data']['id'] = $userData->id;
                                $outputArray['data']['name'] = $userData->name;
                                $outputArray['data']['email'] = $userData->email;
                                $outputArray['data']['phone'] = $userData->phone;
                                $outputArray['data']['dob'] = $userData->dob;
                                $outputArray['data']['gender'] = $userData->gender;
                                $agentUserOTPDelete = UserAgentOTP::where('agent_id', $user->id)->where('phone', $requestData['phone'])->where('otp', $requestData['otp'])->delete();
                            }
                        }
                    }
                }
                else
                {
                    $this->log->error('API something went wrong while verify agent OTP', array('login_user_id' => $user->id));
                    $statusCode = 200;
                    $outputArray = ['status' => 0, 'message' => trans('apimessages.invalid_otp')];
                    $outputArray['data'] = new \stdClass();
                }
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while verify agent OTP', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Get Near By Businesses
     */
    public function getNearByBusinesses1(Request $request)
    {
        //$user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        $outputArray = [];
        try
        {
            $filters = [];
            $filters['approved'] = 1;
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if(isset($request->page) && $request->page != 0)
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }
            else
            {
                if(isset($request->page) && $request->page != 0)
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }
            if(isset($request->sortBy) && !empty($request->sortBy) && $request->sortBy == 'ratings')
            {
                $filters['sortBy'] = 'ratings';
                $getBusinessListingData = $this->objBusiness->getBusinessesByRating($filters);
            }
            elseif (isset($request->sortBy) && $request->sortBy == 'nearMe' && isset($request->radius) && !empty ($request->radius) && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
            {
                // $filters['sortBy'] = 'nearMe';
                // $filters['radius'] = $request->radius;
                // $filters['latitude'] = $request->latitude;
                // $filters['longitude'] = $request->longitude;
                // $getBusinessListingData = $this->objBusiness->getBusinessesByNearMe($filters);
                // while($getBusinessListingData->count() < 15) {
                //     $filters['radius'] = $filters['radius']*2;
                //     $getBusinessListingData = $this->objBusiness->getBusinessesByNearMe($filters);
                // }
                $filters['sortBy'] = 'nearMe';
                $filters['latitude'] = $request->latitude;
                $filters['longitude'] = $request->longitude;
                $getBusinessListingData = $this->objBusiness->getBusinessesByNearMe($filters);
            }
            else
            {
                $filters['orderBy'] = 'promoted';
                // $getBusinessListingData = $this->objBusiness->getAll($filters);
                $getBusinessListingData = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            }
            if($getBusinessListingData)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.nearby_me_business_fetched_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();
                $i = 0;
                foreach ($getBusinessListingData as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['categories'] = array();
                    if(!empty($value->category_id) && $value->category_id != '')
                    {
                        $categoryIdsArray = (explode(',', $value->category_id));
                        if($categoryIdsArray)
                        {
                            $j = 0;
                            foreach($categoryIdsArray as $cIdKey => $cIdValue)
                            {
                                $categoryData = Category::find($cIdValue);
                                if(!empty($categoryData))
                                {
                                    $outputArray['data'][$i]['categories'][$j]['category_id'] = $categoryData->id;
                                    $outputArray['data'][$i]['categories'][$j]['category_name'] = $categoryData->name;
                                    $outputArray['data'][$i]['categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                                    // if(!empty($categoryData->cat_logo))
                                    // {
                                    //     //$catLogoPath = $this->categoryLogoThumbImagePath.$categoryData->cat_logo;

                                    // }

                                    if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) {
                                        $s3url =   Config::get('constant.s3url');
                                        // return $s3url;
                                       $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                                  }else{
                                      $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                  }
                                  // $catLogoPath = (($categoryData->cat_logo != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));


                                    $outputArray['data'][$i]['categories'][$j]['category_logo'] = $catLogoPath;

                                    $outputArray['data'][$i]['categories'][$j]['parent_category_id'] = $categoryData->parent;
                                    $j++;
                                }
                            }
                        }
                    }
                    $parentCatArray = [];
                    $outputArray['data'][$i]['parent_categories'] = array();
                    if (!empty($value->parent_category) && $value->parent_category != '')
                    {
                        $parentcategoryIdsArray = (explode(',', $value->parent_category));
                        if ($parentcategoryIdsArray)
                        {
                            $j = 0;
                            foreach ($parentcategoryIdsArray as $pIdKey => $pIdValue)
                            {
                                $parentcategoryData = Category::find($pIdValue);
                                if (!empty($parentcategoryData))
                                {
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_id'] = $parentcategoryData->id;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_name'] = $parentcategoryData->name;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_slug'] = (!empty($parentcategoryData->category_slug)) ? $parentcategoryData->category_slug : '';


                                    if ($parentcategoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo) {
                                        $s3url =   Config::get('constant.s3url');
                                        // return $s3url;
                                       $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo;
                                  }else{
                                      $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                  }

                                  // $catLogoPath = (($parentcategoryData->cat_logo != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));

                                    $outputArray['data'][$i]['parent_categories'][$j]['category_logo'] = $catLogoPath;

                                    $parentCatArray[] =  $parentcategoryData->name;
                                    $j++;
                                }
                            }
                        }
                    }

                    $outputArray['data'][$i]['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                    $outputArray['data'][$i]['categories_name_list'] = $outputArray['data'][$i]['parent_category_name'];


                    $outputArray['data'][$i]['owners'] = '';

                        if($value->owners)
                        {
                            $owners = [];
                            foreach($value->owners as $owner)
                            {
                                $owners[] = $owner->full_name;
                            }
                            if(!empty($owners))
                            {
                                $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                            }
                        }

                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';

                    $outputArray['data'][$i]['address'] = $value->address;
                    $outputArray['data'][$i]['street_address'] = $value->street_address;
                    $outputArray['data'][$i]['locality'] = $value->locality;
                    $outputArray['data'][$i]['country'] = $value->country;
                    $outputArray['data'][$i]['state'] = $value->state;
                    $outputArray['data'][$i]['city'] = $value->city;
                    $outputArray['data'][$i]['taluka'] = $value->taluka;
                    $outputArray['data'][$i]['district'] = $value->district;
                    $outputArray['data'][$i]['pincode'] = $value->pincode;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : 0;
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : 0;


                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {


                        if($value->document_approval ==3){
                             $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($value->document_approval ==2){

                            $outputArray['data'][$i]['membership_type_icon']= url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data'][$i]['membership_type_icon']= url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                        //$outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                    }

                    // if(isset($value->businessImagesById) && !empty($value->businessImagesById->image_name))
                    // {
                    //     // $img_thumb_path = $this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->businessImagesById->image_name;
                    //     // $img_thumb_url = (!empty($img_thumb_path) && file_exists($img_thumb_path)) ? $img_thumb_path : '';

                    // }
                    // else
                    // {
                    //     $outputArray['data'][$i]['business_image'] = url($this->catgoryTempImage);
                    // }

                    // $imgThumbUrl = ((isset($value->businessImagesById) && !empty($value->businessImagesById->image_name) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->businessImagesById->image_name))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name) : !empty($outputArray['data'][$i]['parent_categories']) ? $outputArray['data'][$i]['parent_categories'][0]['category_logo'] : url(Config::get('constant.DEFAULT_IMAGE'));
                    // $outputArray['data'][$i]['business_image'] = $imgThumbUrl;

                    $s3url =   Config::get('constant.s3url');
                    if (isset($value->businessImagesById) && !empty($value->businessImagesById->image_name) &&  $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name) {
                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl =  $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name;
                     } else if (!empty($outputArray['data'][$i]['parent_categories'])) {
                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl = $outputArray['data'][$i]['parent_categories'][0]['category_logo'];
                    } else {
                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                    }

                    // $businessLogoThumbImgPath = ((isset($value->business_logo) && !empty($value->business_logo) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->business_logo))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo) : $imgThumbUrl;
                    // $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;
                    if (isset($value->business_logo) && !empty($value->business_logo) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->business_logo)) {
                        $outputArray['data'][$i]['logo_thumbnail'] =  $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo;
                    } else {
                        $outputArray['data'][$i]['logo_thumbnail'] = $imgThumbUrl;
                    }

                    $i++;
                }
                $this->log->info('API getNearByBusinesses get successfully');
            }
            else
            {
                $this->log->info('API getNearByBusinesses no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getNearByBusinesses', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    public function getPublicBusinessRatings(Request $request)
    {
        $headerData = $request->header('Platform');
        $outputArray = [];  

        $businessId = Business::where('url_slug', $request->url_slug)->with(['getBusinessRatings','user'])->first();

        $user = $businessId->user;
        $businessId = $businessId->id;
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        try
        {
            $filters = [];
            $filters['updated_at'] = 'updated_at';
            $totalBusinessesRatingData = BusinessRatings::where('business_id', $businessId)->get();

            $totalBusinessesRatingCount = count($totalBusinessesRatingData);
            $businessListing = '';
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                $offset = Helpers::getWebOffset($pageNo);
                if(!empty($businessId))
                {
                    $filters['business_id'] = $businessId;
                    $filters['offset'] = $offset;
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $businessListing = $this->objBusinessRatings->getAll($filters);
                }
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {
                $offset = Helpers::getOffset($pageNo);
                if(!empty($businessId))
                {
                    $filters['business_id'] = $businessId;
                    $filters['offset'] = $offset;
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $businessListing = $this->objBusinessRatings->getAll($filters);
                }
            }
            if($businessListing)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_ratings_fetched_successfully');
                $statusCode = 200;

                if($headerData == Config::get('constant.WEBSITE_PLATFORM'))
                {
                    if($businessListing->count() < Config::get('constant.WEBSITE_RECORD_PER_PAGE'))
                    {
                        $outputArray['loadMore'] = 0;
                    } else{
                        $offset = Helpers::getWebOffset($pageNo+1);
                        $filters = [];
                        $filters['business_id'] = $businessId;
                        $filters['offset'] = $offset;
                        $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');

                        $businessRatingCount = $this->objBusinessRatings->getAll($filters);
                        $outputArray['loadMore'] = (count($businessRatingCount) > 0) ? 1 : 0 ;
                    }
                }
                else
                {
                    if($businessListing->count() < Config::get('constant.API_RECORD_PER_PAGE'))
                    {
                        $outputArray['loadMore'] = 0;
                    } else{
                        $offset = Helpers::getOffset($pageNo+1);
                        $filters = [];
                        $filters['business_id'] = $businessId;
                        $filters['offset'] = $offset;
                        $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                        $businessRatingCount =  $this->objBusinessRatings->getAll($filters);
                        $outputArray['loadMore'] = (count($businessRatingCount) > 0) ? 1 : 0 ;
                    }
                }

                $outputArray['data'] = array();
                $outputArray['data']['avg_rating'] = round($totalBusinessesRatingData->avg('rating'), 1);
                $userRating = $totalBusinessesRatingData->where('user_id', $user->id)->where('business_id', $businessId)->pluck('rating')->first();
                $outputArray['data']['user_rating'] = (isset($userRating) && !empty($userRating)) ? intval($userRating) : '';
                $outputArray['data']['start_5_rating'] = $totalBusinessesRatingData->where('rating', '=', '5.0')->count();
                $outputArray['data']['start_4_rating'] = $totalBusinessesRatingData->where('rating', '=', '4.0')->count();
                $outputArray['data']['start_3_rating'] = $totalBusinessesRatingData->where('rating', '=', '3.0')->count();
                $outputArray['data']['start_2_rating'] = $totalBusinessesRatingData->where('rating', '=', '2.0')->count();
                $outputArray['data']['start_1_rating'] = $totalBusinessesRatingData->where('rating', '=', '1.0')->count();
                $outputArray['data']['total'] = (isset($totalBusinessesRatingCount) && !empty($totalBusinessesRatingCount)) ? $totalBusinessesRatingCount : 0;

                $outputArray['data']['reviews'] = array();
                $l = 0;
                foreach($businessListing as $ratingKey => $ratingValue)
                {
                    $outputArray['data']['reviews'][$l]['id'] = $ratingValue->id;
                    $outputArray['data']['reviews'][$l]['rating'] = $ratingValue->rating;
                    $outputArray['data']['reviews'][$l]['name'] = (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->name)) ? $ratingValue->getUsersData->name : '';

                    $outputArray['data']['reviews'][$l]['timestamp'] = (!empty($ratingValue->updated_at)) ? strtotime($ratingValue->updated_at)*1000 : '';
                    $outputArray['data']['reviews'][$l]['review'] = $ratingValue->comment;

                    // if(isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic))
                    // {
                    //     // $imgThumbPath = $this->USER_THUMBNAIL_IMAGE_PATH.$ratingValue->getUsersData->profile_pic;
                    //     // $imgThumbUrl = (!empty($imgThumbPath) && file_exists($imgThumbPath)) ? $imgThumbPath : '';
                        // }
                       if (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic) && Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }


                    // $imgThumbUrl = ((isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                    $outputArray['data']['reviews'][$l]['image_url'] = $imgThumbUrl;
                    $outputArray['data']['reviews'][$l]['user_business_id'] = (isset($ratingValue->getUsersData->singlebusiness) && $ratingValue->getUsersData->singlebusiness->id != '')? (string)$ratingValue->getUsersData->singlebusiness->id : '';
                    $l++;
                }
                $this->log->info('API getBusinessRatings get successfully');
            }
            else
            { 
                $this->log->info('API getBusinessRatings no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getBusinessRatings', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    /**
     * Get Business Ratings
     */
    public function getBusinessRatings(Request $request)
    {
        $loginUserId = 0;
        try {
           
           $user = JWTAuth::parseToken()->authenticate();
           $this->log->info('Logged in user');
           $loginUserId = $user->id;
       } catch (TokenInvalidException $e) {
           $this->log->info('Not logged in user');
           $loginUserId = 0;
       } catch (JWTAuthException $e) {
           $this->log->info('Not logged in user');
           $loginUserId = 0;
       } catch (JWTException $e) {
           $this->log->info('Not logged in user');
           $loginUserId = 0;
       } 

        $headerData = $request->header('Platform');
        $outputArray = [];
        
        $businessId = (isset($request->business_id) && $request->business_id > 0) ? $request->business_id : 0;
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        try
        {
            $filters = [];
            $filters['updated_at'] = 'updated_at';
            $totalBusinessesRatingData = BusinessRatings::where('business_id', $businessId)->get();
            $totalBusinessesRatingCount = count($totalBusinessesRatingData);
            $businessListing = '';
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                $offset = Helpers::getWebOffset($pageNo);
                if(!empty($businessId))
                {
                    $filters['business_id'] = $businessId;
                    $filters['offset'] = $offset;
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $businessListing = $this->objBusinessRatings->getAll($filters);
                }
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {
                $offset = Helpers::getOffset($pageNo);
                if(!empty($businessId))
                {
                    $filters['business_id'] = $businessId;
                    $filters['offset'] = $offset;
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $businessListing = $this->objBusinessRatings->getAll($filters);
                }
            }
            if($businessListing)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_ratings_fetched_successfully');
                $statusCode = 200;

                if($headerData == Config::get('constant.WEBSITE_PLATFORM'))
                {
                    if($businessListing->count() < Config::get('constant.WEBSITE_RECORD_PER_PAGE'))
                    {
                        $outputArray['loadMore'] = 0;
                    } else{
                        $offset = Helpers::getWebOffset($pageNo+1);
                        $filters = [];
                        $filters['business_id'] = $businessId;
                        $filters['offset'] = $offset;
                        $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');

                        $businessRatingCount = $this->objBusinessRatings->getAll($filters);
                        $outputArray['loadMore'] = (count($businessRatingCount) > 0) ? 1 : 0 ;
                    }
                }
                else
                {
                    if($businessListing->count() < Config::get('constant.API_RECORD_PER_PAGE'))
                    {
                        $outputArray['loadMore'] = 0;
                    } else{
                        $offset = Helpers::getOffset($pageNo+1);
                        $filters = [];
                        $filters['business_id'] = $businessId;
                        $filters['offset'] = $offset;
                        $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                        $businessRatingCount =  $this->objBusinessRatings->getAll($filters);
                        $outputArray['loadMore'] = (count($businessRatingCount) > 0) ? 1 : 0 ;
                    }
                }

                $outputArray['data'] = array();
                $outputArray['data']['avg_rating'] = round($totalBusinessesRatingData->avg('rating'), 1);
                $userRating = $totalBusinessesRatingData->where('user_id', $loginUserId)->where('business_id', $businessId)->pluck('rating')->first();
                $outputArray['data']['user_rating'] = (isset($userRating) && !empty($userRating)) ? intval($userRating) : '';
                $outputArray['data']['start_5_rating'] = $totalBusinessesRatingData->where('rating', '=', '5.0')->count();
                $outputArray['data']['start_4_rating'] = $totalBusinessesRatingData->where('rating', '=', '4.0')->count();
                $outputArray['data']['start_3_rating'] = $totalBusinessesRatingData->where('rating', '=', '3.0')->count();
                $outputArray['data']['start_2_rating'] = $totalBusinessesRatingData->where('rating', '=', '2.0')->count();
                $outputArray['data']['start_1_rating'] = $totalBusinessesRatingData->where('rating', '=', '1.0')->count();
                $outputArray['data']['total'] = (isset($totalBusinessesRatingCount) && !empty($totalBusinessesRatingCount)) ? $totalBusinessesRatingCount : 0;

                $outputArray['data']['reviews'] = array();
                $l = 0;
                foreach($businessListing as $ratingKey => $ratingValue)
                {
                    $outputArray['data']['reviews'][$l]['id'] = $ratingValue->id;
                    $outputArray['data']['reviews'][$l]['rating'] = $ratingValue->rating;
                    $outputArray['data']['reviews'][$l]['name'] = (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->name)) ? $ratingValue->getUsersData->name : '';

                    $outputArray['data']['reviews'][$l]['timestamp'] = (!empty($ratingValue->updated_at)) ? strtotime($ratingValue->updated_at)*1000 : '';
                    $outputArray['data']['reviews'][$l]['review'] = $ratingValue->comment;

                    // if(isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic))
                    // {
                    //     // $imgThumbPath = $this->USER_THUMBNAIL_IMAGE_PATH.$ratingValue->getUsersData->profile_pic;
                    //     // $imgThumbUrl = (!empty($imgThumbPath) && file_exists($imgThumbPath)) ? $imgThumbPath : '';
                    // }

                    if (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic) && Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic){
                        $s3url =   Config::get('constant.s3url');
                       $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic;
                  }else{
                      $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                  }

                    // $imgThumbUrl = ((isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                    $outputArray['data']['reviews'][$l]['image_url'] = $imgThumbUrl;
                    $outputArray['data']['reviews'][$l]['user_business_id'] = (isset($ratingValue->getUsersData->singlebusiness) && $ratingValue->getUsersData->singlebusiness->id != '')? (string)$ratingValue->getUsersData->singlebusiness->id : '';
                    $l++;
                }
                $this->log->info('API getBusinessRatings get successfully');
            }
            else
            {
                $this->log->info('API getBusinessRatings no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getBusinessRatings', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Add Business Rating
     */
    public function addBusinessRating(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $outputArray = [];
        $data = [];
        $requestData = array_map('trim',$request->all());
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData,
                [
                    'user_id' => 'required',
                    'business_id' => 'required',
                    'rating' => 'required'
                ]
            );
            if ($validator->fails())
            {
                DB::rollback();
                $this->log->error('API validation failed while addBusinessRating', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            else
            {
                $data['user_id'] = $requestData['user_id'];
                $data['business_id'] = $requestData['business_id'];
                $data['rating'] = $requestData['rating'];
                $data['comment'] = (isset($requestData['comment']) && !empty($requestData['comment'])) ? $requestData['comment'] : NULL;
                $getRatingRecord = Helpers::getSameUserBusinessData($data['user_id'], $data['business_id']);

                if($getRatingRecord)
                {
                    $data['id'] = $getRatingRecord->id;
                    $message =  trans('apimessages.business_rating_updated_successfully');
                }
                else{
                    $message =  trans('apimessages.business_rating_added_successfully');
                }
                $response = $this->objBusinessRatings->insertUpdate($data);

                if($response)
                {
                    $businessDetail = Business::find($requestData['business_id']);
                    if(isset($businessDetail->user))
                    {
                        //Send push notification to Business User
                        $notificationData = [];
                        $notificationData['title'] = 'Business Rated';
                        $notificationData['message'] = 'Dear '.$businessDetail->user->name.',  Your business just got new review.  Find out what they said.';
                        $notificationData['type'] = '4';
                        $notificationData['business_id'] = $businessDetail->id;
                        $notificationData['business_name'] = $businessDetail->name;
                        Helpers::sendPushNotification($businessDetail->user_id, $notificationData);

                        // notification list
                        $notificationListArray = [];
                        $notificationListArray['user_id'] = $businessDetail->user_id;
                        $notificationListArray['business_id'] = $businessDetail->id;
                        $notificationListArray['title'] = 'Business Rated';
                        $notificationListArray['message'] =  'Dear '.$businessDetail->user->name.',  Your business just got new review.  Find out what they said.';
                        $notificationListArray['type'] = '4';
                        $notificationListArray['business_name'] = $businessDetail->name;
                        $notificationListArray['user_name'] = $businessDetail->user->name;
                        $notificationListArray['activity_user_id'] = $requestData['user_id'];


                        NotificationList::create($notificationListArray);

                    }

                    DB::commit();
                    $this->log->info('API addBusinessRating save successfully', array('login_user_id' => $user->id));
                    $outputArray['status'] = 1;
                    $outputArray['message'] = $message;
                    $statusCode = 200;
                }
                else
                {
                    DB::rollback();
                    $this->log->error('API something went wrong while addBusinessRating', array('login_user_id' => $user->id));
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 400;
                }
            }
        } catch (Exception $e) {
            DB::rollback();
            $this->log->error('API something went wrong while addBusinessRating', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    /**
     * get online stores of business
     *
     * @param  mixed $request
     * @return void
     */
    public function getBusinessOnlineStores(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(),
                [
                    'business_id' => 'required',
                ]
            );
            if ($validator->fails())
            {
                $this->log->error('API validation failed while getBusinessOnlineStores');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            else
            {
                $business = Business::find($request->business_id);
                if($business)
                {
                    $onlineStores = $this->getEntityOnlineStores($business->online_store_url);

                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.business_online_store_fetched_successfully');
                    $outputArray['data'] = $onlineStores;
                    $statusCode = 200;
                }
                else
                {
                    $this->log->error('API something went wrong while getBusinessOnlineStores');
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 400;
                }
                return response()->json($outputArray, $statusCode);
            }
        } catch (\Exception $e) {
            $this->log->error('API something went wrong while getBusinessOnlineStores');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * save online stores of business
     *
     * @param  mixed $request
     * @return void
     */
    public function saveBusinessOnlineStores(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(),
                [
                    'business_id' => 'required',
                ]
            );
            if ($validator->fails())
            {
                $this->log->error('API validation failed while saveBusinessOnlineStores');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            else
            {
                $business = Business::find($request->business_id);
                if($business)
                {
                    $business->online_store_url = $request->online_stores;
                    $business->save(); 

                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.business_online_store_created_successfully');
                    $outputArray['data'] = $this->getEntityOnlineStores($business->online_store_url);

                    $statusCode = 200;
                }
                else
                {
                    $this->log->error('API something went wrong while saveBusinessOnlineStores');
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $outputArray['data']= [];
                    $statusCode = 400;
                }
                return response()->json($outputArray, $statusCode);
            }
        } catch (\Exception $e) {
            $this->log->error('API something went wrong while saveBusinessOnlineStores');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * get all active online stores
     *
     * @return void
     */
    public function getOnlineStores()
    {
        $activeStores = OnlineStore::select('id','name')->whereStatus(1)->get();
        $outputArray['status'] = 1;
        $outputArray['message'] = trans('apimessages.online_store_fetched_successfully');
        $outputArray['data'] = $activeStores;
        $statusCode = 200;
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Get Service Details
     */
    public function getServiceDetails(Request $request)
    {
        $outputArray = [];
        $serviceId = (isset($request->service_id) && $request->service_id > 0) ? $request->service_id : 0;
        try
        {
            $serviceDetails = Service::find($serviceId);
            if($serviceDetails)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.service_details_fetched_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();

                $outputArray['data']['business_id'] = (isset($serviceDetails->serviceBusiness) && !empty($serviceDetails->serviceBusiness->id)) ? $serviceDetails->serviceBusiness->id : 0;
                $outputArray['data']['business_name'] = (isset($serviceDetails->serviceBusiness) && !empty($serviceDetails->serviceBusiness->name)) ? $serviceDetails->serviceBusiness->name : '';

                $outputArray['data']['name'] = $serviceDetails->name;
                $outputArray['data']['descriptions'] = (isset($serviceDetails->description) && !empty($serviceDetails->description)) ? $serviceDetails->description : '';
                $outputArray['data']['metatags'] = (isset($serviceDetails->metatags) && !empty($serviceDetails->metatags)) ? $serviceDetails->metatags : '';
                $outputArray['data']['cost'] = (isset($serviceDetails->cost) && !empty($serviceDetails->cost)) ? $serviceDetails->cost : '';
                // if(!empty($serviceDetails->logo))
                // {
                //     // $imgOriginalPath = $this->SERVICE_ORIGINAL_IMAGE_PATH.$serviceDetails->logo;
                //     // $imgOriginalUrl = (!empty($imgOriginalPath) && file_exists($imgOriginalPath)) ? url($imgOriginalPath) : '';
                // }

                 if ($serviceDetails->logo != '' && Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgOriginalUrl = $s3url.Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo;
                                        }else{
                                            $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }

                // $imgOriginalUrl = (($serviceDetails->logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                $outputArray['data']['logo'] = $imgOriginalUrl;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }



     public function getPublicServiceDetails(Request $request)
    {
        $outputArray = [];
        $businessId = Business::where('url_slug', $request->url_slug)->first();
        $serviceId = (isset($request->service_id) && $request->service_id > 0) ? $request->service_id : 0;
        try
        {
            $serviceDetails = Service::where('business_id', $businessId ? $businessId->id:'')->find($serviceId);
            if($serviceDetails && $businessId)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.service_details_fetched_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();

                $outputArray['data']['business_id'] = (isset($serviceDetails->serviceBusiness) && !empty($serviceDetails->serviceBusiness->id)) ? $serviceDetails->serviceBusiness->id : 0;
                $outputArray['data']['business_name'] = (isset($serviceDetails->serviceBusiness) && !empty($serviceDetails->serviceBusiness->name)) ? $serviceDetails->serviceBusiness->name : '';

                $outputArray['data']['name'] = $serviceDetails->name;
                $outputArray['data']['descriptions'] = (isset($serviceDetails->description) && !empty($serviceDetails->description)) ? $serviceDetails->description : '';
                $outputArray['data']['metatags'] = (isset($serviceDetails->metatags) && !empty($serviceDetails->metatags)) ? $serviceDetails->metatags : '';
                $outputArray['data']['cost'] = (isset($serviceDetails->cost) && !empty($serviceDetails->cost)) ? $serviceDetails->cost : '';
                // if(!empty($serviceDetails->logo))
                // {
                //     // $imgOriginalPath = $this->SERVICE_ORIGINAL_IMAGE_PATH.$serviceDetails->logo;
                //     // $imgOriginalUrl = (!empty($imgOriginalPath) && file_exists($imgOriginalPath)) ? url($imgOriginalPath) : '';
                // }

                 if ($serviceDetails->logo != '' && Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgOriginalUrl = $s3url.Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo;
                                        }else{
                                            $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }

                // $imgOriginalUrl = (($serviceDetails->logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.SERVICE_ORIGINAL_IMAGE_PATH').$serviceDetails->logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                $outputArray['data']['logo'] = $imgOriginalUrl;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    public function getPublicProductDetails(Request $request)
    {

        // return $request->all();
        $outputArray = [];
        $businessId = Business::where('url_slug', $request->url_slug)->with(['products'])->first();
        $productId = (isset($request->product_id) && $request->product_id > 0) ? $request->product_id : 0;
        // $productId = $businessId->products->where('id' , $request->product_id)->first();
        try
        {
         if($productId)
            {
                    // $productId = $productId->id;
                 // $productId =  $businessId->id;


                    $productDetails = Product::where('business_id', $businessId ? $businessId->id:'')->find($productId);
                    if($productDetails  && $businessId)
                    {
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.product_details_fetched_successfully');
                        $statusCode = 200;
                        $outputArray['data'] = array();

                        $outputArray['data']['business_id'] = (isset($productDetails->productBusiness) && !empty($productDetails->productBusiness->id)) ? $productDetails->productBusiness->id : 0;
                        $outputArray['data']['business_name'] = (isset($productDetails->productBusiness) && !empty($productDetails->productBusiness->name)) ? $productDetails->productBusiness->name : '';

                        $outputArray['data']['name'] = $productDetails->name;
                        $outputArray['data']['descriptions'] = (isset($productDetails->description) && !empty($productDetails->description)) ? $productDetails->description : '';
                        $outputArray['data']['metatags'] = (isset($productDetails->metatags) && !empty($productDetails->metatags)) ? $productDetails->metatags : '';
                        $outputArray['data']['cost'] = (isset($productDetails->cost) && !empty($productDetails->cost)) ? $productDetails->cost : '';
                        $outputArray['data']['product_images'] = array();
                        $i = 0;
                        if(isset($productDetails->productImages))
                        {
                            foreach ($productDetails->productImages as $key => $value)
                            {
                                if(!empty($value->image_name))
                                {
                                    //$imgOriginalPath = $this->PRODUCT_ORIGINAL_IMAGE_PATH.$value->image_name;


                                     if (Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgOriginalUrl = $s3url.Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name;
                                        }else{
                                            $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }

                                         $outputArray['data']['product_images'][$i] =  $imgOriginalUrl;

                                    // $outputArray['data']['product_images'][$i] = (Storage::disk(config('constant.DISK'))->exists(Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name) : url(Config::get('constant.DEFAULT_IMAGE'));
                                    $i++;
                                }
                            }
                        }
                        else
                        {
                            $outputArray['data']['product_images'][$i] = url(Config::get('constant.DEFAULT_IMAGE'));
                        }
                    }
                    else
                    {
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.norecordsfound');
                        $statusCode = 200;
                        $outputArray['data'] = array();
                    }
             }       
             else
            {
                $this->log->info('API Product Details no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    /**
     * Get Product Details
     */
    public function getProductDetails(Request $request)
    {
        $outputArray = [];
        $productId = (isset($request->product_id) && $request->product_id > 0) ? $request->product_id : 0;
        try
        {
            $productDetails = Product::find($productId);
            if($productDetails)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.product_details_fetched_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();

                $outputArray['data']['business_id'] = (isset($productDetails->productBusiness) && !empty($productDetails->productBusiness->id)) ? $productDetails->productBusiness->id : 0;
                $outputArray['data']['business_name'] = (isset($productDetails->productBusiness) && !empty($productDetails->productBusiness->name)) ? $productDetails->productBusiness->name : '';

                $outputArray['data']['name'] = $productDetails->name;
                $outputArray['data']['descriptions'] = (isset($productDetails->description) && !empty($productDetails->description)) ? $productDetails->description : '';
                $outputArray['data']['metatags'] = (isset($productDetails->metatags) && !empty($productDetails->metatags)) ? $productDetails->metatags : '';
                $outputArray['data']['cost'] = (isset($productDetails->cost) && !empty($productDetails->cost)) ? $productDetails->cost : '';
                $outputArray['data']['product_images'] = array();
                $i = 0;
                if(isset($productDetails->productImages))
                {
                    foreach ($productDetails->productImages as $key => $value)
                    {
                        if(!empty($value->image_name))
                        {
                            //$imgOriginalPath = $this->PRODUCT_ORIGINAL_IMAGE_PATH.$value->image_name;

                            if (Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name){
                                $s3url =   Config::get('constant.s3url');
                               $imgOriginalUrl = $s3url.Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name;
                          }else{
                              $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                          }


              // $outputArray['data']['product_images'][$i] = (Storage::disk(config('constant.DISK'))->exists(Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.PRODUCT_ORIGINAL_IMAGE_PATH').$value->image_name) : url(Config::get('constant.DEFAULT_IMAGE'));
              $i++;
                        }
                    }
                }
                else
                {
                    $outputArray['data']['product_images'][$i] = url(Config::get('constant.DEFAULT_IMAGE'));
                }
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    public function getPublicBusinessDetail(Request $request)
    {
        $headerData = $request->header('Platform');
        $businessId = $request->business_id;
        $businessSlug = (isset($request->business_slug) && !empty($request->business_slug)) ? $request->business_slug : '';
        $getBusinessDetails = Business::where('url_slug', $request->url_slug)->with(['user'])->first();

        
        $outputArray = [];
        try
        {
            if($getBusinessDetails)
            {
                $user = $getBusinessDetails->user;

                //Send push notification to Business User
                if($getBusinessDetails->user_id != $user->id) {
                    
                    if($user->gender != 2 || $user->gender == null) {
                        if(isset($getBusinessDetails->user))
                        {
                            $notificationData = [];
                            $notificationData['title'] = 'Business Visited';
                            if((isset($user->singlebusiness->name)) && $user->singlebusiness->name != '')
                            {
                                $notificationData['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone.' from '.$user->singlebusiness->name : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' from '.$user->singlebusiness->name;
                            }
                            else
                            {
                                $notificationData['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name;
                            }

                            $notificationData['type'] = '3';
                            $notificationData['business_id'] = $getBusinessDetails->id;
                            $notificationData['business_name'] = $getBusinessDetails->name;
                            $notificationData['phone'] = ($user->gender != 2) ? $user->phone : "";
                            $notificationData['country_code'] = $user->country_code;
                            $notificationData['user_business_id'] = (isset($user->singlebusiness->id))? $user->singlebusiness->id : '';
                            $notificationData['user_business_name'] = (isset($user->singlebusiness->name)) ? $user->singlebusiness->name : '';
                            Helpers::sendPushNotification($getBusinessDetails->user_id, $notificationData);

                            // list notification list
                            $notificationListArray = [];
                            $notificationListArray['user_id'] = $getBusinessDetails->user_id;
                            $notificationListArray['business_id'] = $getBusinessDetails->id;
                            $notificationListArray['title'] = 'Business Visited';
                            $notificationListArray['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name;
                            $notificationListArray['type'] = '3';
                            $notificationListArray['business_name'] = $getBusinessDetails->name;
                            $notificationListArray['user_name'] = $getBusinessDetails->user->name;
                            $notificationListArray['activity_user_id'] = $user->id;

                            NotificationList::create($notificationListArray);


                        }
                    }
                }


                $loginUserId = $user->id;
                if($loginUserId != $getBusinessDetails->user_id)
                {

                    $getBusinessDetails->visits = $getBusinessDetails->visits + 1;
                    $getBusinessDetails->save();
                }
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_detail_get_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();

                $outputArray['data']['user_id'] = (isset($getBusinessDetails->user) && !empty($getBusinessDetails->user) && !empty($getBusinessDetails->user->id)) ? $getBusinessDetails->user->id : '';
                $outputArray['data']['user_name'] = (isset($getBusinessDetails->user) && !empty($getBusinessDetails->user) && !empty($getBusinessDetails->user->name)) ? $getBusinessDetails->user->name : '';
                $outputArray['data']['suggested_categories'] = $getBusinessDetails->suggested_categories;
                $outputArray['data']['created_by_agent'] = (isset($getBusinessDetails->businessCreatedBy)) ? $getBusinessDetails->businessCreatedBy->agent_approved : 0;

                $outputArray['data']['category_hierarchy'] = array();

                if($getBusinessDetails->category_id && !empty($getBusinessDetails->category_id))
                {
                    $explodeCategories = explode(',', $getBusinessDetails->category_id);
					$categories = Helpers::getCategoryWithSubCategory($explodeCategories);
					$outputArray['data']['category_hierarchy'] = $categories;
                    
                }

                $parentCatArray = [];
                $outputArray['data']['parent_categories'] = array();
                if($getBusinessDetails->parent_category && !empty($getBusinessDetails->parent_category))
                {
                    $parentcategoryIdsArray = (explode(',', $getBusinessDetails->parent_category));
                    if ($parentcategoryIdsArray)
                    {

                        $mainArray = [];
                        foreach ($parentcategoryIdsArray as $pIdKey => $pIdValue)
                        {
                            $parentcategoryData = Category::find($pIdValue);
                            if (!empty($parentcategoryData))
                            {
                                $listArray = [];

                                $listArray['id'] = $parentcategoryData->id;
                                $listArray['name'] = $parentcategoryData->name;

                                 if ($parentcategoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$parentcategoryData->cat_logo){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$parentcategoryData->cat_logo;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }



                                $listArray['logo'] = $imgThumbUrl;
                                $parentCatArray[] =  $parentcategoryData->name;
                                $mainArray[] = $listArray;

                            }
                        }

                        $outputArray['data']['parent_categories'] = $mainArray;
                    }
                }

                $outputArray['data']['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                $outputArray['data']['categories_name_list'] = $outputArray['data']['parent_category_name'];
                $outputArray['data']['id'] = $getBusinessDetails->id;
                $outputArray['data']['name'] = $getBusinessDetails->name;
                $outputArray['data']['business_slug'] = (!empty($getBusinessDetails->business_slug)) ? $getBusinessDetails->business_slug : '';
                $outputArray['data']['website_color_theme'] = (!empty($getBusinessDetails->web_site_color_theme)) ? $getBusinessDetails->web_site_color_theme : '';
                $outputArray['data']['website_url_slug'] = (!empty($getBusinessDetails->url_slug)) ? $getBusinessDetails->url_slug : '';

                $businessLogoThumbImgPath = ((isset($getBusinessDetails->business_logo) && !empty($getBusinessDetails->business_logo)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$getBusinessDetails->business_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$getBusinessDetails->business_logo) : url(Config::get('constant.DEFAULT_IMAGE'));

                 $businessLogoOriginalImgPath = ((isset($getBusinessDetails->business_logo) && !empty($getBusinessDetails->business_logo)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$getBusinessDetails->business_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$getBusinessDetails->business_logo) : url(Config::get('constant.DEFAULT_IMAGE'));

                $outputArray['data']['business_logo'] = $businessLogoThumbImgPath;
                $outputArray['data']['business_logo_original'] = $businessLogoOriginalImgPath;

                $outputArray['data']['default_image'] = url(Config::get('constant.RYEC_DEFAULT_BANNER_IMAGE'));
                $outputArray['data']['business_images'] = array();

                if(isset($getBusinessDetails->businessImages))
                {
                    $g = 0;
                    foreach($getBusinessDetails->businessImages as $businessImgKey => $businessImgValue)
                    {
                        if(!empty($businessImgValue->image_name))
                        {
                            if ($businessImgValue->image_name != '' && Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name;
                                        }else{
                                            $imgThumbUrl = '';
                                        }

                            // $imgThumbUrl = (($businessImgValue->image_name != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name) : '';

                            if(!empty($imgThumbUrl))
                            {
                                $outputArray['data']['business_images'][$g]['id']= $businessImgValue->id;
                                $outputArray['data']['business_images'][$g]['image_name']= $imgThumbUrl;
                                $g++;
                            }
                        }
                    }
                }

                $outputArray['data']['full_address'] = $getBusinessDetails->address;
                $outputArray['data']['address'] = '';
                $address = [];
                $addressImplode = '';
                if($getBusinessDetails->street_address != ''){
                    $address[] = $getBusinessDetails->street_address;
                }
                if($getBusinessDetails->locality != '')
                {
                    $address[] = $getBusinessDetails->locality;
                }
                if($getBusinessDetails->city != '')
                {
                    $address[] = $getBusinessDetails->city;
                }
                if($getBusinessDetails->state != '')
                {
                    $address[] = $getBusinessDetails->state;
                }
                if(!empty($address))
                {
                    $addressImplode = implode(', ',$address);
                }
                $outputArray['data']['address'] = $addressImplode;
                if($getBusinessDetails->pincode != '' && $outputArray['data']['address'] != '')
                {
                    $outputArray['data']['address'] = $addressImplode.' - '.$getBusinessDetails->pincode;
                }

                $outputArray['data']['street_address'] = $getBusinessDetails->street_address;
                $outputArray['data']['locality'] = $getBusinessDetails->locality;
                $outputArray['data']['country'] = $getBusinessDetails->country;
                $outputArray['data']['state'] = $getBusinessDetails->state;
                $outputArray['data']['city'] = $getBusinessDetails->city;
                $outputArray['data']['taluka'] = $getBusinessDetails->taluka;
                $outputArray['data']['district'] = $getBusinessDetails->district;
                $outputArray['data']['pincode'] = $getBusinessDetails->pincode;
                $outputArray['data']['phone'] = $getBusinessDetails->phone;
                $outputArray['data']['country_code'] = $getBusinessDetails->country_code;
                $outputArray['data']['mobile'] = $getBusinessDetails->mobile;
                //$outputArray['data']['country'] = (isset($getBusinessDetails->business_address) && !empty($getBusinessDetails->business_address->country) ) ? $getBusinessDetails->business_address->country : '';
                $outputArray['data']['email'] = $getBusinessDetails->email_id;
                $outputArray['data']['latitude'] = (!empty($getBusinessDetails->latitude)) ? $getBusinessDetails->latitude : 0;
                $outputArray['data']['longitude'] = (!empty($getBusinessDetails->longitude)) ? $getBusinessDetails->longitude : 0;
                /**
                Developed By: Jaydeep Rajgor
                Date: 19/06/2019
                Reason: Some URL was without http and in frontend it was landing on the websites   landing page when user clicked on the website on business-detail page.
                **/
                if(!empty($getBusinessDetails->website_url))
                {
                    $website_url=$getBusinessDetails->website_url;
                    if(strpos($website_url,"https://")!==false || strpos($website_url,"http://")!==false)
                    {
                        $outputArray['data']['website'] = $getBusinessDetails->website_url;
                    }
                    else
                    {
                        $outputArray['data']['website'] = "http://".$getBusinessDetails->website_url;
                    }
                }
                else
                {
                    $outputArray['data']['website'] = '';
                }
                //$outputArray['data']['website'] = (!empty($getBusinessDetails->website_url)) ? $getBusinessDetails->website_url : '';

                $outputArray['data']['membership_type'] = $getBusinessDetails->membership_type;
                if($getBusinessDetails->membership_type == 2)
                {
                    $outputArray['data']['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                }
                elseif($getBusinessDetails->membership_type == 1)
                {
                    $outputArray['data']['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                }
                else
                {
                    $outputArray['data']['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                }

                $outputArray['data']['year_of_establishment'] = (!empty($getBusinessDetails->establishment_year)) ? (string)$getBusinessDetails->establishment_year : '';
                $outputArray['data']['descriptions'] = (!empty($getBusinessDetails->description)) ? $getBusinessDetails->description : '';
                $outputArray['data']['approved'] = $getBusinessDetails->approved;
                if(isset($getBusinessDetails->businessWorkingHours) && !empty($getBusinessDetails->businessWorkingHours))
                {
                    $getTiming = Helpers::getCurrentDataTiming($getBusinessDetails->businessWorkingHours);
                    $outputArray['data']['current_open_status'] = $getTiming['current_open_status'];
                    $outputArray['data']['timings'] = $getTiming['timings'];
                    $outputArray['data']['hoursOperation'] = Helpers::getBusinessWorkingDayHours($getBusinessDetails->businessWorkingHours);
                    $outputArray['data']['timezone'] = (isset($getBusinessDetails->businessWorkingHours->timezone) && !empty($getBusinessDetails->businessWorkingHours->timezone)) ? $getBusinessDetails->businessWorkingHours->timezone : '';
                }
                else
                {
                    /** 
                     * @date: 22nd Aug, 2018
                     * We need to display default time, if owner did not added timing. 
                     */
                    $timezone = 'Asia/Kolkata';
                    $getTiming = Helpers::getDefaultBusinessTiming($timezone);
                    $outputArray['data']['current_open_status'] = $getTiming['current_open_status'];
                    $outputArray['data']['timings'] = $getTiming['timings'];
                    $outputArray['data']['hoursOperation'] = Helpers::getDefaultBusinessWorkingDayHours();
                    $outputArray['data']['timezone'] = $timezone;

                    /**
                     * @date: 22nd Aug, 2018
                     * Following code is commented because we need to display default business timing Mon to Sat 9 AM to 6 PM & Sun closed.
                     */
                    // $outputArray['data']['current_open_status'] = trans('labels.closedtoday');
                    // $outputArray['data']['timings'] = '';
                    // $outputArray['data']['hoursOperation'] = [];
                    // $outputArray['data']['timezone'] = '';
                }
                // For  meta tags
                $outputArray['data']['metatags'] = (isset($getBusinessDetails->metatags) && !empty($getBusinessDetails->metatags)) ? $getBusinessDetails->metatags : '' ;
                // For Owners
                $outputArray['data']['owners'] = array();
                if(isset($getBusinessDetails->owners))
                {
                    $i = 0;
                    foreach ($getBusinessDetails->owners as $ownerKey => $ownerValue)
                    {
                        $outputArray['data']['owners'][$i]['id'] = $ownerValue->id;
                        $outputArray['data']['owners'][$i]['name'] = $ownerValue->full_name;

                        if($loginUserId == $getBusinessDetails->user_id)
                        {
                            $outputArray['data']['owners'][$i]['email'] = (!empty($ownerValue->email_id)) ? $ownerValue->email_id : '';
                            $outputArray['data']['owners'][$i]['country_code'] = (!empty($ownerValue->country_code)) ? $ownerValue->country_code : '';
                            $outputArray['data']['owners'][$i]['phone'] = (!empty($ownerValue->mobile)) ? $ownerValue->mobile : '';
                        }
                        else
                        {
                            $outputArray['data']['owners'][$i]['email'] = '';
                            $outputArray['data']['owners'][$i]['country_code'] = '';
                            $outputArray['data']['owners'][$i]['phone'] = '';
                            if($ownerValue->public_access == 1)
                            {
                                $outputArray['data']['owners'][$i]['email'] = (!empty($ownerValue->email_id)) ? $ownerValue->email_id : '';
                                $outputArray['data']['owners'][$i]['country_code'] = (!empty($ownerValue->country_code)) ? $ownerValue->country_code : '';
                                $outputArray['data']['owners'][$i]['phone'] = (!empty($ownerValue->mobile)) ? $ownerValue->mobile : '';
                            }
                        }

                        if ($ownerValue->photo != '' && Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$ownerValue->photo){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$ownerValue->photo;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }


                        $outputArray['data']['owners'][$i]['image_url'] = $imgThumbUrl;
                        $i++;
                    }
                }

                // For Social Profiles
                $outputArray['data']['social_profiles']['facebook_url'] = (!empty($getBusinessDetails->facebook_url)) ? $getBusinessDetails->facebook_url : '';
                $outputArray['data']['social_profiles']['twitter_url'] = (!empty($getBusinessDetails->twitter_url)) ? $getBusinessDetails->twitter_url : '';
                $outputArray['data']['social_profiles']['linkedin_url'] = (!empty($getBusinessDetails->linkedin_url)) ? $getBusinessDetails->linkedin_url : '';
                $outputArray['data']['social_profiles']['instagram_url'] = (!empty($getBusinessDetails->instagram_url)) ? $getBusinessDetails->instagram_url : '';

                // For Products
                $outputArray['data']['products'] = array();
                if(isset($getBusinessDetails->products))
                {
                    $j = 0;
                    foreach ($getBusinessDetails->products as $productKey => $productValue)
                    {
                        $outputArray['data']['products'][$j]['id'] = $productValue->id;
                        $outputArray['data']['products'][$j]['name'] = $productValue->name;
                        $imgThumbUrl = '';

                         if (isset($productValue->productImage) && !empty($productValue->productImage) && !empty($productValue->productImage->image_name) && Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }

                        // $imgThumbUrl = ((isset($productValue->productImage) && !empty($productValue->productImage) && !empty($productValue->productImage->image_name)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name) : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['products'][$j]['image_url'] = $imgThumbUrl;
                        $j++;
                    }
                }
                // For Services
                $outputArray['data']['services'] = array();
                if(isset($getBusinessDetails->services))
                {
                    $j = 0;
                    foreach ($getBusinessDetails->services as $serviceKey => $serviceValue)
                    {
                        $outputArray['data']['services'][$j]['id'] = $serviceValue->id;
                        $outputArray['data']['services'][$j]['name'] = $serviceValue->name;
                        $imgThumbUrl = '';

                         if ((isset($serviceValue->logo) && !empty($serviceValue->logo)) && Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }

                        // $imgThumbUrl = ((isset($serviceValue->logo) && !empty($serviceValue->logo)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['services'][$j]['image_url'] = $imgThumbUrl;
                        $j++;
                    }
                }

                //For Business Activities
                $outputArray['data']['business_activities'] = array();
                if(isset($getBusinessDetails->businessActivities))
                {
                    $k = 0;
                    foreach ($getBusinessDetails->businessActivities as $activityKey => $activityValue)
                    {
                        if(!empty($activityValue->activity_title))
                        {
                            $outputArray['data']['business_activities'][$k]['id'] = $activityValue->id;
                            $outputArray['data']['business_activities'][$k]['business_id'] = $activityValue->business_id;
                            $outputArray['data']['business_activities'][$k]['activity_title'] = $activityValue->activity_title;
                            $k++;
                        }
                    }
                }

                //For rating
                if(isset($getBusinessDetails->getBusinessRatings))
                {
                    $l = 0;
                    $outputArray['data']['rating']['avg_rating'] = round($getBusinessDetails->getBusinessRatings->avg('rating'), 1);

                    $userRating = $getBusinessDetails->getBusinessRatings->where('user_id', $loginUserId)->where('business_id', $businessId)->pluck('rating')->first();

                    $outputArray['data']['rating']['user_rating'] = (isset($userRating) && !empty($userRating)) ? intval($userRating) : '';
                    $outputArray['data']['rating']['start_5_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '5.0')->count();
                    $outputArray['data']['rating']['start_4_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '4.0')->count();
                    $outputArray['data']['rating']['start_3_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '3.0')->count();
                    $outputArray['data']['rating']['start_2_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '2.0')->count();
                    $outputArray['data']['rating']['start_1_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '1.0')->count();
                    $outputArray['data']['rating']['total'] = $getBusinessDetails->getBusinessRatings->count('rating');

                    $outputArray['data']['rating']['reviews'] = array();
                    $businessRatingDetails = $getBusinessDetails->getBusinessRatings()->orderBy('updated_at', 'DESC')->limit(Config::get('constant.BUSINESS_DETAILS_RATINGS_LIMIT'))->get();
                    foreach ($businessRatingDetails as $ratingKey => $ratingValue)
                    {
                        $outputArray['data']['rating']['reviews'][$l]['id'] = $ratingValue->id;
                        $outputArray['data']['rating']['reviews'][$l]['rating'] = $ratingValue->rating;
                        $outputArray['data']['rating']['reviews'][$l]['name'] = (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->name)) ? $ratingValue->getUsersData->name : '';

                        $outputArray['data']['rating']['reviews'][$l]['timestamp'] = (!empty($ratingValue->updated_at)) ? strtotime($ratingValue->updated_at)*1000 : '';
                        $outputArray['data']['rating']['reviews'][$l]['review'] = $ratingValue->comment;

                        $imgThumbUrl = '';
                         if ((isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic)) && Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic){
                                              $s3url =   Config::get('constant.s3url');
                                             $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic;
                                        }else{
                                            $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                        }


                        // $imgThumbUrl = ((isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['rating']['reviews'][$l]['image_url'] = $imgThumbUrl;
                        $outputArray['data']['rating']['reviews'][$l]['user_business_id'] = (isset($ratingValue->getUsersData->singlebusiness) && $ratingValue->getUsersData->singlebusiness->id != '')? (string)$ratingValue->getUsersData->singlebusiness->id : '';
                        $l++;
                    }
                }
                else
                {
                    $outputArray['data']['rating'] = new stdClass();
                }
            }
            else
            {
                $this->log->info('API getBusinessDetail no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->info('API getBusinessDetail no records found', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
       return response()->json($outputArray, $statusCode);
    }





    /**
     * Get Business Detail
     */
    public function getBusinessDetail(Request $request)
    {
         $loginUserId = 0;
         try {
            
            $user = JWTAuth::parseToken()->authenticate();
            //$this->log->info('Logged in user');
            $loginUserId = $user->id;
        } catch (TokenInvalidException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTAuthException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } 

        $headerData = $request->header('Platform');
        $businessId = $request->business_id; 

        $businessSlug = (isset($request->business_slug) && !empty($request->business_slug)) ? $request->business_slug : '';
        $time1 = intval(microtime(true)*1000);
        $outputArray = [];
        try
        {
            if($businessSlug)
            {
               $getBusinessDetails = Business::where('business_slug', $businessSlug)->first();
               $businessId = ($getBusinessDetails) ? $getBusinessDetails->id : null;
            }
            else
            {
                $getBusinessDetails = Business::find($businessId);
            }
            $time2 = intval(microtime(true)*1000);
            \Log::info("getBusinessDetail Query Execution time after get business:=== " . ($time2 - $time1).'ms');
            if($getBusinessDetails)
            { 
                

                //Send push notification to Business User
                if($loginUserId !=0) {
                if($getBusinessDetails->user_id != $user->id) {
                    
                    if($user->gender != 2 || $user->gender == null) {
                        if(isset($getBusinessDetails->user))
                        {
                            $notificationData = [];
                            $notificationData['title'] = 'Business Visited';
                            if((isset($user->singlebusiness->name)) && $user->singlebusiness->name != '')
                            {
                                $notificationData['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone.' from '.$user->singlebusiness->name : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' from '.$user->singlebusiness->name;
                            }
                            else
                            {
                                $notificationData['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name;
                            }

                            $notificationData['type'] = '3';
                            $notificationData['business_id'] = $getBusinessDetails->id;
                            $notificationData['business_name'] = $getBusinessDetails->name;
                            $notificationData['phone'] = ($user->gender != 2) ? $user->phone : "";
                            $notificationData['country_code'] = $user->country_code;
                            $notificationData['user_business_id'] = (isset($user->singlebusiness->id))? $user->singlebusiness->id : '';
                            $notificationData['user_business_name'] = (isset($user->singlebusiness->name)) ? $user->singlebusiness->name : '';
                            Helpers::sendPushNotification($getBusinessDetails->user_id, $notificationData);

                            // list notification list
                            $notificationListArray = [];
                            $notificationListArray['user_id'] = $getBusinessDetails->user_id;
                            $notificationListArray['business_id'] = $getBusinessDetails->id;
                            $notificationListArray['title'] = 'Business Visited';
                            $notificationListArray['message'] = ($user->gender != 2) ? 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name.' '.$user->phone : 'Dear '.$getBusinessDetails->user->name.',  Your business just got viewed by '.$user->name;
                            $notificationListArray['type'] = '3';
                            $notificationListArray['business_name'] = $getBusinessDetails->name;
                            $notificationListArray['user_name'] = $getBusinessDetails->user->name;
                            $notificationListArray['activity_user_id'] = $user->id;

                            NotificationList::create($notificationListArray);


                        }
                        $time3 = intval(microtime(true)*1000);
                        \Log::info("getBusinessDetail Query Execution time after create notification:=== " . ($time3 - $time2).'ms');
                    }
                }
            }
                
                //  Insert Record in User visit Activit Table by Dsu  
                if($loginUserId >0){
                    $userVisitActivity = new UserVisitActivity();
                    $userVisitActivity->user_id = $loginUserId;
                    $userVisitActivity->entity_id = $businessId;
                    $userVisitActivity->entity_type = (isset($getBusinessDetails->entityType)) ? $getBusinessDetails->entityType->name : 'Business';
                    if($request->latitude){ 
                        $userVisitActivity->latitude = $request->latitude;
                    }
                    if($request->longitude){ 
                        $userVisitActivity->longitude = $request->longitude;
                    } 
                    $userVisitActivity->ip_address = $request->ip(); 
                    $userVisitActivity->save();                       
                }
                
                $getBusinessDetails->visits = $getBusinessDetails->visits + 1;
                $getBusinessDetails->save();
                $time3 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after save visit count:=== " . ($time3 - $time2).'ms');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_detail_get_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();

                if(!$getBusinessDetails->is_normal_view) {
                    $outputArray['data'] = $this->getExtendedEntityDetail($getBusinessDetails);
                    return response()->json($outputArray, $statusCode);
                }
               

                $outputArray['data']['user_id'] = (isset($getBusinessDetails->user) && !empty($getBusinessDetails->user) && !empty($getBusinessDetails->user->id)) ? $getBusinessDetails->user->id : '';
                $outputArray['data']['user_name'] = (isset($getBusinessDetails->user) && !empty($getBusinessDetails->user) && !empty($getBusinessDetails->user->name)) ? $getBusinessDetails->user->name : '';
                $outputArray['data']['suggested_categories'] = $getBusinessDetails->suggested_categories;
                $outputArray['data']['created_by_agent'] = (isset($getBusinessDetails->businessCreatedBy)) ? $getBusinessDetails->businessCreatedBy->agent_approved : 0;
                $outputArray['data']['location_id'] = $getBusinessDetails->location_id;
                $outputArray['data']['category_hierarchy'] = array();
                if($getBusinessDetails->category_id && !empty($getBusinessDetails->category_id))
                {
                    $explodeCategories = explode(',', $getBusinessDetails->category_id);
                    $categories = Helpers::getCategoryWithSubCategory($explodeCategories);
					$outputArray['data']['category_hierarchy'] = $categories;
                }
                $time4 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get category:=== " . ($time4 - $time3).'ms');
                // $parentCatArray = [];
                // $outputArray['data']['parent_categories'] = array();
                // if($getBusinessDetails->parent_category && !empty($getBusinessDetails->parent_category))
                // {
                //     $parentcategoryIdsArray = (explode(',', $getBusinessDetails->parent_category));
                //     if ($parentcategoryIdsArray)
                //     {
                //         $mainArray = [];
                //         foreach ($parentcategoryIdsArray as $pIdKey => $pIdValue)
                //         {
                //             $parentcategoryData = Category::find($pIdValue);
                //             if (!empty($parentcategoryData))
                //             {
                //                 $listArray = [];

                //                 $listArray['id'] = $parentcategoryData->id;
                //                 $listArray['name'] = $parentcategoryData->name;


                //                 if ($parentcategoryData->cat_logo != '' &&  Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo){
                //                     $s3url =   Config::get('constant.s3url');
                //                     $imgOriginalUrl = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH') . $parentcategoryData->cat_logo;
                //                 }else{
                //                     $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                //                 }

                //                 $listArray['logo'] = $imgOriginalUrl;

                //     //   $listArray['logo'] = $imgThumbUrl;
                //        $parentCatArray[] =  $parentcategoryData->name;
                //                 $mainArray[] = $listArray;

                //             }
                //         }

                //         $outputArray['data']['parent_categories'] = $mainArray;
                //     }
                // }

                // $outputArray['data']['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                $parentCatArray = $this->getBusinessParentCategory($getBusinessDetails->parent_category);
                $outputArray['data']['parent_categories'] = $parentCatArray;
                $outputArray['data']['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';

                $time5 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get parent category:=== " . ($time5 - $time4).'ms');
                $outputArray['data']['categories_name_list'] = $outputArray['data']['parent_category_name'];
                $outputArray['data']['id'] = $getBusinessDetails->id;
                $outputArray['data']['name'] = $getBusinessDetails->name;
                $outputArray['data']['business_slug'] = (!empty($getBusinessDetails->business_slug)) ? $getBusinessDetails->business_slug : '';
                $outputArray['data']['entity_type'] = (isset($getBusinessDetails->entityType)) ? $getBusinessDetails->entityType->name : 'Business';
                $outputArray['data']['asset_type_id'] = $getBusinessDetails->asset_type_id;
                $outputArray['data']['website_color_theme'] = (!empty($getBusinessDetails->web_site_color_theme)) ? $getBusinessDetails->web_site_color_theme : '';
                $outputArray['data']['website_url_slug'] = (!empty($getBusinessDetails->url_slug)) ? $getBusinessDetails->url_slug : '';

                if (isset($getBusinessDetails->business_logo) && !empty($getBusinessDetails->business_logo) &&  Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$getBusinessDetails->business_logo){
                    $s3url =   Config::get('constant.s3url');
                   $businessLogoThumbImgPath = $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$getBusinessDetails->business_logo;
                }else{
                    $businessLogoThumbImgPath = url(Config::get('constant.DEFAULT_IMAGE'));
                }
                if (isset($getBusinessDetails->business_logo) && !empty($getBusinessDetails->business_logo) &&  Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$getBusinessDetails->business_logo){
                    $s3url =   Config::get('constant.s3url');
                   $businessLogoOrigImgPath = $s3url.Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$getBusinessDetails->business_logo;
                }else{
                    $businessLogoOrigImgPath = url(Config::get('constant.DEFAULT_IMAGE'));
                }

                $outputArray['data']['business_logo'] = $businessLogoThumbImgPath;
                $outputArray['data']['business_logo_original'] = $businessLogoOrigImgPath;

                $outputArray['data']['default_image'] = url(Config::get('constant.RYEC_DEFAULT_BANNER_IMAGE'));
                $outputArray['data']['business_images'] = array();

                if(isset($getBusinessDetails->businessImages))
                {
                    $g = 0;
                    foreach($getBusinessDetails->businessImages as $businessImgKey => $businessImgValue)
                    {
                        if(!empty($businessImgValue->image_name))
                        {


                            if ($businessImgValue->image_name != '' &&  Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name){
                                $s3url =   Config::get('constant.s3url');
                               $imgThumbUrl = $s3url.Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name;
                          }else{
                              $imgThumbUrl = '';
                          }

                            if(!empty($imgThumbUrl))
                            {
                                $outputArray['data']['business_images'][$g]['id']= $businessImgValue->id;
                                $outputArray['data']['business_images'][$g]['image_name']= $imgThumbUrl;
                                $g++;
                            }
                        }
                    }
                }

                $outputArray['data']['full_address'] = $getBusinessDetails->address;
                $outputArray['data']['address'] = '';
                $address = [];
                $addressImplode = '';
                if($getBusinessDetails->street_address != ''){
                    $address[] = $getBusinessDetails->street_address;
                }
                if($getBusinessDetails->locality != '')
                {
                    $address[] = $getBusinessDetails->locality;
                }
                if($getBusinessDetails->city != '')
                {
                    $address[] = $getBusinessDetails->city;
                }
                if($getBusinessDetails->state != '')
                {
                    $address[] = $getBusinessDetails->state;
                }
                if(!empty($address))
                {
                    $addressImplode = implode(', ',$address);
                }
                $outputArray['data']['address'] = $addressImplode;
                if($getBusinessDetails->pincode != '' && $outputArray['data']['address'] != '')
                {
                    $outputArray['data']['address'] = $addressImplode.' - '.$getBusinessDetails->pincode;
                }

                $outputArray['data']['street_address'] = $getBusinessDetails->street_address;
                $outputArray['data']['locality'] = $getBusinessDetails->locality;
                $outputArray['data']['country'] = $getBusinessDetails->country;
                $outputArray['data']['state'] = $getBusinessDetails->state;
                $outputArray['data']['city'] = $getBusinessDetails->city;
                $outputArray['data']['taluka'] = $getBusinessDetails->taluka;
                $outputArray['data']['district'] = $getBusinessDetails->district;
                $outputArray['data']['pincode'] = $getBusinessDetails->pincode;
                $outputArray['data']['phone'] = $getBusinessDetails->phone;
                $outputArray['data']['country_code'] = $getBusinessDetails->country_code;
                $outputArray['data']['mobile'] = $getBusinessDetails->mobile;
                //$outputArray['data']['country'] = (isset($getBusinessDetails->business_address) && !empty($getBusinessDetails->business_address->country) ) ? $getBusinessDetails->business_address->country : '';
                $outputArray['data']['email'] = $getBusinessDetails->email_id;
                $outputArray['data']['latitude'] = (!empty($getBusinessDetails->latitude)) ? $getBusinessDetails->latitude : 0;
                $outputArray['data']['longitude'] = (!empty($getBusinessDetails->longitude)) ? $getBusinessDetails->longitude : 0;
                /**
                Developed By: Jaydeep Rajgor
                Date: 19/06/2019
                Reason: Some URL was without http and in frontend it was landing on the websites   landing page when user clicked on the website on business-detail page.
                **/
                if(!empty($getBusinessDetails->website_url))
                {
                    $website_url=$getBusinessDetails->website_url;
                    if(strpos($website_url,"https://")!==false || strpos($website_url,"http://")!==false)
                    {
                        $outputArray['data']['website'] = $getBusinessDetails->website_url;
                    }
                    else
                    {
                        $outputArray['data']['website'] = "http://".$getBusinessDetails->website_url;
                    }
                }
                else
                {
                    $outputArray['data']['website'] = '';
                }
                //$outputArray['data']['website'] = (!empty($getBusinessDetails->website_url)) ? $getBusinessDetails->website_url : '';

                $outputArray['data']['membership_type'] = $getBusinessDetails->membership_type;
                \Log::info('member:'.$getBusinessDetails->membership_type);
                if($getBusinessDetails->membership_type == 2)
                {
                    $outputArray['data']['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                }
                elseif($getBusinessDetails->membership_type == 1)
                {
                    $outputArray['data']['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                }
                else
                {

                    if($getBusinessDetails->document_approval ==3){
                             $outputArray['data']['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($getBusinessDetails->document_approval ==2){

                            $outputArray['data']['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data']['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                   // $outputArray['data']['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
		    $outputArray['data']['basic_membership_message'] = config('constant.BASIC_MEMBERSHIP_MESSAGE');
                }

                $outputArray['data']['year_of_establishment'] = (!empty($getBusinessDetails->establishment_year)) ? (string)$getBusinessDetails->establishment_year : '';
                $outputArray['data']['descriptions'] = (!empty($getBusinessDetails->description)) ? $getBusinessDetails->description : '';
                $outputArray['data']['short_description'] = (!empty($getBusinessDetails->short_description)) ? $getBusinessDetails->short_description : '';
               // $outputArray['data']['custom_details'] = $getBusinessDetails->customDetails;
                $outputArray['data']['approved'] = $getBusinessDetails->approved;
                $outputArray['data']['verified'] = $getBusinessDetails->verified;

                // for online stores key
                $onlineStores = [];
                if(!empty($getBusinessDetails->online_store_url)) {
                    $onlineStores = $this->getEntityOnlineStores($getBusinessDetails->online_store_url);
                }
                $outputArray['data']['online_stores'] = $onlineStores;
                $time6 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get online stores:=== " . ($time6 - $time5).'ms');

                // for language drop downs
                $languages = trim(Helpers::isOnSettings('other_languages'));
                $othersLangs = [];
                if(!empty($languages)) {
                    $othersLangs = explode(',',$languages);
                }
                array_unshift($othersLangs,'english');
                $outputArray['data']['languages'] = $othersLangs;
                $time7 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get language array:=== " . ($time7 - $time6).'ms');
                if(isset($getBusinessDetails->businessWorkingHours) && !empty($getBusinessDetails->businessWorkingHours))
                {
                    $getTiming = Helpers::getCurrentDataTiming($getBusinessDetails->businessWorkingHours);
                    $outputArray['data']['current_open_status'] = $getTiming['current_open_status'];
                    $outputArray['data']['timings'] = $getTiming['timings'];
                    $outputArray['data']['hoursOperation'] = Helpers::getBusinessWorkingDayHours($getBusinessDetails->businessWorkingHours);
                    $outputArray['data']['timezone'] = (isset($getBusinessDetails->businessWorkingHours->timezone) && !empty($getBusinessDetails->businessWorkingHours->timezone)) ? $getBusinessDetails->businessWorkingHours->timezone : '';
                }
                else
                {
                    /** 
                     * @date: 22nd Aug, 2018
                     * We need to display default time, if owner did not added timing. 
                     */
                    $timezone = 'Asia/Kolkata';
                    $getTiming = Helpers::getDefaultBusinessTiming($timezone);
                    $outputArray['data']['current_open_status'] = $getTiming['current_open_status'];
                    $outputArray['data']['timings'] = $getTiming['timings'];
                    $outputArray['data']['hoursOperation'] = Helpers::getDefaultBusinessWorkingDayHours();
                    $outputArray['data']['timezone'] = $timezone;

                    /**
                     * @date: 22nd Aug, 2018
                     * Following code is commented because we need to display default business timing Mon to Sat 9 AM to 6 PM & Sun closed.
                     */
                    // $outputArray['data']['current_open_status'] = trans('labels.closedtoday');
                    // $outputArray['data']['timings'] = '';
                    // $outputArray['data']['hoursOperation'] = [];
                    // $outputArray['data']['timezone'] = '';
                }

                $time8 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get working hours:=== " . ($time8 - $time7).'ms');
                // For  meta tags
                $outputArray['data']['metatags'] = (isset($getBusinessDetails->metatags) && !empty($getBusinessDetails->metatags)) ? $getBusinessDetails->metatags : '' ;
                $outputArray['data']['seo_meta_tags'] = (isset($getBusinessDetails->seo_meta_tags) && !empty($getBusinessDetails->seo_meta_tags)) ? $getBusinessDetails->seo_meta_tags : '' ;
                $outputArray['data']['seo_meta_description'] = (isset($getBusinessDetails->seo_meta_description) && !empty($getBusinessDetails->seo_meta_description)) ? $getBusinessDetails->seo_meta_description : '' ;
                // For Owners
                $outputArray['data']['owners'] = array();
                if(isset($getBusinessDetails->owners))
                {
                    $i = 0;
                    foreach ($getBusinessDetails->owners as $ownerKey => $ownerValue)
                    {
                        $outputArray['data']['owners'][$i]['id'] = $ownerValue->id;
                        $outputArray['data']['owners'][$i]['name'] = $ownerValue->full_name;

                        if($loginUserId == $getBusinessDetails->user_id)
                        {
                            $outputArray['data']['owners'][$i]['email'] = (!empty($ownerValue->email_id)) ? $ownerValue->email_id : '';
                            $outputArray['data']['owners'][$i]['country_code'] = (!empty($ownerValue->country_code)) ? $ownerValue->country_code : '';
                            $outputArray['data']['owners'][$i]['phone'] = (!empty($ownerValue->mobile)) ? $ownerValue->mobile : '';
                        }
                        else
                        {
                            $outputArray['data']['owners'][$i]['email'] = '';
                            $outputArray['data']['owners'][$i]['country_code'] = '';
                            $outputArray['data']['owners'][$i]['phone'] = '';
                            if($ownerValue->public_access == 1)
                            {
                                $outputArray['data']['owners'][$i]['email'] = (!empty($ownerValue->email_id)) ? $ownerValue->email_id : '';
                                $outputArray['data']['owners'][$i]['country_code'] = (!empty($ownerValue->country_code)) ? $ownerValue->country_code : '';
                                $outputArray['data']['owners'][$i]['phone'] = (!empty($ownerValue->mobile)) ? $ownerValue->mobile : '';
                            }
                        }
     
                        if ($ownerValue->photo != '' &&  Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$ownerValue->photo){
                            $s3url =   Config::get('constant.s3url');
                           $imgOriginalUrl = $s3url.Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$ownerValue->photo;
                        }else{
                            $imgOriginalUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                        }
                        $outputArray['data']['owners'][$i]['image_url'] =    $imgOriginalUrl;   
                        $i++;
                    }
                }

                $time9 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get owners:=== " . ($time9 - $time8).'ms');
                // For Social Profiles
                $outputArray['data']['social_profiles']['facebook_url'] = (!empty($getBusinessDetails->facebook_url)) ? $getBusinessDetails->facebook_url : '';
                $outputArray['data']['social_profiles']['twitter_url'] = (!empty($getBusinessDetails->twitter_url)) ? $getBusinessDetails->twitter_url : '';
                $outputArray['data']['social_profiles']['linkedin_url'] = (!empty($getBusinessDetails->linkedin_url)) ? $getBusinessDetails->linkedin_url : '';
                $outputArray['data']['social_profiles']['instagram_url'] = (!empty($getBusinessDetails->instagram_url)) ? $getBusinessDetails->instagram_url : '';
                $outputArray['data']['social_profiles']['online_store_url'] = (!empty($getBusinessDetails->online_store_url)) ? $getBusinessDetails->online_store_url : '';

                // For Products
                $outputArray['data']['products'] = array();
                if(isset($getBusinessDetails->products))
                {
                    $j = 0;
                    foreach ($getBusinessDetails->products as $productKey => $productValue)
                    {
                        $outputArray['data']['products'][$j]['id'] = $productValue->id;
                        $outputArray['data']['products'][$j]['name'] = $productValue->name;
                        $imgThumbUrl = '';


                        if (isset($productValue->productImage) && !empty($productValue->productImage) && !empty($productValue->productImage->image_name) &&  Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name){
                            $s3url =   Config::get('constant.s3url');
                           $imgThumbUrl = $s3url.Config::get('constant.PRODUCT_THUMBNAIL_IMAGE_PATH').$productValue->productImage->image_name;
                      }else{
                          $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                      }                       
                       $outputArray['data']['products'][$j]['image_url'] = $imgThumbUrl;
                        $j++;
                    }
                }

                $time10 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get products:=== " . ($time10 - $time9).'ms');
                // For Products
                $outputArray['data']['document'] = array();
                if(isset($getBusinessDetails->BusinessDoc) && count((array)$getBusinessDetails->BusinessDoc) > 0)
                {
                    $j = 0;
                    foreach ($getBusinessDetails->BusinessDoc as $BusinessDocKey => $BusinessDocValue)
                    {
                        $outputArray['data']['document'][$j]['id'] = $BusinessDocValue->id;
                        $outputArray['data']['document'][$j]['name'] = $BusinessDocValue->doc_name;
                        $front_image = '';

                        $front_image = ((isset($BusinessDocValue->front_image) && !empty($BusinessDocValue->front_image))) ? url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$BusinessDocValue->front_image) : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['document'][$j]['front_image'] = $front_image;

                        $back_image = '';

                        $back_image = ((isset($BusinessDocValue->back_image) && !empty($BusinessDocValue->back_image))) ? url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$BusinessDocValue->back_image) : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['document'][$j]['back_image'] = $back_image;

                        $j++;
                    }
                }
                $time11 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get document:=== " . ($time11 - $time10).'ms');
                // For Services
                $outputArray['data']['services'] = array();
                if(isset($getBusinessDetails->services))
                {
                    $j = 0;
                    foreach ($getBusinessDetails->services as $serviceKey => $serviceValue)
                    {
                        $outputArray['data']['services'][$j]['id'] = $serviceValue->id;
                        $outputArray['data']['services'][$j]['name'] = $serviceValue->name;
                        $imgThumbUrl = '';


                         if (isset($serviceValue->logo) && !empty($serviceValue->logo) &&  Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo){
                            $s3url =   Config::get('constant.s3url');
                           $imgThumbUrl = $s3url.Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo;
                      }else{
                          $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                      }

      // $imgThumbUrl = ((isset($serviceValue->logo) && !empty($serviceValue->logo)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.SERVICE_THUMBNAIL_IMAGE_PATH').$serviceValue->logo) : url(Config::get('constant.DEFAULT_IMAGE'));
      $outputArray['data']['services'][$j]['image_url'] = $imgThumbUrl;
                        $j++;
                    }
                }

                $time12 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get services:=== " . ($time12 - $time11).'ms');
                //For Business Activities
                $outputArray['data']['business_activities'] = array();
                if(isset($getBusinessDetails->businessActivities) && count($getBusinessDetails->businessActivities) > 0)
                {
                    $k = 0;
                    foreach ($getBusinessDetails->businessActivities as $activityKey => $activityValue)
                    {
                        if(!empty($activityValue->activity_title))
                        {
                            $outputArray['data']['business_activities'][$k]['id'] = $activityValue->id;
                            $outputArray['data']['business_activities'][$k]['business_id'] = $activityValue->business_id;
                            $outputArray['data']['business_activities'][$k]['activity_title'] = $activityValue->activity_title;
                            $k++;
                        }
                    }
                }
                $time13 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get activities:=== " . ($time13 - $time12).'ms');
                //For rating
                if(isset($getBusinessDetails->getBusinessRatings) && count($getBusinessDetails->getBusinessRatings) > 0)
                {
                    $l = 0;
                    $outputArray['data']['rating']['avg_rating'] = round($getBusinessDetails->getBusinessRatings->avg('rating'), 1);

                    $userRating = $getBusinessDetails->getBusinessRatings->where('user_id', $loginUserId)->where('business_id', $businessId)->pluck('rating')->first();

                    $outputArray['data']['rating']['user_rating'] = (isset($userRating) && !empty($userRating)) ? intval($userRating) : '';
                    $outputArray['data']['rating']['start_5_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '5.0')->count();
                    $outputArray['data']['rating']['start_4_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '4.0')->count();
                    $outputArray['data']['rating']['start_3_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '3.0')->count();
                    $outputArray['data']['rating']['start_2_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '2.0')->count();
                    $outputArray['data']['rating']['start_1_rating'] = $getBusinessDetails->getBusinessRatings->where('rating', '=', '1.0')->count();
                    $outputArray['data']['rating']['total'] = $getBusinessDetails->getBusinessRatings->count('rating');

                    $outputArray['data']['rating']['reviews'] = array();
                    $businessRatingDetails = $getBusinessDetails->getBusinessRatings()->orderBy('updated_at', 'DESC')->with('getUsersData')->limit(Config::get('constant.BUSINESS_DETAILS_RATINGS_LIMIT'))->get();
                    foreach ($businessRatingDetails as $ratingKey => $ratingValue)
                    {
                        $outputArray['data']['rating']['reviews'][$l]['id'] = $ratingValue->id;
                        $outputArray['data']['rating']['reviews'][$l]['rating'] = $ratingValue->rating;
                        $outputArray['data']['rating']['reviews'][$l]['name'] = (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->name)) ? $ratingValue->getUsersData->name : '';

                        $outputArray['data']['rating']['reviews'][$l]['timestamp'] = (!empty($ratingValue->updated_at)) ? strtotime($ratingValue->updated_at)*1000 : '';
                        $outputArray['data']['rating']['reviews'][$l]['review'] = $ratingValue->comment;

                        $imgThumbUrl = '';


                        if (isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic) &&  Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic){
                            $s3url =   Config::get('constant.s3url');
                           $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic;
                      }else{
                          $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                      }


      // $imgThumbUrl = ((isset($ratingValue->getUsersData) && !empty($ratingValue->getUsersData->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$ratingValue->getUsersData->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
      $outputArray['data']['rating']['reviews'][$l]['image_url'] = $imgThumbUrl;
                        $outputArray['data']['rating']['reviews'][$l]['user_business_id'] = (isset($ratingValue->getUsersData->singlebusiness) && $ratingValue->getUsersData->singlebusiness->id != '')? (string)$ratingValue->getUsersData->singlebusiness->id : '';
                        $l++;
                    }
                }
                else
                {
                    $outputArray['data']['rating'] = new stdClass();
                }
                $time14 = intval(microtime(true)*1000);
                \Log::info("getBusinessDetail Query Execution time after get rating:=== " . ($time14 - $time13).'ms');
                \Log::info("getBusinessDetail Query Execution total time:=== " . ($time14 - $time1).'ms');
            }
            else
            {
                $this->log->info('API getBusinessDetail no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->info('API getBusinessDetail no records found', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
       return response()->json($outputArray, $statusCode);
    }



    public function getBusinessDoc(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $businessId = $request->business_id;
        $outputArray = array();
            try
            {
                  $business_doc = DB::table('business_doc')->where('business_id',$businessId)->where('deleted_at',null)->get();
                 $j = 0;
                  foreach ($business_doc as $key => $busi_doc) {
                    $outputArray['data'][$j]['id'] = $busi_doc->id;
                    $outputArray['data'][$j]['doc_name'] = $busi_doc->doc_name;
                    
                    $outputArray['data'][$j]['front_image'] = (($busi_doc->front_image != '') ) ? url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$busi_doc->front_image) : url(Config::get('constant.DEFAULT_IMAGE'));
                  
                    $outputArray['data'][$j]['back_image'] = (($busi_doc->back_image != '') ) ? url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$busi_doc->back_image) : url(Config::get('constant.DEFAULT_IMAGE'));
                   $j++;
                       
                  }
                  $outputArray['status'] = 1;
                $outputArray['message'] = 'Business document list';
                    $statusCode = 200;
            }
            catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    /**
     * Get Populuar Businesses
     */
    public function getPopularBusinesses(Request $request)
    {
        // $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        $outputArray = [];
        try
        {

            $filters = [];
            $filters['limit'] = (isset($request->limit) && !empty($request->limit)) ? $request->limit : 10;
            //$filters['approved'] = 1;
            $businessesCount = Business::orderBy('visits', 'DESC')->count();
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if(isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                elseif(isset($request->limit) && !empty($request->limit) && isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                }
                if(isset($request->sortBy) && !empty($request->sortBy))
                {
                    if($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                    elseif($request->sortBy == 'AtoZ')
                    {
                        $filters['sortBy'] = 'AtoZ';
                    }
                    elseif($request->sortBy == 'ZtoA')
                    {
                        $filters['sortBy'] = 'ZtoA';
                    }
                    elseif($request->sortBy == 'nearMe' && isset($request->radius) && !empty ($request->radius) && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = $request->sortBy;
                        $filters['radius'] = $request->radius;
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    }
                    elseif($request->sortBy == 'relevance' && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = 'relevance';
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    }
                }
            }
            if(isset($request->sortBy) && !empty($request->sortBy) && $request->sortBy == 'ratings')
            {
                $filters['sortBy'] = 'ratings';
                $recentlyAddedBusiness = $this->objBusiness->getBusinessesByRating($filters);
            }
            else
            {
                if(!isset($request->sortBy) && empty($request->sortBy))
                {
                    $filters['sortBy'] = 'promoted';
                }
                $recentlyAddedBusiness = $this->objBusiness->getAll($filters);

            }

            if($recentlyAddedBusiness && count($recentlyAddedBusiness) > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.popular_business_fetched_successfully');
                $statusCode = 200;
                $outputArray['businessesTotalCount'] = (isset($businessesCount) && $businessesCount > 0) ? $businessesCount : 0;

                $outputArray['data'] = array();
                $i = 0;
                foreach ($recentlyAddedBusiness as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['user_name'] = (isset($value->user) && !empty($value->user) && !empty($value->user->name)) ? $value->user->name : '';

                    $outputArray['data'][$i]['owners'] = '';

                    if($value->owners && count($value->owners) > 0)
                    {
                        $owners = [];
                        foreach($value->owners as $owner)
                        {
                            $owners[] = $owner->full_name;
                        }
                        if(!empty($owners))
                        {
                            $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                        }
                    }

                    $outputArray['data'][$i]['categories'] = array();
                    if(!empty($value->category_id) && $value->category_id != '')
                    {
                        $categoryIdsArray = (explode(',', $value->category_id));
                        if(count($categoryIdsArray) > 0)
                        {
                            $j = 0;
                            foreach($categoryIdsArray as $cIdKey => $cIdValue)
                            {
                                $categoryData = Category::find($cIdValue);
                                if(!empty($categoryData))
                                {
                                    $outputArray['data'][$i]['categories'][$j]['category_id'] = $categoryData->id;
                                    $outputArray['data'][$i]['categories'][$j]['category_name'] = $categoryData->name;
                                    $outputArray['data'][$i]['categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                                    // if(!empty($categoryData->cat_logo))
                                    // {
                                    //    // $catLogoPath = $this->categoryLogoThumbImagePath.$categoryData->cat_logo;

                                    // }

                                    if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo){
                                        $s3url =   Config::get('constant.s3url');
                                      $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                                  }else{
                                        $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                  }        
                                 // $catLogoPath = (($categoryData->cat_logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                                 $outputArray['data'][$i]['categories'][$j]['category_logo'] = $catLogoPath;

                                    $outputArray['data'][$i]['categories'][$j]['parent_category_id'] = $categoryData->parent;
                                    $j++;
                                }
                            }
                        }
                    }
                    $parentCatArray = [];
                    $outputArray['data'][$i]['parent_categories'] = array();
                    if(!empty($value->parent_category) && $value->parent_category != '')
                    {
                        $categoryIdsArray = (explode(',', $value->parent_category));
                        if(count($categoryIdsArray) > 0)
                        {
                            $j = 0;
                            foreach($categoryIdsArray as $cIdKey => $cIdValue)
                            {
                                $categoryData = Category::find($cIdValue);
                                if(!empty($categoryData))
                                {
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_id'] = $categoryData->id;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_name'] = $categoryData->name;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                                    // if(!empty($categoryData->cat_logo))
                                    // {
                                    //    // $catLogoPath = $this->categoryLogoThumbImagePath.$categoryData->cat_logo;

                                    // }

                                    if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo){
                                        $s3url =   Config::get('constant.s3url');
                                      $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                                  }else{
                                        $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                  }    
                                 // $catLogoPath = (($categoryData->cat_logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                                 $outputArray['data'][$i]['parent_categories'][$j]['category_logo'] = $catLogoPath;
                                    $parentCatArray[] =  $categoryData->name;

                                    $j++;

                                }
                            }
                        }
                    }

                    $outputArray['data'][$i]['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                    $outputArray['data'][$i]['categories_name_list'] = $outputArray['data'][$i]['parent_category_name'];
                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : '';
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : '';
                    $outputArray['data'][$i]['address'] = (!empty($value->address)) ? $value->address : '';
                    $outputArray['data'][$i]['street_address'] = $value->street_address;
                    $outputArray['data'][$i]['locality'] = $value->locality;
                    $outputArray['data'][$i]['country'] = $value->country;
                    $outputArray['data'][$i]['state'] = $value->state;
                    $outputArray['data'][$i]['city'] = $value->city;
                    $outputArray['data'][$i]['taluka'] = $value->taluka;
                    $outputArray['data'][$i]['district'] = $value->district;
                    $outputArray['data'][$i]['pincode'] = $value->pincode;
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';
                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {
                         if($value->document_approval ==3){
                             $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($value->document_approval ==2){

                          $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                        //$outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                    }
                    // if(isset($value->businessImagesById) && !empty($value->businessImagesById->image_name))
                    // {

                    //     // $img_thumb_path = $this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->businessImagesById->image_name;
                    //     // $img_thumb_url = (!empty($img_thumb_path) && file_exists($img_thumb_path)) ? $img_thumb_path : '';
                    //     // $outputArray['data'][$i]['business_image'] = (!empty($img_thumb_url)) ? url($img_thumb_url) : url($this->catgoryTempImage);

                    // }
                    // else
                    // {
                    //     $outputArray['data'][$i]['business_image'] = url($this->catgoryTempImage);
                    // }
                    $s3url =   Config::get('constant.s3url');

                    $imgThumbUrl = ((isset($value->businessImagesById) && !empty($value->businessImagesById->image_name)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name : url(Config::get('constant.DEFAULT_IMAGE'));
                    $outputArray['data'][$i]['business_image'] = $imgThumbUrl;

                    $businessLogoThumbImgPath = ((isset($value->business_logo) && !empty($value->business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo : $imgThumbUrl;
                    $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;
                    $i++;

                }
            }
            else
            {
                $this->log->info('API getPopularBusinesses no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (\Exception $e) {
            $this->log->error('API something went wrong while getPopularBusinesses', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }
       /**
     * Get recently added business listing
     */

    public function getPremiumBusinesses(Request $request)
    {
        // $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        $outputArray = [];
        try
        {
            $filters = [];
            $filters['approved'] = 1;
            $filters['membership_type'] = 1;
            $filters['membership_premium_lifetime_type'] = 1;
            $filters['sortBy'] = 'relevance';
            //$businessesCount = Business::where('approved', $filters['approved'])->where('membership_type','<>',0)->orderBy('id', 'DESC')->count();
            //$businessesCount = Business::where('membership_type','<>',0)->orderBy('id', 'DESC')->count();


            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {

                if(isset($request->limit) && !empty($request->limit) && isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                }
                elseif(isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {

                if(isset($request->limit) && !empty($request->limit) && isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                }
                elseif(isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
               
            }

            if(isset($request->sortBy) && !empty($request->sortBy))
            {
                $filters['sortBy'] = $request->sortBy;

                if(isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                {
                    $filters['latitude'] = $request->latitude;
                    $filters['longitude'] = $request->longitude;
                }
                    
                if (isset($request->radius) && !empty ($request->radius))
                {
                        
                    //$filters['radius'] = $request->radius;
                }
            } 
            //Log::info("Limit:". $filters['take']." Skip:".$filters['skip']);

            //Log::info("Sort By:".$filters['sortBy']);
            $time1 = intval(microtime(true)*1000);

            $businessesCount = $this->objBusiness->getAllCountForFrontAndMobileApp($filters);
            $time2 = intval(microtime(true)*1000);
            \Log::info("getPremiumBusinesses Query Execution time after count premium business:=== " . ($time2 - $time1).'ms');
            //Log::info("Count:".$businessesCount);

            $premiumBusiness = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            $time3 = intval(microtime(true)*1000);
            \Log::info("getPremiumBusinesses Query Execution time after get premium business:=== " . ($time3 - $time2).'ms');

            //Log::info("Count:".count($premiumBusiness));
            
            // return $premiumBusiness;
            if($premiumBusiness && count($premiumBusiness) > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.premium_business_fetched_successfully');
                $statusCode = 200;

                $outputArray['businessesTotalCount'] = $businessesCount;

                $outputArray['data'] = array();
                $i = 0;
                foreach ($premiumBusiness as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['user_name'] = (isset($value->user) && !empty($value->user) && !empty($value->user->name)) ? $value->user->name : '';

                    $outputArray['data'][$i]['owners'] = '';

                    if($value->owners && count($value->owners) > 0)
                    {
                        $owners = [];
                        foreach($value->owners as $owner)
                        {
                            $owners[] = $owner->full_name;
                        }
                        if(!empty($owners))
                        {
                            $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                        }
                    }

                    $parentCatArray = $this->getBusinessParentCategory($value->parent_category);
                    $outputArray['data'][$i]['parent_categories'] = $parentCatArray;
                    $outputArray['data'][$i]['categories_name_list'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';

                    // $outputArray['data'][$i]['categories'] = array();
                    // if(!empty($value->category_id) && $value->category_id != '')
                    // {
                    //     $categoryIdsArray = (explode(',', $value->category_id));
                    //     if(count((array)$categoryIdsArray) > 0)
                    //     {
                    //         $j = 0;
                    //         foreach($categoryIdsArray as $cIdKey => $cIdValue)
                    //         {
                    //             $categoryData = Category::find($cIdValue);
                    //             if(!empty($categoryData))
                    //             {
                    //                 $outputArray['data'][$i]['categories'][$j]['category_id'] = $categoryData->id;
                    //                 $outputArray['data'][$i]['categories'][$j]['category_name'] = $categoryData->name;
                    //                 $outputArray['data'][$i]['categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                    //                 // if(!empty($categoryData->cat_logo))
                    //                 // {
                    //                 //     //$catLogoPath = $this->categoryLogoThumbImagePath.$categoryData->cat_logo;

                    //                 // }

                    //                 $catLogoPath = (($categoryData->cat_logo != '') ) ? url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                    //                 $outputArray['data'][$i]['categories'][$j]['category_logo'] = $catLogoPath;

                    //                 $outputArray['data'][$i]['categories'][$j]['parent_category_id'] = $categoryData->parent;
                    //                 $j++;
                    //             }
                    //         }
                    //     }
                    // }
                    // $parentCatArray = [];
                    // $outputArray['data'][$i]['parent_categories'] = array();
                    // if(!empty($value->parent_category) && $value->parent_category != '')
                    // {
                    //     $categoryIdsArray = (explode(',', $value->parent_category));
                    //     if(count($categoryIdsArray) > 0)
                    //     {
                    //         $j = 0;
                    //         foreach($categoryIdsArray as $cIdKey => $cIdValue)
                    //         {
                    //             $categoryData = Category::find($cIdValue);
                    //             if(!empty($categoryData))
                    //             {
                    //                 $outputArray['data'][$i]['parent_categories'][$j]['category_id'] = $categoryData->id;
                    //                 $outputArray['data'][$i]['parent_categories'][$j]['category_name'] = $categoryData->name;
                    //                 $outputArray['data'][$i]['parent_categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                                    

                    //                 if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo){
                    //                     $s3url =   Config::get('constant.s3url');
                    //                    $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                    //               }else{
                    //                   $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                    //               }

                    //               $outputArray['data'][$i]['parent_categories'][$j]['category_logo'] = $catLogoPath;
                    //                 $parentCatArray[] =  $categoryData->name;
                    //                 $j++;
                    //             }
                    //         }
                    //     }
                    // }
                    // $outputArray['data'][$i]['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                    // $outputArray['data'][$i]['categories_name_list'] = $outputArray['data'][$i]['parent_category_name'];
                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : '';
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : '';
                    $outputArray['data'][$i]['address'] = (!empty($value->address)) ? $value->address : '';
                    
                    $outputArray['data'][$i]['street_address'] = (!empty($value->street_address)) ? $value->street_address : '';
                    $outputArray['data'][$i]['locality'] = (!empty($value->locality)) ? $value->locality : '';
                    $outputArray['data'][$i]['country'] = (!empty($value->country)) ? $value->country : '';
                    $outputArray['data'][$i]['state'] = (!empty($value->state)) ? $value->state : '';
                    $outputArray['data'][$i]['city'] = (!empty($value->city)) ? $value->city : '';
                    $outputArray['data'][$i]['taluka'] = (!empty($value->taluka)) ? $value->taluka : '';
                    $outputArray['data'][$i]['district'] = (!empty($value->district)) ? $value->district : '';
                    $outputArray['data'][$i]['pincode'] = (!empty($value->pincode)) ? $value->pincode : '';
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';
                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    $outputArray['data'][$i]['is_normal_view'] = $value->is_normal_view;
                    $outputArray['data'][$i]['entity_type'] = $value->entity_type;
                     if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {
                        if($value->document_approval ==3){
                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                        }elseif($value->document_approval ==2){

                          $outputArray['data'][$i]['membership_type_icon']= url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                        }else{

                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                        }
                    }
                    
                    $s3url =   Config::get('constant.s3url');
                   if(isset($value->businessImagesById) && !empty($value->businessImagesById->image_name) && Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name) {

                              if (Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name){
                                          
                                         $imgThumbUrl = $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name;
                                    }else{
                                        $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }

                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl;
                        
                    } elseif(!empty($outputArray['data'][$i]['parent_categories'])) {
                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl = $outputArray['data'][$i]['parent_categories'][0]['category_logo'];
                    } else {
                        $outputArray['data'][$i]['business_image'] = $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                    }

                    if (isset($value->business_logo) && !empty($value->business_logo)) {
                        $outputArray['data'][$i]['logo_thumbnail'] = $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo;
                    } else {
                        $outputArray['data'][$i]['logo_thumbnail'] = $imgThumbUrl;
                    }

                    $i++;
                }
                $time4 = intval(microtime(true)*1000);
                \Log::info("getPremiumBusinesses Query Execution time after get category of premium business:=== " . ($time4 - $time3).'ms');
            }
            else
            {
                $this->log->info('API getPremiumBusinesses no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getPremiumBusinesses');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        $time5 = intval(microtime(true)*1000);
        \Log::info("getPremiumBusinesses Query Execution time after total response time:=== " . ($time5 - $time1).'ms');
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Get Promoted Businesses
     */
    public function getRecentlyAddedBusinessListing(Request $request)
    {
        //$user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        $limit = $request->input('limit',10);
        $time1 = intval(microtime(true)*1000);
        \Log::info("Start RecentlyAddedBusinessListing Query Execution :-");
        $outputArray = [];
        try
        {
            $filters = [];
            $filters['approved'] = 1;
            //$businessesCount = Business::orderBy('id', 'DESC')->count();
            $filters['recent'] = true;

            $entityTypeId = $this->getEntityIdByName($request->entity_type);
            if(!empty($entityTypeId)) {
                $filters['asset_type_id'] = $entityTypeId;
            }

            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if(!empty($limit) && isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $limit;
                    $filters['skip'] = $offset;
                }
                elseif(!empty($limit))
                {
                    $filters['take'] = $limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECENTLY_ADDED_BUSINESS_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {

                if(!empty($limit) && isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = $limit;
                    $filters['skip'] = $offset;
                } else {
                    $filters['take'] = $limit;
                    $filters['skip'] = 0;
                }
            }
            else
            {
                if(isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                else
                {
                    $filters['take'] = $limit;
                    $filters['skip'] = 0;
                }
            }
            if(isset($request->sortBy) && !empty($request->sortBy))
            {
                if($request->sortBy == 'popular')
                {
                    $filters['sortBy'] = 'popular';
                }
                elseif($request->sortBy == 'AtoZ')
                {
                    $filters['sortBy'] = 'AtoZ';
                }
                elseif($request->sortBy == 'ZtoA')
                {
                    $filters['sortBy'] = 'ZtoA';
                }
                elseif($request->sortBy == 'nearMe') {

                    $filters['sortBy'] = 'nearMe';

                    if(isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {

                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    } 
                }
                elseif($request->sortyBy=='relevance') {

                    $filters['sortBy'] = 'relevance';
                    if(isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {

                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    } 
                }
                elseif($request->sortBy == 'ratings') 
                {
                    $filters['sortBy'] = 'ratings';
                }
                else 
                {
                    $filters['sortBy'] = 'relevance';
                }
                
            } else {

                 $filters['sortBy'] = 'relevance';
            }

            if(!empty($request->searchText)) {
                $filters['searchText'] = $request->searchText;
            }
            
            $time2 = intval(microtime(true)*1000);
            \Log::info("RecentlyAddedBusinessListing Query Execution time after add filters array:=== " . ($time2 - $time1).'ms');


            $businessesCount = $this->objBusiness->getAllCountForFrontAndMobileApp($filters);
            $time3 = intval(microtime(true)*1000);
            \Log::info("RecentlyAddedBusinessListing Query Execution time after count business:=== " . ($time3 - $time2).'ms');
            //Log::info("Count:".$businessesCount);

            //$businessesCount = 200;

            $recentlyAddedBusiness = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            $time4 = intval(microtime(true)*1000);
            \Log::info("RecentlyAddedBusinessListing Query Execution time after get bussiness:=== " . ($time4 - $time3).'ms');
            

            if($recentlyAddedBusiness && count($recentlyAddedBusiness) > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.recently_added_business_fetched_successfully');
                $statusCode = 200;

                $outputArray['businessesTotalCount'] = (isset($businessesCount) && $businessesCount > 0) ? $businessesCount : 0;

                $outputArray['data'] = array();
                $i = 0;
                foreach ($recentlyAddedBusiness as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['user_name'] = (isset($value->user) && !empty($value->user) && !empty($value->user->name)) ? $value->user->name : '';

                    $outputArray['data'][$i]['owners'] = '';

                    if($value->owners && count($value->owners) > 0)
                    {
                        $owners = [];
                        foreach($value->owners as $owner)
                        {
                            $owners[] = $owner->full_name;
                        }
                        if(!empty($owners))
                        {
                            $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                        }
                    }
                    
                    $time5 = intval(microtime(true)*1000);
                    $parentCatArray = $this->getBusinessParentCategory($value->parent_category);
                    $outputArray['data'][$i]['parent_categories'] = $parentCatArray;
                    
                    $time6 = intval(microtime(true)*1000);
                    \Log::info("RecentlyAddedBusinessListing Query Execution time after get parent category detail:=== " . ($time6 - $time5).'ms');
                    $outputArray['data'][$i]['categories_name_list'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';
                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : '';
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : '';
                    $outputArray['data'][$i]['address'] = (!empty($value->address)) ? $value->address : '';
                   
                    $outputArray['data'][$i]['street_address'] = (!empty($value->street_address)) ? $value->street_address : '';
                    $outputArray['data'][$i]['locality'] = (!empty($value->locality)) ? $value->locality : '';
                    $outputArray['data'][$i]['country'] = (!empty($value->country)) ? $value->country : '';
                    $outputArray['data'][$i]['state'] = (!empty($value->state)) ? $value->state : '';
                    $outputArray['data'][$i]['city'] = (!empty($value->city)) ? $value->city : '';
                    $outputArray['data'][$i]['taluka'] = (!empty($value->taluka)) ? $value->taluka : '';
                    $outputArray['data'][$i]['district'] = (!empty($value->district)) ? $value->district : '';
                    $outputArray['data'][$i]['pincode'] = (!empty($value->pincode)) ? $value->pincode : '';
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';
                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    $outputArray['data'][$i]['is_normal_view'] = $value->is_normal_view;
                    $outputArray['data'][$i]['entity_type'] = $value->entity_type;
                    if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {

                        if($value->document_approval ==3){
                             $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($value->document_approval ==2){

                          $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                        //$outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                    }
                    
                    $s3url =   Config::get('constant.s3url');

                    // $imgThumbUrl = ((isset($value->businessImagesById) && !empty($value->businessImagesById) && !empty($value->businessImagesById->image_name))) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name : (empty($outputArray['data'][$i]['parent_categories'][0]['category_logo']) ? url(Config::get('constant.DEFAULT_IMAGE')) : $outputArray['data'][$i]['parent_categories'][0]['category_logo']);
                    // $outputArray['data'][$i]['business_image'] = $imgThumbUrl;
                    
                    $businessLogoThumbImgPath = ((isset($value->business_logo) && !empty($value->business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo : (empty($outputArray['data'][$i]['parent_categories'][0]['category_logo']) ? url(Config::get('constant.DEFAULT_IMAGE')) : $outputArray['data'][$i]['parent_categories'][0]['category_logo']);
                    $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;

                    $i++;
                }

                $time7 = intval(microtime(true)*1000);
                \Log::info("RecentlyAddedBusinessListing Query Execution time after response array:=== " . ($time7 - $time4).'ms');
            }
            else
            {
                $this->log->info('API getRecentlyAddedBusinessListing no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getRecentlyAddedBusinessListing', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        $time8 = intval(microtime(true)*1000);
        \Log::info("RecentlyAddedBusinessListing Query Execution total time:=== " . ($time8 - $time1).'ms');
        return response()->json($outputArray, $statusCode);
    }

     /**
     * Get recently added business listing
     */
    public function getPromotedBusinesses(Request $request)
    {
        //$user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
        $outputArray = [];
        try
        {
            $filters = [];
            //$filters['approved'] = 1;
            $filters['promoted'] = 1;
            $businessesCount = Business::where('promoted', $filters['promoted'])->orderBy('id', 'DESC')->count();
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if(isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                elseif(isset($request->limit) && !empty($request->limit) && isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                }

                if(isset($request->sortBy) && !empty($request->sortBy))
                {
                    if($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                    elseif($request->sortBy == 'AtoZ')
                    {
                        $filters['sortBy'] = 'AtoZ';
                    }
                    elseif($request->sortBy == 'ZtoA')
                    {
                        $filters['sortBy'] = 'ZtoA';
                    }
                    elseif($request->sortBy == 'nearMe' && isset($request->radius) && !empty ($request->radius) && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = $request->sortBy;
                        $filters['radius'] = $request->radius;
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;

                        //$businessNearMeData = Business::where('approved', $filters['approved']);
                        $businessNearMeData = Business::whereNull('deleted_at');
                        $businessNearMeData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                        $businessNearMeData->having('distance', '<', $filters['radius']);

                        $businessNearMeData->orderBy('distance', 'DESC');
                        $businessesCount = $businessNearMeData->get();
                    }
                    elseif($request->sortBy == 'relevance' && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = $request->sortBy;
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;

                        //$businessNearMeData = Business::where('approved', $filters['approved']);
                        $businessNearMeData = Business::whereNull('deleted_at');
                        $businessNearMeData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                        $businessNearMeData->orderBy('membership_type','DESC')->orderBy('distance', 'DESC');
                        $businessesCount = $businessNearMeData->get();
                    }
                }
            }
            if(isset($request->sortBy) && !empty($request->sortBy) && $request->sortBy == 'ratings')
            {
                $filters['sortBy'] = 'ratings';
                $promotedBusiness = $this->objBusiness->getBusinessesByRating($filters);
            }
            else
            {
                $promotedBusiness = $this->objBusiness->getAll($filters);
            }
            if($promotedBusiness && count($promotedBusiness) > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.promoted_business_fetched_successfully');
                $statusCode = 200;

                //$searchfilters['approved'] = 1;
                $searchfilters['promoted'] = 1;
                $totalPromotedBusinesses =  $this->objBusiness->getAll($searchfilters);

                $outputArray['businessesTotalCount'] = (isset($totalPromotedBusinesses) && count($totalPromotedBusinesses) > 0) ? count($totalPromotedBusinesses) : 0;

                $outputArray['data'] = array();
                $i = 0;
                foreach ($promotedBusiness as $key => $value)
                {
                    $outputArray['data'][$i]['id'] = $value->id;
                    $outputArray['data'][$i]['name'] = $value->name;
                    $outputArray['data'][$i]['business_slug'] = (isset($value->business_slug) && !empty($value->business_slug)) ? $value->business_slug : '';
                    $outputArray['data'][$i]['user_name'] = (isset($value->user) && !empty($value->user) && !empty($value->user->name)) ? $value->user->name : '';

                    $outputArray['data'][$i]['owners'] = '';

                    if($value->owners && count($value->owners) > 0)
                    {
                        $owners = [];
                        foreach($value->owners as $owner)
                        {
                            $owners[] = $owner->full_name;
                        }
                        if(!empty($owners))
                        {
                            $outputArray['data'][$i]['owners'] = implode(', ',$owners);
                        }
                    }

                    $outputArray['data'][$i]['categories'] = array();
                    if(!empty($value->category_id) && $value->category_id != '')
                    {
                        $categoryIdsArray = (explode(',', $value->category_id));
                        if(count($categoryIdsArray) > 0)
                        {
                            $j = 0;
                            foreach($categoryIdsArray as $cIdKey => $cIdValue)
                            {
                                $categoryData = Category::find($cIdValue);
                                if(!empty($categoryData))
                                {
                                    $outputArray['data'][$i]['categories'][$j]['category_id'] = $categoryData->id;
                                    $outputArray['data'][$i]['categories'][$j]['category_name'] = $categoryData->name;
                                    $outputArray['data'][$i]['categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';
                                    // if(!empty($categoryData->cat_logo))
                                    // {
                                    //     //$catLogoPath = $this->categoryLogoThumbImagePath.$categoryData->cat_logo;

                                    // }
                                    if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo){
                                           $s3url =   Config::get('constant.s3url');
                                         $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                                     }else{
                                           $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                     }  


                                    // $catLogoPath = (($categoryData->cat_logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                                    $outputArray['data'][$i]['categories'][$j]['category_logo'] = $catLogoPath;

                                    $outputArray['data'][$i]['categories'][$j]['parent_category_id'] = $categoryData->parent;
                                    $j++;
                                }
                            }
                        }
                    }
                    $parentCatArray = [];
                    $outputArray['data'][$i]['parent_categories'] = array();
                    if(!empty($value->parent_category) && $value->parent_category != '')
                    {
                        $categoryIdsArray = (explode(',', $value->parent_category));
                        if(count($categoryIdsArray) > 0)
                        {
                            $j = 0;
                            foreach($categoryIdsArray as $cIdKey => $cIdValue)
                            {
                                $categoryData = Category::find($cIdValue);
                                if(!empty($categoryData))
                                {
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_id'] = $categoryData->id;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_name'] = $categoryData->name;
                                    $outputArray['data'][$i]['parent_categories'][$j]['category_slug'] = (!empty($categoryData->category_slug)) ? $categoryData->category_slug : '';

                                    if ($categoryData->cat_logo != '' && Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo){
                                        $s3url =   Config::get('constant.s3url');
                                      $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo;
                                  }else{
                                        $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                  }  

                                 // $catLogoPath = (($categoryData->cat_logo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->cat_logo) : url(Config::get('constant.DEFAULT_IMAGE'));
                                 $outputArray['data'][$i]['parent_categories'][$j]['category_logo'] = $catLogoPath;
                                    $parentCatArray[] =  $categoryData->name;
                                    $j++;
                                }
                            }
                        }
                    }
                    $outputArray['data'][$i]['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray) : '';
                    $outputArray['data'][$i]['categories_name_list'] = $outputArray['data'][$i]['parent_category_name'];
                    $outputArray['data'][$i]['descriptions'] = $value->description;
                    $outputArray['data'][$i]['phone'] = $value->phone;
                    $outputArray['data'][$i]['country_code'] = $value->country_code;
                    $outputArray['data'][$i]['mobile'] = $value->mobile;
                    $outputArray['data'][$i]['latitude'] = (!empty($value->latitude)) ? $value->latitude : '';
                    $outputArray['data'][$i]['longitude'] = (!empty($value->longitude)) ? $value->longitude : '';
                    $outputArray['data'][$i]['address'] = (!empty($value->address)) ? $value->address : '';
                    $outputArray['data'][$i]['street_address'] = (!empty($value->street_address)) ? $value->street_address : '';
                    $outputArray['data'][$i]['locality'] = (!empty($value->locality)) ? $value->locality : '';
                    $outputArray['data'][$i]['country'] = (!empty($value->country)) ? $value->country : '';
                    $outputArray['data'][$i]['state'] = (!empty($value->state)) ? $value->state : '';
                    $outputArray['data'][$i]['city'] = (!empty($value->city)) ? $value->city : '';
                    $outputArray['data'][$i]['taluka'] = (!empty($value->taluka)) ? $value->taluka : '';
                    $outputArray['data'][$i]['district'] = (!empty($value->district)) ? $value->district : '';
                    $outputArray['data'][$i]['pincode'] = (!empty($value->pincode)) ? $value->pincode : '';
                    $outputArray['data'][$i]['email_id'] = (!empty($value->email_id)) ? $value->email_id : '';
                    $outputArray['data'][$i]['website_url'] = (!empty($value->website_url)) ? $value->website_url : '';
                    $outputArray['data'][$i]['membership_type'] = $value->membership_type;
                    if($value->membership_type == 2)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($value->membership_type == 1)
                    {
                        $outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {
                         if($value->document_approval ==3){
                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($value->document_approval ==2){

                          $outputArray['data'][$i]['membership_type_icon']= url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data'][$i]['membership_type_icon']  = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                        //$outputArray['data'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                    }
                    // if(isset($value->businessImagesById) && !empty($value->businessImagesById->image_name))
                    // {
                    //     // $img_thumb_path = $this->BUSINESS_THUMBNAIL_IMAGE_PATH.$value->businessImagesById->image_name;
                    //     // $img_thumb_url = (!empty($img_thumb_path) && file_exists($img_thumb_path)) ? $img_thumb_path : '';
                    //     // $outputArray['data'][$i]['business_image'] = (!empty($img_thumb_url)) ? url($img_thumb_url) : url($this->catgoryTempImage);
                    // }
                    // else
                    // {
                    //     $outputArray['data'][$i]['business_image'] = url($this->catgoryTempImage);
                    // }

                    $s3url =   Config::get('constant.s3url');

                    $imgThumbUrl = ((isset($value->businessImagesById) && !empty($value->businessImagesById->image_name)) &&  $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->businessImagesById->image_name : url(Config::get('constant.DEFAULT_IMAGE'));
                    $outputArray['data'][$i]['business_image'] = $imgThumbUrl;

                    $businessLogoThumbImgPath = ((isset($value->business_logo) && !empty($value->business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$value->business_logo : $imgThumbUrl;
                    $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;

                    $i++;
                }
            }
            else
            {
                $this->log->info('API getPromotedBusinesses no records found', array('login_user_id' => $user->id));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getPromotedBusinesses', array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }



    /**
     * Get Business Listing By Category Id
     */
    public function getBusinessListingByCatId(Request $request)
    {
        $headerData = (!empty($request->header('Platform'))) ? $request->header('Platform') : '';
        $time1 = intval(microtime(true)*1000);
        info("Start Business Listing Query Execution time:=== ");
        try
        {
            if($request->header('Platform') == 'mobile'){
                $categoryId = (isset($request->category_id) && !empty($request->category_id)) ? $request->category_id : 0;
            }
          
            $categorySlug = (isset($request->category_slug) && !empty($request->category_slug)) ? $request->category_slug : '';
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
            $filters = [];
            
            if(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {
                $categoryDetails = Category::find($categoryId);
                $offset = Helpers::getOffset($pageNo);
                $filters['approved'] = 1;
                $filters['offset'] = $offset;
                $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                if(isset($request->sortBy) && !empty($request->sortBy))
                {
                    if($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                    elseif($request->sortBy == 'ratings')
                    {
                        $filters['sortBy'] = 'ratings';
                    }
                    elseif($request->sortBy == 'AtoZ')
                    {
                        $filters['sortBy'] = 'AtoZ';
                    }
                    elseif($request->sortBy == 'ZtoA')
                    {
                        $filters['sortBy'] = 'ZtoA';
                    }
                    elseif (isset($request->sortBy) && $request->sortBy == 'relevance' && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = 'relevance';
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    }
                    elseif (isset($request->sortBy) && $request->sortBy == 'nearMe' && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = 'nearMe';
                        if(isset($request->radius) && $request->radius != ''){
                            $filters['radius'] = $request->radius;
                        }
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    }
                    else
                    {
                        $filters['sortBy'] = 'relevance';
                    }
                }
                else
                {
                    $filters['sortBy'] = 'relevance';
                }
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                $categoryDetails = Category::where('category_slug', $categorySlug)->first();
                $offset = Helpers::getWebOffset($pageNo);
                $filters['approved'] = 1;
                $filters['offset'] = $offset;
                $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                $categoryId = $categoryDetails ? $categoryDetails->id : 0;
                if(isset($request->sortBy) && !empty($request->sortBy))
                {
                    if($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                    elseif($request->sortBy == 'ratings')
                    {
                        $filters['sortBy'] = 'ratings';
                    }
                    elseif($request->sortBy == 'AtoZ')
                    {
                        $filters['sortBy'] = 'AtoZ';
                    }
                    elseif($request->sortBy == 'ZtoA')
                    {
                        $filters['sortBy'] = 'ZtoA';
                    }
                    elseif (isset($request->sortBy) && $request->sortBy == 'nearMe' && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                    {
                        $filters['sortBy'] = 'nearMe';
                        if(isset($request->radius) && $request->radius != ''){
                            $filters['radius'] = $request->radius;
                        }
                        $filters['latitude'] = $request->latitude;
                        $filters['longitude'] = $request->longitude;
                    }
                    else
                    {
                        $filters['sortBy'] = 'relevance';
                    }
                }
                else
                {
                    $filters['sortBy'] = 'relevance';
                }
            }
            else
            {
                $categoryDetails = Category::find($categoryId);
                $offset = Helpers::getOffset($pageNo);
                $filters['approved'] = 1;
                $filters['offset'] = $offset;
                $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                if(isset($request->sortBy) && !empty($request->sortBy))
                {
                    if($request->sortBy == 'popular')
                    {
                        $filters['sortBy'] = 'popular';
                    }
                    elseif($request->sortBy == 'ratings')
                    {
                        $filters['sortBy'] = 'ratings';
                    }
                    else
                    {
                        $filters['sortBy'] = 'relevance';
                    }
                }
                else
                {
                    $filters['sortBy'] = 'relevance';
                }
            }
            $time2 = intval(microtime(true)*1000);
            info("Business Listing Query Execution time 1:" . ($time2 - $time1).'ms');

            $filters['searchText'] = $request->searchText;
            $filters['city'] = $request->location;

            $categoryIds = $request->input('categoryIds',[]);
            if(empty($categoryIds)) {
                //$getAllSubCategoriesByParent = Helpers::getCategorySubHierarchy($categoryId);
                $getAllSubCategoriesByParent = [];
            } else {
                $getAllSubCategoriesByParent = $categoryIds;
            }

            $time3 = intval(microtime(true)*1000);
            info("Business Listing Query Execution time 1-2:" . ($time3 - $time2).'ms');

            if(isset($request->all) && $request->all == 1)
            {


                $whereStr = "FIND_IN_SET(" . $categoryId . ", parent_category)";
                $whereArr = [];
               // $whereArr[] = "FIND_IN_SET(".$categoryId.", category_id)";

                if(!empty($getAllSubCategoriesByParent))
                {
                    foreach ($getAllSubCategoriesByParent as $id) {
                        $whereArr[]  = "FIND_IN_SET(".$id.", category_id)";
                    }
                }

                if (!empty($whereArr))
                {
                    $whereStr .= ' AND '. implode(' OR ', $whereArr);
                }

                $businessesCount = Business::whereRaw($whereStr)->count();

                $time4 = intval(microtime(true)*1000);
                info("Business Listing Query Execution time 2:" . ($time4 - $time3).'ms');

                $businessListing = $this->objBusiness->getBusinessListingByCategoryId($categoryId,$filters,$getAllSubCategoriesByParent);

                $time5 = intval(microtime(true)*1000);
                info("Business Listing Query Execution time 3:" . ($time5 - $time4).'ms');
            }
            else
            {
                $businessesCount = Business::where('approved', 1)->whereRaw("FIND_IN_SET(".$categoryId.",parent_category)")->count();
                $time4 = intval(microtime(true)*1000);
                info("Business Listing Query Execution time 2:" . ($time4 - $time3).'ms');

                $businessListing = $this->objBusiness->getBusinessListingByCategoryId($categoryId,$filters,[]);
                $time5 = intval(microtime(true)*1000);
                info("Business Listing Query Execution time 3:" . ($time5 - $time4).'ms');

            }


            if($categoryDetails)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.business_fetched_successfully');
                $statusCode = 200;
                $outputArray['data'] = array();
                $outputArray['data']['category_id'] = $categoryDetails->id;
                $outputArray['data']['category_name'] = $categoryDetails->name;
                $outputArray['data']['category_slug'] = (!empty($categoryDetails->category_slug)) ? $categoryDetails->category_slug : '';
                // if(!empty($categoryDetails->cat_logo))
                // {
                //     //$catLogoPath = $this->categoryLogoThumbImagePath.$categoryDetails->cat_logo;

                // }

                 if ($categoryDetails->cat_logo != '' &&  Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryDetails->cat_logo){
                      $s3url =   Config::get('constant.s3url');
                     $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryDetails->cat_logo;
                }else{
                    $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                }
                $outputArray['data']['category_logo'] = $catLogoPath;

                $outputArray['data']['businesses'] = array();
                if($businessListing->isNotEmpty())
                {
                    $outputArray['businessesTotalCount'] = $businessesCount;

                    $perPageCnt = $pageNo * $filters['take'];
                    if($businessesCount > $perPageCnt)
                    {
                        $outputArray['loadMore'] = 1;
                    } else {
                        $outputArray['loadMore'] = 0;
                    }

                    $i = 0;
                    foreach ($businessListing as $businessValue)
                    {
                        $time6 = intval(microtime(true)*1000);
                        $outputArray['data']['businesses'][$i]['user_id'] = (isset($businessValue->user) && !empty($businessValue->user->id)) ? $businessValue->user->id : '';
                        $outputArray['data']['businesses'][$i]['user_name'] = (isset($businessValue->user) && !empty($businessValue->user->name)) ? $businessValue->user->name : '';

                        $time7 = intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 5:" . ($time7 - $time6).'ms');

                        $outputArray['data']['businesses'][$i]['owners'] = '';

                        if($businessValue->owners && count($businessValue->owners) > 0)
                        {
                            $owners = [];
                            foreach($businessValue->owners as $owner)
                            {
                                $owners[] = $owner->full_name;
                            }
                            if(!empty($owners))
                            {
                                $outputArray['data']['businesses'][$i]['owners'] = implode(', ',$owners);
                            }
                        }
                        $time8 = intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 6:" . ($time8 - $time7).'ms');                        

                        $outputArray['data']['businesses'][$i]['id'] = $businessValue->id;
                        $outputArray['data']['businesses'][$i]['name'] = $businessValue->name;
                        $outputArray['data']['businesses'][$i]['business_slug'] = (isset($businessValue->business_slug) && !empty($businessValue->business_slug)) ? $businessValue->business_slug : '';
                        $outputArray['data']['businesses'][$i]['email_id'] = $businessValue->email_id;
                        $outputArray['data']['businesses'][$i]['phone'] = $businessValue->phone;
                        $outputArray['data']['businesses'][$i]['country_code'] = $businessValue->country_code;
                        $outputArray['data']['businesses'][$i]['mobile'] = $businessValue->mobile;
                        $outputArray['data']['businesses'][$i]['descriptions'] = $businessValue->description;
                        

                        $outputArray['data']['businesses'][$i]['address'] = $businessValue->address;


                         if($businessValue->document_approval ==3){
                        $outputArray['data']['businesses'][$i]['document_approval'] = url(Config::get('constant.VERIFIED_IMAGE'));
                         }elseif($businessValue->document_approval ==2){
                          $outputArray['data']['businesses'][$i]['document_approval'] =  url(Config::get('constant.NON_VERIFIED_IMAGE2'));
                         }elseif($businessValue->document_approval ==1){

                           $outputArray['data']['businesses'][$i]['document_approval'] = url(Config::get('constant.NON_VERIFIED_IMAGE'));
                         }

                       // $outputArray['data']['businesses'][$i]['document_approval'] = (($businessValue->document_approval))? url(Config::get('constant.VERIFIED_IMAGE')) :  url(Config::get('constant.NON_VERIFIED_IMAGE'));;
                        $outputArray['data']['businesses'][$i]['street_address'] = $businessValue->street_address;
                        $outputArray['data']['businesses'][$i]['locality'] = $businessValue->locality;
                        $outputArray['data']['businesses'][$i]['country'] = $businessValue->country;
                        $outputArray['data']['businesses'][$i]['state'] = $businessValue->state;
                        $outputArray['data']['businesses'][$i]['city'] = $businessValue->city;
                        $outputArray['data']['businesses'][$i]['taluka'] = $businessValue->taluka;
                        $outputArray['data']['businesses'][$i]['district'] = $businessValue->district;
                        $outputArray['data']['businesses'][$i]['pincode'] = $businessValue->pincode;

                        $outputArray['data']['businesses'][$i]['is_normal_view'] = $businessValue->is_normal_view;
                        $outputArray['data']['businesses'][$i]['entity_type'] = $businessValue->entity_type;

                        $outputArray['data']['businesses'][$i]['latitude'] = (!empty($businessValue->latitude)) ? $businessValue->latitude : 0;
                        $outputArray['data']['businesses'][$i]['longitude'] = (!empty($businessValue->longitude)) ? $businessValue->longitude : 0;
                        $outputArray['data']['businesses'][$i]['website_url'] = $businessValue->website_url;
                        $outputArray['data']['businesses'][$i]['membership_type'] = $businessValue->membership_type;
                         if($businessValue->membership_type == 2)
                        {
                            $outputArray['data']['businesses'][$i]['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                        }
                        elseif($businessValue->membership_type == 1)
                        {
                            $outputArray['data']['businesses'][$i]['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                        }
                        else
                        {

                        if($businessValue->document_approval ==3){
                           $outputArray['data']['businesses'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($businessValue->document_approval ==2){

                          $outputArray['data']['businesses'][$i]['membership_type_icon']= url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $outputArray['data']['businesses'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }

                           // $outputArray['data']['businesses'][$i]['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE'));
                        }

                        $time10 = intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 7:" . ($time10 - $time8).'ms');

                        $outputArray['data']['businesses'][$i]['categories'] = $this->getBusinessParentCategory($businessValue->category_id);
                        $time11= intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 8:" . ($time11- $time10).'ms');
                        $parentCatArray = $this->getBusinessParentCategory($businessValue->parent_category);
                        $outputArray['data']['businesses'][$i]['parent_categories'] = $parentCatArray;
                        $outputArray['data']['businesses'][$i]['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';
                        $outputArray['data']['businesses'][$i]['categories_name_list'] = $outputArray['data']['businesses'][$i]['parent_category_name'];

                       
                        $time13= intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 10:" . ($time13- $time11).'ms');
                        // $imgThumbUrl = ((isset($businessValue->businessImagesById) && !empty($businessValue->businessImagesById) && !empty($businessValue->businessImagesById->image_name)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessValue->businessImagesById->image_name) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessValue->businessImagesById->image_name) : !empty($outputArray['data'][$i]['categories']) ? $outputArray['data'][$i]['categories'][0]['category_logo'] : url(Config::get('constant.DEFAULT_IMAGE'));
                        $s3url =   Config::get('constant.s3url');
                        // Log::info("s3 URL".$s3url);
                        $imgThumbUrl = ((isset($businessValue->businessImagesById) && !empty($businessValue->businessImagesById) && !empty($businessValue->businessImagesById->image_name))) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessValue->businessImagesById->image_name : url(Config::get('constant.DEFAULT_IMAGE'));
                        $outputArray['data']['businesses'][$i]['business_image'] = $imgThumbUrl;

                        $businessLogoThumbImgPath = ((isset($businessValue->business_logo) && !empty($businessValue->business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessValue->business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessValue->business_logo : $imgThumbUrl;
                        $outputArray['data']['businesses'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;
						$outputArray['data']['businesses'][$i]['verified'] = $businessValue->verified;
                        $time14= intval(microtime(true)*1000);
                        info("Business Listing Query Execution time 11:" . ($time14- $time13).'ms');
                        $i++;
                    }
                }
            }
            else
            {
                $this->log->info('API getBusinessListingByCatId no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $outputArray['loadMore'] = 0;
                $statusCode = 200;
                $outputArray['data'] = new \stdClass();
            }
            $time14= intval(microtime(true)*1000);
            info("Business Listing Query Execution total time:" . ($time14- $time1).'ms');

            return response()->json($outputArray, $statusCode);
        } catch (\Exception $e) {
            $this->log->error('API something went wrong while getBusinessListingByCatId');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Add Business
     */
    public function addBusiness(Request $request)
    {
        $userId = Auth::id();
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|min:6|max:13',
                'name' => 'required',
                'address' => 'required',
                'country_code' => 'required'
            ]);
            if ($validator->fails())
            {
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $requestData['user_id'] = $userId;
                $businessName = trim($requestData['name']);
                //$businessSlug = (!empty($businessName) &&  $businessName != '') ? Helpers::getSlug($businessName) : NULL;
                //$requestData['business_slug'] = $businessSlug;
                $businessSlug = SlugService::createSlug(Business::class, 'business_slug', $businessName);
                $requestData['business_slug'] = (isset($businessSlug) && !empty($businessSlug)) ? $businessSlug : NULL;
                $requestData['address'] =  $requestData['address'];
                $requestData['street_address'] =  isset($requestData['street_address']) ? $requestData['street_address']:'';
                $requestData['country'] =  isset($requestData['country']) ? $requestData['country']:'';
                $requestData['state'] =  isset($requestData['state']) ? $requestData['state']:'';
                $requestData['city'] =  isset($requestData['city']) ? $requestData['city']:'';
                $requestData['taluka'] =  isset($requestData['taluka']) ? $requestData['taluka']:'';
                $requestData['district'] =  isset($requestData['district']) ? $requestData['district']:'';
                $requestData['pincode'] =  isset($requestData['pincode']) ? $requestData['pincode']:'';



                $categoryDetail = Category::where('name','General')->first();
                if($categoryDetail)
                {
                    $requestData['category_id'] = $categoryDetail->id;
                    $requestData['parent_category'] = $categoryDetail->id;
                    $requestData['category_hierarchy'] = $categoryDetail->id;
                }

                $checkExist = $this->objBusiness->where('user_id',$userId)->first();

                if($checkExist)
                {
                    $this->log->error('API something went wrong while add business', array('login_user_id' => $userId));
                    $responseData['status'] = 0;
                    $responseData['message'] = 'Business already exist';
                    $statusCode = 200;
                    return response()->json($responseData, $statusCode);
                }
                else
                {
                    $response = $this->objBusiness->insertUpdate($requestData);
                }



                if((isset($requestData['latitude']) && $requestData['latitude'] != '') && (isset($requestData['longitude']) && $requestData['longitude'] != ''))
                {
                    $business_address_attributes = Helpers::getAddressAttributes($requestData['latitude'], $requestData['longitude']);
                    $business_address_attributes['business_id'] = $response->id;
                    $businessObject = Business::find($response->id);
                    if (!$businessObject->business_address) {
                        $businessObject->business_address()->create($business_address_attributes);
                    } else {
                        $businessObject->business_address()->update($business_address_attributes);
                    }
                }


                Cache::forget('membersForApproval');
                Cache::forget('businessesData');
                if($response)
                {
                    $userData = User::find($userId);
                    if($userData)
                    {
                        // if($userData->country_code == '+91')
                        // {
                        //     $smsResponse = Helpers::sendMessage($userData->phone, "Dear ".$userData->name.", Welcome to My Rajasthan Club, We received your business profile. Our team will review and get in touch with you.");

                        // }
                        $ownerInsert = [];
                        $ownerInsert['id'] = 0;
                        $ownerInsert['business_id'] = $response->id;
                        $ownerInsert['full_name'] = $userData->name;
                        $ownerInsert['gender'] = $userData->gender;
                        $ownerInsert['dob'] = $userData->dob;
                        $ownerInsert['email_id'] = $userData->email;
                        if($userData->profile_pic && !empty($userData->profile_pic))
                        {
                            $userFileName = $userData->profile_pic;
                            if(Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_ORIGINAL_IMAGE_PATH').$userFileName))
                            {
                                $userOriginalImgPath = Config::get('constant.USER_ORIGINAL_IMAGE_PATH').$userFileName;
                                $userThumbnailImgPath = Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$userFileName;
                                $ownereExtension = pathinfo(Config::get('constant.USER_ORIGINAL_IMAGE_PATH').$userFileName, PATHINFO_EXTENSION);
                                $ownerFileName = 'owner_'.uniqid().'.'.$ownereExtension;
                                $ownerOriginalImgPath = $this->OWNER_ORIGINAL_IMAGE_PATH.$ownerFileName;
                                $ownerThumbnailImgPath = $this->OWNER_THUMBNAIL_IMAGE_PATH.$ownerFileName;
                                Storage::disk(config('constant.DISK'))->copy($userOriginalImgPath, $ownerOriginalImgPath);
                                Storage::disk(config('constant.DISK'))->copy($userThumbnailImgPath, $ownerThumbnailImgPath);

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
                         $ownerSave = $this->objOwner->insertUpdate($ownerInsert);
                    }

                    $responseData['status'] = 1;
                    $responseData['message'] =  trans('apimessages.business_added_success');
                    $responseData['data'] = ['business_id' => $response->id, 'business_name' => $response->name, 'business_slag' => $response->business_slug];
                    $statusCode = 200;
                    $this->log->info('API addBusiness save successfully', array('login_user_id' => $userId));
                }
                else
                {
                    $this->log->error('API something went wrong while add business', array('login_user_id' => $userId));
                    $responseData['status'] = 0;
                    $responseData['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while add business', array('login_user_id' => $userId, 'error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

     /**
     * Get Timezone
     */
    public function getTimezone(Request $request)
    {
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $listArray = [];

            $timezone = Helpers::getTimezone();

            if(!empty($timezone))
            {
                $listArray = [];
                foreach($timezone as $zone)
                {
                    $listArray[] = $zone;
                }
                $responseData['status'] = 1;
                $responseData['message'] =  'Success';
                $responseData['data'] =  $listArray;

                $statusCode = 200;
            }
            else
            {
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.invalid_owner_id');
                $responseData['data'] = [];
                $statusCode = 200;
            }

        } catch (Exception $e) {
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    /*
    ** get SearchAutocomplete
    */
    public function getSearchAutocomplete(Request $request)
    {
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $listArray = [];

            if(isset($requestData['searchText']) && $requestData['searchText'] != '')
            {
                $entityTypeId = $this->getEntityIdByName($request->entity_type);

                $tagArray = [];
                $categoryArray = [];
                $BusinessArray = [];
                $Businesslist = $this->objBusiness->getAll(array('autoCompleteText'=>$requestData['searchText'],'asset_type_id' => $entityTypeId ));

                // return $Businesslist;
                if(count($Businesslist) > 0)
                {
                    foreach ($Businesslist as $key => $value) {
                        $listArray = [];
                        $listArray['value'] = trim($value->name);
                        $BusinessArray[] = $listArray;
                    }
                }

                if(empty($entityTypeId)) {
                    $metatagslist = $this->objMetatag->getAll(array('searchText'=>$requestData['searchText']));

                    if(count($metatagslist) > 0)
                    {
                        foreach ($metatagslist as $key => $value) {
                            $listArray = [];
                            $listArray['value'] = trim($value->tag);
                            $tagArray[] = $listArray;
                        }
                    }

                    $categoryList = $this->objCategory->getAll(array('searchText'=>$requestData['searchText']));
                    if(count($categoryList) > 0)
                    {
                        foreach ($categoryList as $key => $value) {

                            $categoryHasBusiness = $this->objCategory->categoryHasBusinesses($value->id);

                            if(!empty($categoryHasBusiness)  && count($categoryHasBusiness))
                            {
                                $listArray = [];
                                $listArray['value'] = ($value->name);
                                $categoryArray[] = $listArray;
                            }
                        }
                    }
                }
                $this->log->info('User Add tag', array('admin_user_id' =>  Auth::id()));
                $responseData['status'] = 1;
                $responseData['message'] =  trans('apimessages.get_tags_successfully');;
                $responseData['data'] = array_merge($BusinessArray,$categoryArray,$tagArray);
                
                // for array unique
				$responseData['data'] = collect($responseData['data'])->unique(function($item){ 
					return strtoupper($item['value']); 
				})->values()->toArray();
                
                $statusCode = 200;
            }
            else
            {
                $this->log->error('Something wrong when doing AutoComplete');
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = [];
                $statusCode = 200;
            }

        } catch (Exception $e) {
            $this->log->error('Something wrong when doing AutoComplete', array('error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        
        return response()->json($responseData, $statusCode);
    }

     /**
     * Get Business Listing By Category Id
     */
    public function getSearchBusinesses(Request $request)
    {


        $loginUserId = 0;
         try {

        $user = JWTAuth::parseToken()->authenticate();
        $loginUserId = $user->id;
            \Log::info("Search By User:".$loginUserId);
        } catch (TokenInvalidException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTAuthException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (Exception $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        }
        $headerData = (!empty($request->header('Platform'))) ? $request->header('Platform') : '';
        $searchCity = '';
        try
        {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
            $filters = [];

            if(isset($request->city) && $request->city != '') {
                if($request->city != 'All Locations') {
                    $filters['city'] = $request->city;
                    $searchCity = $request->city;
                }
            } else if(!empty($request->location)) {
                  $filters['city'] = $request->location;
                  $searchCity = $request->location;
            }

            if(isset($request->searchText) && $request->searchText != '')
            {
               $filters['searchText'] = $request->searchText;
            }

             \Log::info("Search For ==> City:".$request->city." SearchText:".$request->searchText);

            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {

               if(isset($request->limit) && !empty($request->limit) && isset($request->page))
               {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
               }
               elseif(isset($request->limit) && !empty($request->limit))
               {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                
            }
            elseif(!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM'))
            {

                if(isset($request->limit) && !empty($request->limit) && isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                }
                elseif(isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif(isset($request->page))
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
               
            } 
            else 
            {
                $offset = Helpers::getOffset($pageNo);
                $filters['offset'] = $offset;
                $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');         
            }

            if(isset($request->sortBy) && !empty($request->sortBy))
            {
                $filters['sortBy'] = $request->sortBy;

                if(isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
                {
                    $filters['latitude'] = $request->latitude;
                    $filters['longitude'] = $request->longitude;
                }
                    
                if (isset($request->radius) && !empty ($request->radius))
                {
                        
                    //$filters['radius'] = $request->radius;
                }
            } 
                

                /**
                 * @Modified on: 30th Jul, 2018
                 * Following code is commented becuase the query is not optimized, which is taking too much time to response data.
                 */
                // $businessListing = $this->objBusiness->getAll($filters);
                /**
                 * @Modified on: 30th Jul, 2018
                 * Optimized Searching query
                 */
		    $time1 = intval(microtime(true)*1000);

            $searchFilters = [];
            if(isset($filters['searchText']) && $filters['searchText'] != '')
            {
                $searchFilters['searchText'] = $filters['searchText'];
            }

            $searchFilters['approved'] = 1;
            if(isset($filters['city']) && $filters['city'] != ''){
                $searchFilters['city'] = $filters['city'];
            }

                /**
                 * @Modified on: 30th Jul, 2018
                 * Following code is commented becuase the query is not optimized, which is taking too much time to response data.
                 */
                // $businessAllListing = $this->objBusiness->getAll($searchFilters);
                /**
                 * @Modified on: 30th Jul, 2018
                 * Optimized Searching query
                 */
                // $businessAllListing = $this->objBusiness->getAllForFrontAndMobileApp($searchFilters);
            $businessesCount = $this->objBusiness->getAllCountForFrontAndMobileApp($searchFilters);

		    $businessListing = $this->objBusiness->getAllForFrontAndMobileApp($filters);
           
                // \Log::info($businessesCount);
            $time2 = intval(microtime(true)*1000);
            \Log::info("Search Query Execution time:=== " . ($time2 - $time1).'ms');
            $outputArray['status'] = 1;
            $outputArray['message'] = trans('apimessages.business_fetched_successfully');
            $statusCode = 200;

            $outputArray['data'] = array();
            $outputArray['data']['businesses'] = array();
            $isBusinessFound = count($businessListing);
            \Log::info("Total Business Found:=== ". $isBusinessFound);
            
            TempSearchTerm::create([
                'search_term' => $request->searchText, 
                'city' => $searchCity,
                'user_id' => $loginUserId, 
                'result_count' => $isBusinessFound, 
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'ip_address' =>  $request->ip()
            ]);
                // if($businessListing && count($businessListing) > 0)
            if($isBusinessFound > 0)
            {
                
                $outputArray['businessesTotalCount'] = (isset($businessesCount) && $businessesCount > 0) ? $businessesCount : 0;

                if($headerData == Config::get('constant.WEBSITE_PLATFORM'))
                {
                        $take = (isset($request->take) && $request->take > 0) ? $request->take : Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                        
                        // if($businessListing->count() < $take)
                        if($isBusinessFound < $take) {
                            $outputArray['loadMore'] = 0;
                        } else {
                            $perPageCnt = $request->page * $take;

                            if($businessesCount > $perPageCnt)
                            {
                                $outputArray['loadMore'] = 1;
                            }
                            else
                            {
                                $outputArray['loadMore'] = 0;
                            }
                        }
                }
                else
                {
                        // if($businessListing->count() < Config::get('constant.API_RECORD_PER_PAGE'))
                        if($isBusinessFound < Config::get('constant.API_RECORD_PER_PAGE'))
                        {
                            $outputArray['loadMore'] = 0;
                        } else{

                            $take = (isset($request->take) && $request->take > 0) ? $request->take : Config::get('constant.API_RECORD_PER_PAGE');

                            //if($businessListing->count() < $take)
                            if($isBusinessFound <  $take)
                            {
                                $outputArray['loadMore'] = 0;
                            } else {
                                $perPageCnt = $request->page * $take;
                                if($businessesCount > $perPageCnt)
                                {
                                    $outputArray['loadMore'] = 1;
                                }
                                else
                                {
                                    $outputArray['loadMore'] = 0;
                                }
                            }
                        }
                }
                    //return $isBusinessFound;
                for($i = 0; $i < $isBusinessFound; $i++) 
                {
                        $businessValue = $businessListing[$i];
                        
                        $owners = $businessValue->owners;
                        $user = $businessValue->user;
                        $businessImagesById = $businessValue->businessImagesById;
                        $business_logo = $businessValue->business_logo;
                        
                        unset($businessValue->owners);
                        unset($businessValue->user);
                        unset($businessValue->businessImagesById);
                        unset($businessValue->business_logo);

		                $outputArray['data']['businesses'][$i]['id'] = $businessValue->id;
                        $outputArray['data']['businesses'][$i]['name'] = $businessValue->name;
                        //$outputArray['data']['businesses'][$i]['business_slug'] = (isset($businessValue->business_slug) && !empty($businessValue->business_slug)) ? $businessValue->business_slug : '';
                        $outputArray['data']['businesses'][$i]['business_slug'] = $businessValue->business_slug;

                        $outputArray['data']['businesses'][$i]['address'] = $businessValue->address;
                        //$outputArray['data']['businesses'][$i]['document_approval'] = (($businessValue->document_approval))? url(Config::get('constant.VERIFIED_IMAGE')) :  url(Config::get('constant.NON_VERIFIED_IMAGE'));;

                        if($businessValue->document_approval ==3){
                        $outputArray['data']['businesses'][$i]['document_approval'] =  url(Config::get('constant.VERIFIED_IMAGE'));
                         }elseif($businessValue->document_approval ==2){
                          $outputArray['data']['businesses'][$i]['document_approval'] =    url(Config::get('constant.NON_VERIFIED_IMAGE2'));
                         }elseif($businessValue->document_approval ==1){

                            $outputArray['data']['businesses'][$i]['document_approval'] =  url(Config::get('constant.NON_VERIFIED_IMAGE'));
                         }
                        $outputArray['data']['businesses'][$i]['street_address'] = $businessValue->street_address;
                        $outputArray['data']['businesses'][$i]['locality'] = $businessValue->locality;
                        $outputArray['data']['businesses'][$i]['country'] = $businessValue->country;
                        $outputArray['data']['businesses'][$i]['state'] = $businessValue->state;
                        $outputArray['data']['businesses'][$i]['city'] = $businessValue->city;
                        $outputArray['data']['businesses'][$i]['taluka'] = $businessValue->taluka;
                        $outputArray['data']['businesses'][$i]['district'] = $businessValue->district;
                        $outputArray['data']['businesses'][$i]['pincode'] = $businessValue->pincode;
                        $outputArray['data']['businesses'][$i]['latitude'] = $businessValue->latitude;
                        $outputArray['data']['businesses'][$i]['longitude'] = $businessValue->longitude;
                        // $outputArray['data']['businesses'][$i]['latitude'] = (!empty($businessValue->latitude)) ? $businessValue->latitude : 0;
                        // $outputArray['data']['businesses'][$i]['longitude'] = (!empty($businessValue->longitude)) ? $businessValue->longitude : 0;
                        $outputArray['data']['businesses'][$i]['website_url'] = $businessValue->website_url;
                        $outputArray['data']['businesses'][$i]['membership_type'] = $businessValue->membership_type;
                        $outputArray['data']['businesses'][$i]['is_normal_view'] = $businessValue->is_normal_view;
                        $outputArray['data']['businesses'][$i]['entity_type'] = $businessValue->entity_type;

                        $outputArray['data']['businesses'][$i]['user_id'] = (isset($user) &&  !empty($user->id)) ? $user->id : '';
                        $outputArray['data']['businesses'][$i]['user_name'] = (isset($user) && !empty($user->name)) ? $user->name : '';
                        
                        $oCount = count($owners->toArray());
                        if($oCount > 0)
                        {
                            unset($outputArray['data']['businesses'][$i]['owners']);
                            $full_name = array_column($owners->toArray(), 'full_name');
                            $outputArray['data']['businesses'][$i]['owners'] = implode(', ', $full_name);
                        }
                
                        $outputArray['data']['businesses'][$i]['membership_type_icon'] = $businessValue->membership_type_icon;
                     
                        //$outputArray['data']['businesses'][$i]['categories_name_list'] = '';                        
                        // $minutes = 30;
                        // if(!empty($businessValue->category_id) && $businessValue->category_id != '')
                        // {
                        //     $categoryIdsArray = (explode(',', $businessValue->category_id));
                        //     $categoryCount = count($categoryIdsArray);
                        //     if($categoryCount > 0)
                        //     {
                        //         $cacheName = 'cat_'. str_replace(",", "_", $businessValue->category_id);
                        //         $categoryData = Cache::remember($cacheName, $minutes, function () use ($categoryIdsArray) {
                        //             return Category::whereIn('id', $categoryIdsArray)
                        //                         ->select('id AS category_id', 
                        //                                 'name AS category_name', 
                        //                                 'category_slug', 
                        //                                 'cat_logo AS category_logo',
                        //                                 'parent AS parent_category_id')
                        //                         ->get();
                        //         });         

                        //         $outputArray['data']['businesses'][$i]['categories_name_list'] = !empty($categoryData->first()) ? $categoryData->first()->category_name : '';
                        //         $categoryForImage = $categoryData->first();
                        //         // $outputArray['data']['businesses'][$i]['categories'] = $categoryData->toArray();
                        //     }
                        // }
                        // $outputArray['data']['businesses'][$i]['parent_categories'] = array();
                        // if(!empty($businessValue->parent_category) && $businessValue->parent_category != '')
                        // {
                        //     $categoryIdsArray = (explode(',', $businessValue->parent_category));
                        //     $categoryCount = count($categoryIdsArray);
                        //     if($categoryCount > 0)
                        //     {
                        //         $cacheName = 'cat_'. str_replace(",", "_", $businessValue->parent_category);
                        //         $categoryData = Cache::remember($cacheName, $minutes, function () use ($categoryIdsArray) {
                        //                 return Category::whereIn('id', $categoryIdsArray)
                        //                             ->select('id AS category_id', 
                        //                                     'name AS category_name', 
                        //                                     'category_slug', 
                        //                                     'cat_logo AS category_logo',
                        //                                     'parent AS parent_category_id')
                        //                             ->get(); 
                        //             });
                        //         $outputArray['data']['businesses'][$i]['parent_categories'] = $categoryData->toArray();
                        //     }
                        // }
                        
                        $categoryData = $this->getBusinessParentCategory($businessValue->parent_category);
                        $outputArray['data']['businesses'][$i]['parent_categories'] = $categoryData;
                        $outputArray['data']['businesses'][$i]['categories_name_list'] = (!empty($categoryData)) ? implode(', ',$categoryData->pluck('category_name')->toArray()) : '';

                        $s3url =   Config::get('constant.s3url');
                        $categoryForImage = null;
                        $categoryLogo = (!empty($categoryData)) ? $categoryData[0]->category_logo : url(Config::get('constant.DEFAULT_IMAGE'));
                        $imgThumbUrl = (((isset($businessImagesById) && !empty($businessImagesById) && !empty($businessImagesById->image_name)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name : empty($categoryForImage)) ? url(Config::get('constant.DEFAULT_IMAGE')) : $categoryLogo;
                        $outputArray['data']['businesses'][$i]['business_image'] = $imgThumbUrl;

                        $businessLogoThumbImgPath = ((isset($business_logo) && !empty($business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo : $imgThumbUrl;
                        $outputArray['data']['businesses'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;
                    }

                    $i = 0;

                }
                else
                {
                    $this->log->info('API getBusinessListingByCatId no records found');
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.norecordsfound');
                    $outputArray['loadMore'] = 0;
                    $statusCode = 200;
                    $outputArray['data'] = new \stdClass();
                }
                return response()->json($outputArray, $statusCode);

        } catch (Exception $e) {
            $this->log->error('API something went wrong while getBusinessListingByCatId');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    public function getBusinessApproved(Request $request)
    {

        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $validator = Validator::make($request->all(), [
                'business_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API something went wrong while get business is approved or not',array('business_id' => $requestData['business_id'], 'error' => $e->getMessage()));
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $response = $this->objBusiness->find($requestData['business_id']);

                if($response)
                {
                    $mainArray = [];

                    $mainArray['isApproved'] = $response->approved;
                    $mainArray['membership_type'] = $response->membership_type;
                    if($response->membership_type == 2)
                    {
                        $mainArray['membership_type'] = 1;
                        $mainArray['membership_type_icon'] = url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE'));
                    }
                    elseif($response->membership_type == 1)
                    {
                       $mainArray['membership_type_icon'] = url(Config::get('constant.PREMIUM_ICON_IMAGE'));
                    }
                    else
                    {
                      
                        if($response->document_approval ==3){
                             $mainArray['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_BLUE'));
                       
                         }elseif($response->document_approval ==2){

                            $mainArray['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_YELLOW'));
                         }else{

                            $mainArray['membership_type_icon'] = url(Config::get('constant.BASIC_ICON_IMAGE_ORANGE'));
                         }
                     
                    }
                    $this->log->info('API get business approved or not',array('business_id' => $requestData['business_id']));
                    $responseData['status'] = 1;
                    $responseData['message'] =  trans('apimessages.default_success_msg');
                    $responseData['data'] =  $mainArray;
                    $statusCode = 200;

                }
                else
                {
                    $this->log->info('API Invalid business id', array('business_id' => $requestData['business_id']));
                    $responseData['status'] = 0;
                    $responseData['message'] = trans('apimessages.invalid_business_id');
                    $responseData['data'] =  [];
                    $statusCode = 200;
                }
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while get business is approved or not', array('business_id' => $requestData['business_id'], 'error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    public function sendMembershipRequest(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();

        try
        {
            
            $validator = Validator::make($requestData, [
                'subscription_plans_id' => 'required',
            ]);
            if ($validator->fails())
            {
                $this->log->error('API something went wrong while send membership plan request',array('subscription_plans_id' => $requestData['subscription_plans_id']));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $insertId = MembershipRequest::firstOrCreate(['subscription_plans_id' =>  $requestData['subscription_plans_id'], 'user_id' => $user->id]);
                $insertId->status = 0;
                $insertId->save();
                //$business = $this->objMembershipRequest->getMembershipRequestDetailsById($insertId->id);
                $plan_id = $requestData['subscription_plans_id'];
                $input['plan_amount']=SubscriptionPlan::where('id',$plan_id)->pluck('price')->first();


                       $payment_request_id = uniqid().'-'.$user->id .'-'.time();

                       $users_data = User::find($user->id);

                        Log::info("Web Pay Payment User Data:".$users_data->name." ".$users_data->phone);
                        $names = $this->getFirstLastName($users_data->name);

                        $payg = new PayGIntegration();        

                        $post_data =  array();
                        $post_data['OrderAmount'] = $input['plan_amount'];
                        $post_data['OrderType'] = 'PAYMENT';
                        $post_data['Source'] = '';
                        $post_data['IntegrationType'] = '';
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
                        $post_data['IntegrationData']['Source'] = "";
                        $post_data['IntegrationData']['IntegrationType'] = "";
                        $post_data['IntegrationData']['HashData'] = "";
                        $post_data['IntegrationData']['PlatformId'] = "";
                        
                        if(isset($users_data->singlebusiness) && $users_data->singlebusiness->membership_type == 2) {
                            $membershipType = "Lifetime";
                        } elseif(isset($users_data->singlebusiness) && $users_data->singlebusiness->membership_type == 1) {
                            $membershipType = "Premium";
                        } else {
                            $membershipType = "Basic";
                        }
                        $productData = [
                            'membershipType' => $membershipType,
                            'businessType' => $request->business_type,
                            'geoLocation' => $request->geo_location,
                            'ipAddress' => $request->ip_address,
                        ];
                        $paymentType = $request->payment_type;
                        $post_data['ProductData']= json_encode($productData);
                        $post_data['callback_url']= url('api/payment/status');
                        $redirect = config('app.Redirect_Url_Web').'/'.$user->id;
                        
                        // call class object to order create function
                        //$resonseOrderData = $payg->orderCreateWeb($post_data,$payment_request_id);  
                        $resonseOrderData = $payg->orderCreate($post_data,$redirect,$paymentType);  

                        // handle the json object response
                        $resonseOrderData = json_decode($resonseOrderData, false);
                           //echo "<pre>";  print_r( $resonseOrderData); 

                        Log::info("Web Order Create Pay Response : ".json_encode($resonseOrderData));

                        $transaction=new PaymentTransaction();
                        $transaction->user_id=$user->id;
                        $transaction->plan_id= $requestData['subscription_plans_id'];
                        $transaction->order_id=$resonseOrderData->OrderKeyId;
                        $transaction->transaction_id=$resonseOrderData->UniqueRequestId;
                        $transaction->OderKeyId=$payment_request_id;
                        //$transaction->business_id=$business->id;
                        $transaction->save();   
                        
                
                /*
                // start- send mail by helpers function
                $replaceArray = array();
                $replaceArray['SUBJECT'] = trans('constant.membershipRequestSubject');
                $replaceArray['USERNAME'] = Auth::user()->name;
                $replaceArray['PHONE'] = Auth::user()->phone;
                $replaceArray['PLAN'] = $business[0]->subscriptionPlan->name;
                $replaceArray['DATE'] = date("d M Y",strtotime($business[0]->created_at));
                if(isset(Auth::user()->singlebusiness->name) && !empty(Auth::user()->singlebusiness->name))
                {
                    $replaceArray['BUSINESSNAME'] = Auth::user()->singlebusiness->name;
                }
                else
                {
                    $replaceArray['BUSINESSNAME'] = 'No Business';
                }

                $et_templatepseudoname = 'membership-request';
               
                $emailParametersArray = [
                                            'toEmail' => Config::get('constant.ADMIN_EMAIL')
                                        ];
                $toName = 'My rajasthan club - Admin';



                Helpers::sendMailByTemplate($replaceArray,$et_templatepseudoname,$emailParametersArray,$toName);

                $this->log->info('API Membership plan request sent successfully', array('login_user_id' =>Auth::id(),'subscription_plans_id' => $requestData['subscription_plans_id']));
                */

                $responseData['status'] = 1;
                $responseData['payment_status'] = 1;
                $responseData['payment_status_up_link'] = $resonseOrderData->PaymentProcessUrl;


                $responseData['message'] = 'Membership Plan Payment initiated successfully.'; 

                trans('apimessages.membership_plan_sent_successfully');

                $statusCode = 200;
            }

        } catch (Exception $e) {
             Log::info("Payment request creation failed".$e->getMessage());
             $responseData = ['status' => 0, 'message' => "Payment request creation failed".$e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    public function doPaymentOrderDone(Request $request)
    {
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();

        try
        {
           
            Log::info("Pay G Android Response for Payment Order Id: ".$requestData['payment_order_id']);
            $users_data = DB::table('payment_transactions')
            ->where('user_id','=',$requestData['payment_order_id'])
            ->orderByRaw('id desc')
            ->take(1)
            ->first();
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
        catch (Exception $e)
        {
            $this->log->error('API something went wrong while send membership plan request',array('subscription_plans_id' => $requestData['subscription_plans_id'], 'error' => $e->getMessage()));
             $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    public function getBusinessNearByMap(Request $request)
    {
        //$user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();

        try
        {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required',
                'longitude' => 'required',
                'radius' => 'required'
            ]);
            if ($validator->fails())
            {
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $filters = [];

                $filters['sortBy'] = 'nearMe';
                $filters['radius'] = $request->radius;
                $filters['latitude'] = $request->latitude;
                $filters['longitude'] = $request->longitude;

                $businessList = $this->objBusiness->getAll($filters);
                if(count($businessList) > 0)
                {
                    $mainArray = [];
                    foreach($businessList as $business)
                    {
                        $listArray = [];
                        $listArray['id'] = $business->id;
                        $listArray['name'] = $business->name;
                        $listArray['latitude'] = $business->latitude;
                        $listArray['longitude'] = $business->longitude;

                        $address = [];
                        $addressImplode = '';
                        if($business->street_address != ''){
                            $address[] = $business->street_address;
                        }
                        if($business->locality != '')
                        {
                            $address[] = $business->locality;
                        }
                        if($business->city != '')
                        {
                            $address[] = $business->city;
                        }
                        if($business->state != '')
                        {
                            $address[] = $business->state;
                        }
                        if(!empty($address))
                        {
                            $addressImplode = implode(', ',$address);
                        }

                        $listArray['address'] = $addressImplode;
                        if($business->pincode != '' && $listArray['address'] != '')
                        {
                            $listArray['address'] = $addressImplode.' - '.$business->pincode;
                        }

                        $mainArray[] = $listArray;
                    }
                        $responseData['status'] = 1;
                        $responseData['message'] =  trans('apimessages.default_success_msg');
                        $responseData['data'] =  $mainArray;
                        $statusCode = 200;

                }
                else
                {
                    $this->log->error('API something went wrong while get BusinessList');
                    $responseData['status'] = 0;
                    $responseData['message'] = trans('apimessages.norecordsfound');
                    $responseData['data'] =  [];
                    $statusCode = 200;
                }
            }


        } catch (Exception $e) {
            $this->log->error('API something went wrong while get BusinessList', array('error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    /**
    *  Get getBusinessBranding
    */
    public function getBrandingFileOrText(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $mainArray = [];
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
				$pageName = $brandingDetail->page_name;
				$isBusiness = false;
				if(str_contains($pageName,'business-detail') || empty($pageName)) {
					$isBusiness = true;
				}
                $mainArray['type'] = $type;
                $mainArray['page_name'] = $pageName;
                $mainArray['isBusiness'] = $isBusiness;
                $this->log->info('API getBusinessBranding successfully');
                $responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.default_success_msg');
                $responseData['data'] =  $mainArray;
                $statusCode = 200;
            }
            else
            {
                $this->log->info('API get business branding not found');
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] =  $mainArray;
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getBusinessBranding', array('error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);
    }

    /**
     * Retrive the list of Life time members
     */
    public function getLifetimeMembers(Request $request)
    {
        try
        {
            $filters = ["membership_type" => 2];
            $businessesCount = $this->objBusiness->getLifetimeMembersCount($filters);
            $businessListing = $this->objBusiness->getLifetimeMembers($filters);

            $outputArray['status'] = 1;
            $outputArray['message'] = trans('apimessages.business_fetched_successfully');
            $statusCode = 200;

            $outputArray['data'] = array();
            $outputArray['data']['businesses'] = array();
            $isBusinessFound = count($businessListing);

            if($isBusinessFound > 0)
            {                    
                $outputArray['businessesTotalCount'] = (isset($businessesCount) && $businessesCount > 0) ? $businessesCount : 0;

                for($i = 0; $i < $isBusinessFound; $i++) {
                    $businessValue = $businessListing[$i];
                    
                    $outputArray['data']['businesses'][$i]['id'] = $businessValue->id;
                    $outputArray['data']['businesses'][$i]['name'] = $businessValue->name;
                    $outputArray['data']['businesses'][$i]['description'] = $businessValue->description;
                    $outputArray['data']['businesses'][$i]['owner_name'] = $businessValue->full_name;



                                      if ($businessValue->photo != '' && Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$businessValue->photo){
                                          $s3url =   Config::get('constant.s3url');
                                         $businessImages = $s3url.Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$businessValue->photo;
                                    }else{
                                        $businessImages = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }

                                    $outputArray['data']['businesses'][$i]['owner_photo'] = $businessImages;


                    // $outputArray['data']['businesses'][$i]['owner_photo'] = (($businessValue->photo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$businessValue->photo) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$businessValue->photo) : url(Config::get('constant.DEFAULT_IMAGE'));

                                 if ($businessValue->photo != '' && Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH').$businessValue->photo){
                                          $s3url =   Config::get('constant.s3url');
                                         $businessTHUMBNAILImages = $s3url.Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH').$businessValue->photo;
                                    }else{
                                        $businessTHUMBNAILImages = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }          
                                    
                                    $outputArray['data']['businesses'][$i]['owner_thumbnail'] = $businessTHUMBNAILImages;


                    // $outputArray['data']['businesses'][$i]['owner_thumbnail'] = (($businessValue->photo != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH').$businessValue->photo) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH').$businessValue->photo) : url(Config::get('constant.DEFAULT_IMAGE'));
                }                    
            }
            else
            {
                $this->log->info('API getLifetimeMembers no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = new \stdClass();
            }
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getLifetimeMembers');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Get Near By Businesses
     */
    public function getNearByBusinesses(Request $request)
    {
        // \Log::info('longitude and longitude', array('latitude' => $request->latitude,'longitude' => $request->longitude));
        // $this->log->info('longitude and longitude', array('latitude' => $request->latitude,'longitude' => $request->longitude));
        //$user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        $outputArray = [];
        try
        {
            $filters = [];
            $filters['approved'] = 1;
            $time1 = intval(microtime(true)*1000);
            if(!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM'))
            {
                if(isset($request->page) && $request->page != 0)
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }
            else
            {
                if(isset($request->page) && $request->page != 0)
                {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }

            if(isset($request->sortBy) && !empty($request->sortBy) && $request->sortBy == 'ratings')
            {
                $filters['sortBy'] = 'ratings';
                $businessListing = $this->objBusiness->getBusinessesByRating($filters);
            }
            elseif (isset($request->sortBy) && $request->sortBy == 'nearMe' && isset($request->radius) && !empty ($request->radius) && isset($request->latitude) && !empty ($request->latitude) && isset($request->longitude) && !empty ($request->longitude))
            {
                $this->log->info('longitude and longitude', array('latitude' => $request->latitude,'longitude' => $request->longitude));
                $filters['sortBy'] = 'nearMe';
                $filters['latitude'] = $request->latitude;
                $filters['longitude'] = $request->longitude;
                $filters['radius'] = $request->radius;
                $businessListing = $this->objBusiness->getAllHomeBusinesses($filters);
            }
            else
            {
                $filters['orderBy'] = 'promoted';
                $businessListing = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            }
            $time2 = intval(microtime(true)*1000);
            \Log::info("Search Query Execution time:=== " . ($time2 - $time1).'ms');
            // $outputArray['data'] = array();
            // $outputArray['data']['businesses'] = array();
            $isBusinessFound = count($businessListing);
            // \Log::info($isBusinessFound);


            if($isBusinessFound > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.nearby_me_business_fetched_successfully');
                $statusCode = 200;
                $outputArray['businessesTotalCount'] = count($businessListing);

                //return $isBusinessFound;
                for($i = 0; $i < $isBusinessFound; $i++) {
                    if($i==0)
                    {
                        $time3 = intval(microtime(true)*1000);
                    }
                    $businessValue = $businessListing[$i];

                    $owners = $businessValue->owners;
                    $user = $businessValue->user;
                    $businessImagesById = $businessValue->businessImagesById;
                    $business_logo = $businessValue->business_logo;
                    
                    unset($businessValue->owners);
                    unset($businessValue->user);
                    unset($businessValue->businessImagesById);
                    unset($businessValue->business_logo);

                    $outputArray['data'][$i]['id'] = $businessValue->id;
                    $outputArray['data'][$i]['name'] = $businessValue->name;
                    //$outputArray['data'][$i]['business_slug'] = (isset($businessValue->business_slug) && !empty($businessValue->business_slug)) ? $businessValue->business_slug : '';
                    $outputArray['data'][$i]['business_slug'] = $businessValue->business_slug;

                    $outputArray['data'][$i]['address'] = $businessValue->address;
                   // $outputArray['data'][$i]['document_approval'] = (($businessValue->document_approval))? url(Config::get('constant.VERIFIED_IMAGE')) :  url(Config::get('constant.NON_VERIFIED_IMAGE'));;

                      if($businessValue->document_approval ==3) {
                            $outputArray['data'][$i]['document_approval']=  url(Config::get('constant.VERIFIED_IMAGE'));
                         }
                         elseif($businessValue->document_approval ==2) {
                            $outputArray['data'][$i]['document_approval']=    url(Config::get('constant.NON_VERIFIED_IMAGE2'));
                         }
                         elseif($businessValue->document_approval ==1) {

                            $outputArray['data'][$i]['document_approval']=  url(Config::get('constant.NON_VERIFIED_IMAGE'));
                         }
                    $outputArray['data'][$i]['street_address'] = $businessValue->street_address;
                    $outputArray['data'][$i]['locality'] = $businessValue->locality;
                    $outputArray['data'][$i]['country'] = $businessValue->country;
                    $outputArray['data'][$i]['state'] = $businessValue->state;
                    $outputArray['data'][$i]['city'] = $businessValue->city;
                    $outputArray['data'][$i]['taluka'] = $businessValue->taluka;
                    $outputArray['data'][$i]['district'] = $businessValue->district;
                    $outputArray['data'][$i]['pincode'] = $businessValue->pincode;
                    $outputArray['data'][$i]['latitude'] = $businessValue->latitude;
                    $outputArray['data'][$i]['longitude'] = $businessValue->longitude;
                    // $outputArray['data'][$i]['latitude'] = (!empty($businessValue->latitude)) ? $businessValue->latitude : 0;
                    // $outputArray['data'][$i]['longitude'] = (!empty($businessValue->longitude)) ? $businessValue->longitude : 0;
                    $outputArray['data'][$i]['website_url'] = $businessValue->website_url;
                    $outputArray['data'][$i]['membership_type'] = $businessValue->membership_type;

                    $outputArray['data'][$i]['user_id'] = (isset($user) && !empty($user->id)) ? $user->id : '';
                    $outputArray['data'][$i]['user_name'] = (isset($user) && !empty($user->name)) ? $user->name : '';
                    
                    $oCount = count($owners->toArray());
                    if($oCount > 0)
                    {
                        unset($outputArray['data'][$i]['owners']);
                        $full_name = array_column($owners->toArray(), 'full_name');
                        $outputArray['data'][$i]['owners'] = implode(', ', $full_name);
                    }
                    
                    $outputArray['data'][$i]['descriptions'] = $businessValue->description;
                    $outputArray['data'][$i]['phone'] = $businessValue->phone;
                    $outputArray['data'][$i]['country_code'] = $businessValue->country_code;
                    $outputArray['data'][$i]['mobile'] = $businessValue->mobile;
                    $outputArray['data'][$i]['email_id'] = (!empty($businessValue->email_id)) ? $businessValue->email_id : '';
                    $outputArray['data'][$i]['membership_type_icon'] = $businessValue->membership_type_icon;

                    $minutes = 30;
                    if(!empty($businessValue->category_id) && $businessValue->category_id != '')
                    {
                        $categoryIdsArray = (explode(',', $businessValue->category_id));
                        $categoryCount = count($categoryIdsArray);
                        if($categoryCount > 0)
                        {
                            $cacheName = 'cat_'. str_replace(",", "_", $businessValue->category_id);
                            $categoryData = Cache::remember($cacheName, $minutes, function () use ($categoryIdsArray) {
                                return Category::whereIn('id', $categoryIdsArray)
                                            ->select('id AS category_id', 
                                                    'name AS category_name', 
                                                    'category_slug', 
                                                    'cat_logo AS category_logo',
                                                    'parent AS parent_category_id')
                                            ->get();
                            });

                            $outputArray['data'][$i]['categories_name_list'] = !empty($categoryData->first()) ? $categoryData->first()->category_name : '';
                            $categoryForImage = $categoryData->first();
                            $outputArray['data'][$i]['categories'] = $categoryData->toArray();

                            if(count($outputArray['data'][$i]['categories'])>0)
                            {
                                foreach ($outputArray['data'][$i]['categories'] as $keys => $values)
                                {

                                    if ($values['category_logo'] != '' && (Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo'])) {
                                          $s3url =   Config::get('constant.s3url');
                                          // return $s3url;
                                         $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo'];
                                    }else{
                                        $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }

                                    

                                    // $catLogoPath = (($values['category_logo'] != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo']))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo']) : url(Config::get('constant.DEFAULT_IMAGE'));
                                    $outputArray['data'][$i]['categories'][$keys]['category_logo'] = $catLogoPath;
                                }
                            }
                        }
                    }
                    $outputArray['data'][$i]['parent_categories'] = array();
                     $outputArray['data'][$i]['parent_category_name'] ='';
                    if(!empty($businessValue->parent_category) && $businessValue->parent_category != '')
                    {
                        $categoryIdsArray1 = (explode(',', $businessValue->parent_category));
                        $categoryCount1 = count($categoryIdsArray1);
                        $outputArray['data'][$i]['parent_category_name'] = '';
                        if($categoryCount1 > 0)
                        {
                            $cacheName = 'cat_'. str_replace(",", "_", $businessValue->parent_category);
                            $categoryData1 = Cache::remember($cacheName, $minutes, function () use ($categoryIdsArray1) {
                                    return Category::whereIn('id', $categoryIdsArray1)
                                                ->select('id AS category_id', 
                                                        'name AS category_name', 
                                                        'category_slug', 
                                                        'cat_logo AS category_logo')
                                                ->get(); 
                                });
                            $outputArray['data'][$i]['parent_categories'] = $categoryData1->toArray();

                            if(count($outputArray['data'][$i]['parent_categories'])>0)
                            {
                                foreach ($outputArray['data'][$i]['parent_categories'] as $keys => $values)
                                {
                                    if($outputArray['data'][$i]['parent_category_name']!='')
                                    {
                                        $outputArray['data'][$i]['parent_category_name'] .= ", ".$values['category_name'];
                                    }
                                    else
                                    {
                                        $outputArray['data'][$i]['parent_category_name'] .= $values['category_name'];
                                    }

                                    if ($values['category_logo'] != '' && (Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo'])) {
                                         $s3url =   Config::get('constant.s3url');
                                         $catLogoPath = $s3url.Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo'];
                                    }else{
                                        $catLogoPath = url(Config::get('constant.DEFAULT_IMAGE'));
                                    }

                                    // $catLogoPath = (($values['category_logo'] != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo']))) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$values['category_logo']) : url(Config::get('constant.DEFAULT_IMAGE'));
                                    $outputArray['data'][$i]['categories'][$keys]['category_logo'] = $catLogoPath;
                      $outputArray['data'][$i]['categories_name_list'] = $outputArray['data'][$i]['parent_category_name'];

                                }
                            }
                        }
                    }



                    // $imgThumbUrl = ((isset($businessImagesById) && !empty($businessImagesById) && !empty($businessImagesById->image_name)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name) : empty($categoryForImage) ? url(Config::get('constant.DEFAULT_IMAGE')) : Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$categoryData->first()->category_logo);
                    // $outputArray['data'][$i]['business_image'] = $imgThumbUrl;

                    // $businessLogoThumbImgPath = ((isset($business_logo) && !empty($business_logo)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo)) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo) : $imgThumbUrl;
                    // $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;




                    // if (isset($businessImagesById) && !empty($businessImagesById->image_name) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$businessImagesById->image_name)) {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl = Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name);
                    // } else if (!empty($outputArray['data'][$i]['parent_categories'])) {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl =  Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$outputArray['data'][$i]['parent_categories'][0]['category_logo']);
                    // } else {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                    // }

                    // if (isset($business_logo) && !empty($business_logo) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$business_logo)) {
                    //     $outputArray['data'][$i]['logo_thumbnail'] = Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo);
                    // } else {
                    //     $outputArray['data'][$i]['logo_thumbnail'] = $imgThumbUrl;
                    // }

                    // if (isset($businessImagesById) && !empty($businessImagesById->image_name) && Storage::disk(config('constant.DISK'))->exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$businessImagesById->image_name)) {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl = Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name);
                    // } else if (!empty($outputArray['data'][$i]['parent_categories'])) {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl = Storage::disk(config('constant.DISK'))->url(Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH').$outputArray['data'][$i]['parent_categories'][0]['category_logo']);
                    // } else {
                    //     $outputArray['data'][$i]['business_image'] = $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                    // }

                    // if (isset($business_logo) && !empty($business_logo) && file_exists($this->BUSINESS_THUMBNAIL_IMAGE_PATH.$business_logo)) {
                    //     $outputArray['data'][$i]['logo_thumbnail'] = Storage::disk(config('constant.DISK'))->url(Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo);
                    // } else {
                    //     $outputArray['data'][$i]['logo_thumbnail'] = $imgThumbUrl;
                    // }

                   $s3url =   Config::get('constant.s3url');

                   $imgThumbUrl = ((isset($businessImagesById) && !empty($businessImagesById) && !empty($businessImagesById->image_name))) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$businessImagesById->image_name : (empty($outputArray['data'][$i]['categories'][0]['category_logo']) ? url(Config::get('constant.DEFAULT_IMAGE')) : $outputArray['data'][$i]['categories'][0]['category_logo']);
                    $outputArray['data'][$i]['business_image'] = $imgThumbUrl;
                    
                    $businessLogoThumbImgPath = ((isset($business_logo) && !empty($business_logo)) && $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo) ? $s3url.Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH').$business_logo : (empty($outputArray['data'][$i]['categories'][0]['category_logo']) ? url(Config::get('constant.DEFAULT_IMAGE')) : $outputArray['data'][$i]['categories'][0]['category_logo']);
                    $outputArray['data'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;




                    if($i==0)
                    {
                        $time4 = intval(microtime(true)*1000);
                        \Log::info("Search Query Execution time:=== " . ($time4 - $time3).'ms');
                    }
                    $outputArray['data'] = array_values($outputArray['data']);
                }
            }
            else
            {
                $this->log->info('API getNearByBusinesses no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getNearByBusinesses',array('error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
	
	/**
	 * Get Membership Page Details
	 *
	 * @return void
	 */
	public function getMembershipPageDetails()
	{
		$isWebsite = false;
        $description1 = "";
		if($isWebsite) {			
			$description = trans('apimessages.top_description_website');
			$type = 'website';
		} else {
			//$description = 'Subscribe to Premium Membership which includes creation of your PUBLIC WEBSITE and other great benefits.Your support will ensure Ryuva Club operation run smoothly for growth of OUR Rajput Community.';
			
            if(config('app.name') == 'RYEC') {
                $description = trans('apimessages.top_description_ryuva');
            } else {
                $description = trans('apimessages.top_description');
            }
			
            $description1 =  trans('apimessages.top_description_benefit');
			$type = 'plan';
		}
		$data['top_description'] =  $description;
        $data['top_description1'] =  $description1;
        
        $url = config('constant.FRONT_END_URL');
        $title = config('constant.APP_SHORT_NAME');
		$data['premium_benifits'] = [
			['icon' => $url."assets/images/position-near-by.png", 'title'=>'Free Public Website - No Domain Required, No Hosting Charges, No Maintanance'],
			['icon' => $url."assets/images/social-media.png", 'title'=>'800K Members - Social Media Promotion (Facebook, Instagram, Linkedin)'],
			['icon' => $url."assets/images/priority-search.png", 'title'=>'Top Listing in Search Results'],
			['icon' => $url."assets/images/investor_dark.png", 'title'=>'Investment Opportunities'],
			['icon' => $url."assets/images/premium-business.png", 'title'=>'Listing in Premium Business Tab'],
			['icon' => $url."assets/images/conference-invitation.png", 'title'=>'Invitation to '.$title.' Premium Member Conference'],
			['icon' => $url."assets/images/product-listing.png", 'title'=>'Unlimited Product Listing'],
			['icon' => $url."assets/images/product-listing.png", 'title'=>'Unlimited Service Listing'],
			['icon' => $url."assets/images/marketplace_dark.png", 'title'=>'Unlimited Ads in Marketplace'],
			['icon' => $url."assets/images/details_inquires.png", 'title'=>'Unlimited Business Inquiries'],
			['icon' => $url."assets/images/chat-potential.png", 'title'=>'Chat with Customers'],
			['icon' => $url."assets/images/review-business.png", 'title'=>'Reviews and Ratings']
		];
		$data['type'] = $type;
		$data['is_iphone'] = config('constant.IS_IPHONE');
		$response =['status'=> 1, 'message' =>trans('apimessages.getting_membership_detail'),'data' => $data ];
		return response()->json($response,200);
	}

    public function qrCodeGenerate(Request $request){
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        try{
                $validator = \Validator::make($request->all(), 
                [                    
                    'business_id' => 'required',
                ]);
                if ($validator->fails()) {
                    Log::error("Business Id for QrCode validation failed.");
                    $responseData['message'] = $validator->messages()->all()[0];
                }else{
                    $business_id = $request->input('business_id');
                    $business = Business::select('id','name','business_slug','asset_type_id')->find($business_id);
                    if(empty($business)) {
                        $responseData['message'] = trans('apimessages.invalid_business_id');
                        return response()->json($responseData,200);
                    }
                    $fileName = $business_id.'.png';
                    $folderName = config('constant.QrCode_PATH');
                    if(Storage::disk(config('constant.DISK'))->exists($folderName.$fileName)){
                        $data['url'] = Storage::disk(config('constant.DISK'))->url($folderName.$fileName);
                    }else{
                        $url = getUrlOfEntityDetail($business)."?business_id=".$business_id;
                        // $businessData = [
                        //     "business_id" => $business_id,
                        //     "business_slug" => $business['business_slug'],
                        //     "business_url" => $url,
                        //     ];
                        //$file = (string)\QrCode::format('png')->size(200)->generate(json_encode($businessData));
                        $file = (string)\QrCode::format('png')->size(200)->generate($url);
                        $result = Helpers::addFileToStorage($fileName, $folderName, $file);
                        $data['url'] = Storage::disk(config('constant.DISK'))->url($folderName.$fileName);
                    }
                    $responseData['status'] = 1;
                    $responseData['message'] = trans('apimessages.qr_code_generated');
                    $responseData['data'] = $data;
                } 
            return response()->json($responseData,200);
        }catch (\Exception $e) {
			Log::error("Getting error while genrating qrcode: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * getEntityInOtherLang
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityInOtherLang(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required',
                'language' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while entity in other language');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $id = $request->entity_id;
                $language = $request->input('language','english');
                if($language == 'english') {
                    $entity = Business::select('id as entity_id',DB::raw('"english" as language'),'description','short_description')->find($id)->makeHidden(['categories','business_logo_url']);
                } else {
                    $entity = EntityDescriptionLanguage::where('entity_id',$id)->where('language',$language)->first();
                }
                $response =['status'=> 1, 'message' =>trans('apimessages.getting_entity_in_other_language'),'data' => $entity ];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error($th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * get entity know more detail in multi language
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityKnowMore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while get know more');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $id = $request->entity_id;
                $language = $request->input('language','english');
                $entityKnowMore = EntityKnowMore::where('entity_id',$id)->where('language',$language)->get();
                if($request->from == 'admin') {
                    $entityKnowMore = view('Admin.Entity.know-more',['knowMores' => $entityKnowMore,'selectedLanguage' => $language])->render();
                }
                $response =['status'=> 1, 'message' =>trans('apimessages.getting_know_more_detail'),'data' => $entityKnowMore ];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error($th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
        
    /**
     * get custom details of entity in multiple language
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityCustomDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while get custom details');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $id = $request->entity_id;
                $language = $request->input('language','english');
                $customDetails = EntityCustomField::where('entity_id',$id)->where('language',$language)->get();
                if($request->from == 'admin') {
                    $customDetails = view('Admin.Entity.know-more',['knowMores' => $customDetails,'selectedLanguage' => $language,'langId'=>'customDetailLang','descClass' =>'custom-detail-desc'])->render();
                }
                $response =['status'=> 1, 'message' =>trans('apimessages.getting_custom_detail'),'data' => $customDetails ];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error($th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * get entity for near by section with sql filter
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityNearBy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while get know more');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $id = $request->entity_id;
                $nearBys = EntityNearbyFilter::where('entity_id',$id)->get();
                $data = [];
                $responseData = [];
                foreach($nearBys as $nearBy) {
                    $data['id'] = $nearBy->id;
                    $data['title'] = $nearBy->title;
                    //$data['is_enable_filter'] = $nearBy->is_enable_filter;
                    //$data['entity_filters'] = getEntities();
                    $data['entities'] = $this->getNearEntityByFilter($nearBy);
                    $responseData[] = $data;
                }
                $response =['status'=> 1, 'message' =>trans('apimessages.getting_near_by_entity'),'data' => $responseData ];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error($th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
        
    /**
     * getFilterEntitiesForNearBy
     * 
     * Note: currently not in use this api
     *
     * @param  mixed $request
     * @return void
     */
    // public function getFilterEntitiesForNearBy(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'entity_nearby_id' => 'required',
    //             'asset_type_id' => 'required'
    //         ]);
    //         if ($validator->fails())
    //         {
    //             $this->log->error('API validation failed while getFilterEntitiesForNearBy');
    //             $outputArray['status'] = 0;
    //             $outputArray['message'] = $validator->messages()->all()[0];
    //             $statusCode = 200;
    //             return response()->json($outputArray, $statusCode);
    //         } else {
    //             $id = $request->entity_id;
    //             $nearBy = EntityNearbyFilter::find($request->entity_nearby_id);
    //             $responseData = $this->getNearEntityByFilter($nearBy,$request->asset_type_id);
    //             if(empty($responseData) || $responseData->isEmpty()) {
    //                 $message = trans('apimessages.norecordsfound');
    //             } else {
    //                 $message = trans('apimessages.getting_near_by_entity');
    //             }
    //             $response =['status'=> 1, 'message' =>$message,'data' => $responseData ];
	// 	        return response()->json($response,200);
    //         }
    //     } catch (\Throwable $th) {
    //         $this->log->error($th);
    //         $outputArray['status'] = 0;
    //         $outputArray['message'] = $th->getMessage();
    //         $statusCode = 400;
    //         return response()->json($outputArray, $statusCode);
    //     }
    // }
    
    /**
     * getNearEntityByFilter
     *
     * @param  mixed $id
     * @param  mixed $nearBy
     * @return void
     */
    public function getNearEntityByFilter($nearBy, $assetTypeId = null) {
        try {
            if($nearBy) {
                $nearEntity = Business::select('business.id','business.name','business.business_slug','business.business_logo','business.category_id','business.description','business.metatags',DB::raw('(select round(AVG(rating),1) from business_ratings where business_id = business.id) as avg_rating'),DB::raw('(select COUNT(id) from business_ratings where business_id = business.id) as total_review'),DB::raw('(select name from asset_types where id = business.asset_type_id) as entity_type'))->where('approved',1)->withTrashed()->whereNull('business.deleted_at')->where('business.id','<>',$nearBy->entity_id);
                if($assetTypeId) {
                    $nearEntity->where('asset_type_id',$assetTypeId);
                }
                if(!empty($nearBy->sql_query)) {
                    $nearEntity->whereRaw($nearBy->sql_query);
                } else {
                    if(!empty($nearBy->asset_type_id)) {
                        $assetIds = explode(',',$nearBy->asset_type_id);
                        $nearEntity->whereIn('asset_type_id',$assetIds);
                    }
                    $entity = Business::select('latitude', 'longitude')->find($nearBy->entity_id);

                    if($entity && !empty($entity->latitude) && !empty($entity->longitude)) {

                        $nearEntity->addSelect(DB::Raw('( 6371 * acos( cos( radians(' . $entity->latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $entity->longitude . ') ) + sin( radians(' . $entity->latitude . ') ) * sin( radians( latitude ) ) ) ) AS distance'));

                        $nearEntity->orderBy('distance', 'ASC');
                    }
                }
                $nearEntity->limit($nearBy->top_limit);
                return $nearEntity->get();
            }
            return [];
        } catch (\Throwable $th) {
            $this->log->error("getting error when get entities near by filter:- ");
            $this->log->error($th);
            return [];
        }
    }
    
    /**
     * store suggestion  of entity description by user
     *
     * @param  mixed $request
     * @return void
     */
    public function suggestDescription(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required_if:entity_know_more_id,==,""',
                'description' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while sugget description');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $entityId = $request->entity_id;
                $knowMoreId = $request->entity_know_more_id;
                $userId = auth()->id();
                $data['user_id'] = $userId;
                if($entityId > 0) {
                    $data['entity_id'] = $entityId;
                } else {
                    $data['entity_know_more_id'] = $knowMoreId;
                }
                $data['description'] = $request->description;
                EntityDescriptionSuggestion::create($data);
                $response =['status'=> 1, 'message' =>trans('apimessages.submitted_description_suggestion')];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error("Getting error while suggest description");
            $this->log->error($th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * use for report of entity or site
     *
     * @param  mixed $request
     * @return void
     */
    public function reportEntity(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required',
                'reason_id' => 'required|array|max:3'
            ],['reason_id.max' => 'You can select maximum 3 reasons only']);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while report entity');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $userId = auth()->id();
                $type = $request->input('type','entity');
                if($type == 'site') {                    
                    $data['site_id'] = $request->entity_id;
                } else {
                    $data['entity_id'] = $request->entity_id;
                }
                $data['report_by'] = $userId;
                $reportCount = EntityReport::where($data)->count();
                if($reportCount >= 5) {
                    $response =['status'=> 0, 'message' =>trans('apimessages.max_limit_report_reasons')];
		            return response()->json($response,200);
                } 
                $data['comment'] = $request->comment;
                $data['asset_type_id'] = $request->asset_type_id;
                $entityReport = EntityReport::create($data);
                $entityReport->reasons()->attach($request->reason_id);
                $response =['status'=> 1, 'message' =>trans('apimessages.submitted_report_reasons')];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error('getting error while reporting to entity:- '.$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * report post
     *
     * @param  mixed $request
     * @return void
     */
    public function reportPost(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
                'asset_type_id' => 'required',
                'reason_id' => 'required|array|max:3'
            ],['reason_id.max' => 'You can select maximum 3 reasons only']);
            if ($validator->fails())
            {
                Log::error('API validation failed while report public post');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $userId = auth()->id();
                $postId = $request->post_id;
                $data['post_id'] = $postId;
                $data['report_by'] = $userId;

                $query = $data ;
                $data['comment'] = $request->comment;
                $data['asset_type_id'] = $request->asset_type_id;
                $publicPostReport = EntityReport::updateOrCreate($query, $data);
                $publicPostReport->reasons()->sync($request->reason_id);
                $reportCount = EntityReport::where('post_id',$postId)->count();
                if($reportCount > 3) {
                    $post = PublicPost::find($postId);
                    $post->status = 'inactive';
                    $post->save();
                }
                $response =['status'=> 1, 'message' =>trans('apimessages.submitted_report_reasons')];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            Log::error('getting error while reporting to public post:- '.$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * getSponsoredEntity
     *
     * @param  mixed $request
     * @return void
     */
    public function getSponsoredEntity(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'entity_types' => 'required|array'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while report entity');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $data = [];
                foreach($request->entity_types as $entityType) {
                    $entities = $this->getPromotedEntity($entityType);
                    if(!empty($entities)) {
                        $data[$entityType] = $entities;
                    }
                }
                if(empty($data)) {
                    $message = trans('apimessages.norecordsfound');
                } else {
                    $message = trans('apimessages.getting_sponsoreed_entity');
                }
                $response =['status'=> 1, 'message' =>$message,'data' => $data ];
                return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error("getting error when sponsored entity:- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * getEntityVideo
     *
     * @param  mixed $request
     * @return void
     */
    public function getEntityVideo(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while get entity video');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $videos = EntityVideo::select('id','title','thumbnail','description')->where('entity_id',$request->entity_id)->get()->makeHidden(['thumbnail','video_url']);

                if($videos->isEmpty()) {
                    $message = trans('apimessages.norecordsfound');
                } else {
                    $message = trans('apimessages.getting_video_detail');
                }
                $response =['status'=> 1, 'message' =>$message,'data' => $videos];
                return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error("getting error when get entity videos:- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * getVideoById
     *
     * @param  mixed $request
     * @return void
     */
    public function getVideoById(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails())
            {
                $this->log->error('API validation failed while get video');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $data = [];
                $video = EntityVideo::find($request->id);
                if($video) {
                    $data = $video;
                    $title = strtok($video->title," ");
                    $relatedVideos = EntityVideo::where('id','<>',$video->id)->where('title','like','%'.$title.'%')->limit(10)->get();
                    $data['relatedVideos'] = $relatedVideos;
                }
                if(empty($data)) {
                    $message = trans('apimessages.norecordsfound');
                } else {
                    $message = trans('apimessages.getting_video_detail');
                }
                $response =['status'=> 1, 'message' =>$message,'data' => $data];
                return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            $this->log->error("getting error when get video detail:- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }


    public function searchAutocomplete(Request $request)
    {
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;
        $requestData = Input::all();
        try
        {
            $listArray = [];

            if(isset($requestData['searchText']) && $requestData['searchText'] != '')
            { 
                $tagArray = [];
                $categoryArray = [];
                $BusinessArray = [];
                $Businesslist = $this->objBusiness->getAll(array('autoCompleteText'=>$requestData['searchText']));

                // return $Businesslist;
                if(count($Businesslist) > 0)
                {
                    foreach ($Businesslist as $key => $value) {
                        $listArray = [];
                        $listArray['id'] = trim($value->id);
                        $listArray['name'] = trim($value->name);
                        $listArray['category'] = trim($value->entityType->name); 
                        $BusinessArray[] = $listArray; 
                    }
                } 
                  
                $responseData['status'] = 1;
                $responseData['message'] =  trans('apimessages.data_found');;
                $responseData['data'] = $BusinessArray;  
                $statusCode = 200;
            }
            else
            {
                $this->log->error('Something wrong when doing AutoComplete');
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = [];
                $statusCode = 200;
            }

        } catch (Exception $e) {
            $this->log->error('Something wrong when doing AutoComplete', array('error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        
        return response()->json($responseData, $statusCode);
    }

    public function searchBusinesses(Request $request)
    {
        $loginUserId = 0;
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $loginUserId = $user->id;
            \Log::info("Search By User:" . $loginUserId);
        } catch (TokenInvalidException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTAuthException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (JWTException $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        } catch (Exception $e) {
            $this->log->info('Not logged in user');
            $loginUserId = 0;
        }
        $headerData = (!empty($request->header('Platform'))) ? $request->header('Platform') : '';
        $searchCity = '';
        try {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 1;
            $filters = [];
            
            if (isset($request->city) && $request->city != '') {
                if ($request->city != 'All Locations') {
                    $filters['city'] = $request->city;
                    $searchCity = $request->city;
                }
            } else if (!empty($request->location)) {
                $filters['city'] = $request->location;
                $searchCity = $request->location;
            }

            if (isset($request->searchText) && $request->searchText != '') {
                $filters['searchText'] = $request->searchText;
            }

            \Log::info("Search For ==> City:" . $request->city . " SearchText:" . $request->searchText);

            if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM')) {

                if (isset($request->limit) && !empty($request->limit) && isset($request->page)) {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                } elseif (isset($request->limit) && !empty($request->limit)) {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                } elseif (isset($request->page)) {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            } elseif (!empty($headerData) && $headerData == Config::get('constant.MOBILE_PLATFORM')) {

                if (isset($request->limit) && !empty($request->limit) && isset($request->page)) {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = $request->limit;
                    $filters['skip'] = $offset;
                } elseif (isset($request->limit) && !empty($request->limit)) {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                } elseif (isset($request->page)) {
                    $offset = Helpers::getOffset($pageNo);
                    $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            } else {
                $offset = Helpers::getOffset($pageNo);
                $filters['offset'] = $offset;
                $filters['take'] = Config::get('constant.API_RECORD_PER_PAGE');
            }

            if (isset($request->sortBy) && !empty($request->sortBy)) {
                $filters['sortBy'] = $request->sortBy;

                if (isset($request->latitude) && !empty($request->latitude) && isset($request->longitude) && !empty($request->longitude)) {
                    $filters['latitude'] = $request->latitude;
                    $filters['longitude'] = $request->longitude;
                }

                if (isset($request->radius) && !empty($request->radius)) {

                    //$filters['radius'] = $request->radius;
                }
            }
 
            $time1 = intval(microtime(true) * 1000);

            $searchFilters = [];
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $searchFilters['searchText'] = $filters['searchText'];
            }

            $searchFilters['approved'] = 1;
            if (isset($filters['city']) && $filters['city'] != '') {
                $searchFilters['city'] = $filters['city'];
            }
            if (isset($request->limit) && $request->limit != '') { 
                $filters['limit'] = $request->limit;                 
            }
            // $businessAllListing = $this->objBusiness->getAllForFrontAndMobileApp($searchFilters);
            $businessesCount = $this->objBusiness->getAllCountForFrontAndMobileApp($searchFilters);

            $businessListing = $this->objBusiness->getAllForFrontAndMobileApp($filters);
            $metatagslist = $this->objBusiness->getAllTags();

            // return($metatagslist);

            $time2 = intval(microtime(true) * 1000);
            \Log::info("Search Query Execution time:=== " . ($time2 - $time1) . 'ms');
            $outputArray['status'] = 1;
            $outputArray['message'] = trans('apimessages.business_fetched_successfully');
            $statusCode = 200;

            $outputArray['data'] = array();
            $outputArray['data']['businesses'] = array();
            $isBusinessFound = count($businessListing);
            \Log::info("Total Business Found:=== " . $isBusinessFound);

            TempSearchTerm::create([
                'search_term' => $request->searchText,
                'city' => $searchCity,
                'user_id' => $loginUserId,
                'result_count' => $isBusinessFound,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'ip_address' =>  $request->ip()
            ]);
            // if($businessListing && count($businessListing) > 0)
            if ($isBusinessFound > 0) {

                $outputArray['businessesTotalCount'] = (isset($businessesCount) && $businessesCount > 0) ? $businessesCount : 0;

                if ($headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    $take = (isset($request->take) && $request->take > 0) ? $request->take : Config::get('constant.WEBSITE_RECORD_PER_PAGE');

                    // if($businessListing->count() < $take)
                    if ($isBusinessFound < $take) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $perPageCnt = $request->page * $take;

                        if ($businessesCount > $perPageCnt) {
                            $outputArray['loadMore'] = 1;
                        } else {
                            $outputArray['loadMore'] = 0;
                        }
                    }
                } else {
                    // if($businessListing->count() < Config::get('constant.API_RECORD_PER_PAGE'))
                    if ($isBusinessFound < Config::get('constant.API_RECORD_PER_PAGE')) {
                        $outputArray['loadMore'] = 0;
                    } else {

                        $take = (isset($request->take) && $request->take > 0) ? $request->take : Config::get('constant.API_RECORD_PER_PAGE');

                        //if($businessListing->count() < $take)
                        if ($isBusinessFound <  $take) {
                            $outputArray['loadMore'] = 0;
                        } else {
                            $perPageCnt = $request->page * $take;
                            if ($businessesCount > $perPageCnt) {
                                $outputArray['loadMore'] = 1;
                            } else {
                                $outputArray['loadMore'] = 0;
                            }
                        }
                    }
                }
                //return $isBusinessFound;
                for ($i = 0; $i < $isBusinessFound; $i++) {
                    $businessValue = $businessListing[$i];

                    $owners = $businessValue->owners;
                    $user = $businessValue->user;
                    $businessImagesById = $businessValue->businessImagesById;
                    $business_logo = $businessValue->business_logo;

                    unset($businessValue->owners);
                    unset($businessValue->user);
                    unset($businessValue->businessImagesById);
                    unset($businessValue->business_logo); 
                    $outputArray['data']['businesses'][$i]['id'] = $businessValue->id;
                    $outputArray['data']['businesses'][$i]['name'] = $businessValue->name;
                    $outputArray['data']['businesses'][$i]['distance'] = $businessValue->distance;
                    $outputArray['data']['businesses'][$i]['tag'] = $businessValue->metatags;
                    $outputArray['data']['businesses'][$i]['business_images'] = $businessValue->businessImages;

                    //$outputArray['data']['businesses'][$i]['business_slug'] = (isset($businessValue->business_slug) && !empty($businessValue->business_slug)) ? $businessValue->business_slug : '';
                    $outputArray['data']['businesses'][$i]['business_slug'] = $businessValue->business_slug;

                    $outputArray['data']['businesses'][$i]['address'] = $businessValue->address;
                    //$outputArray['data']['businesses'][$i]['document_approval'] = (($businessValue->document_approval))? url(Config::get('constant.VERIFIED_IMAGE')) :  url(Config::get('constant.NON_VERIFIED_IMAGE'));;

                    if ($businessValue->document_approval == 3) {
                        $outputArray['data']['businesses'][$i]['document_approval'] =  url(Config::get('constant.VERIFIED_IMAGE'));
                    } elseif ($businessValue->document_approval == 2) {
                        $outputArray['data']['businesses'][$i]['document_approval'] =    url(Config::get('constant.NON_VERIFIED_IMAGE2'));
                    } elseif ($businessValue->document_approval == 1) {

                        $outputArray['data']['businesses'][$i]['document_approval'] =  url(Config::get('constant.NON_VERIFIED_IMAGE'));
                    }
                    $outputArray['data']['businesses'][$i]['street_address'] = $businessValue->street_address;
                    $outputArray['data']['businesses'][$i]['locality'] = $businessValue->locality;
                    $outputArray['data']['businesses'][$i]['country'] = $businessValue->country;
                    $outputArray['data']['businesses'][$i]['state'] = $businessValue->state;
                    $outputArray['data']['businesses'][$i]['city'] = $businessValue->city;
                    $outputArray['data']['businesses'][$i]['taluka'] = $businessValue->taluka;
                    $outputArray['data']['businesses'][$i]['district'] = $businessValue->district;
                    $outputArray['data']['businesses'][$i]['pincode'] = $businessValue->pincode;
                    $outputArray['data']['businesses'][$i]['latitude'] = $businessValue->latitude;
                    $outputArray['data']['businesses'][$i]['longitude'] = $businessValue->longitude;
                    // $outputArray['data']['businesses'][$i]['latitude'] = (!empty($businessValue->latitude)) ? $businessValue->latitude : 0;
                    // $outputArray['data']['businesses'][$i]['longitude'] = (!empty($businessValue->longitude)) ? $businessValue->longitude : 0;
                    $outputArray['data']['businesses'][$i]['website_url'] = $businessValue->website_url;
                    $outputArray['data']['businesses'][$i]['membership_type'] = $businessValue->membership_type;
                    $outputArray['data']['businesses'][$i]['is_normal_view'] = $businessValue->is_normal_view;
                    $outputArray['data']['businesses'][$i]['entity_type'] = $businessValue->entity_type;

                    $outputArray['data']['businesses'][$i]['user_id'] = (isset($user) &&  !empty($user->id)) ? $user->id : '';
                    $outputArray['data']['businesses'][$i]['user_name'] = (isset($user) && !empty($user->name)) ? $user->name : '';

                    $oCount = count($owners->toArray());
                    if ($oCount > 0) {
                        unset($outputArray['data']['businesses'][$i]['owners']);
                        $full_name = array_column($owners->toArray(), 'full_name');
                        $outputArray['data']['businesses'][$i]['owners'] = implode(', ', $full_name);
                    }

                    $outputArray['data']['businesses'][$i]['membership_type_icon'] = $businessValue->membership_type_icon;

                    $categoryData = $this->getBusinessParentCategory($businessValue->parent_category);
                    $outputArray['data']['businesses'][$i]['parent_categories'] = $categoryData;
                    $outputArray['data']['businesses'][$i]['categories_name_list'] = (!empty($categoryData)) ? implode(', ', $categoryData->pluck('category_name')->toArray()) : '';

                    $s3url =   Config::get('constant.s3url');
                    $categoryForImage = null;
                    $categoryLogo = (!empty($categoryData)) ? $categoryData[0]->category_logo : url(Config::get('constant.DEFAULT_IMAGE'));
                    $imgThumbUrl = (((isset($businessImagesById) && !empty($businessImagesById) && !empty($businessImagesById->image_name)) && $s3url . Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $businessImagesById->image_name) ? $s3url . Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $businessImagesById->image_name : empty($categoryForImage)) ? url(Config::get('constant.DEFAULT_IMAGE')) : $categoryLogo;
                    $outputArray['data']['businesses'][$i]['business_image'] = $imgThumbUrl;

                    $businessLogoThumbImgPath = ((isset($business_logo) && !empty($business_logo)) && $s3url . Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $business_logo && $s3url . Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $business_logo) ? $s3url . Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH') . $business_logo : $imgThumbUrl;
                    $outputArray['data']['businesses'][$i]['logo_thumbnail'] = $businessLogoThumbImgPath;
                    
                }
                $outputArray['data'] ['metatagslist']= $metatagslist;

                $i = 0;
            } else {
                $this->log->info('API getBusinessListingByCatId no records found');
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $outputArray['loadMore'] = 0;
                $statusCode = 200;
                $outputArray['data'] = new \stdClass();
            }
            
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e) {
            $this->log->error('API something went wrong while getBusinessListingByCatId');
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    
}
