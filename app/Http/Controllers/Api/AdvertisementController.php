<?php
                                    
namespace App\Http\Controllers\Api;

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
use Tymon\JWTAuth\Exceptions\JWTAuthException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Cache;
use Storage;
use \stdClass;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Cviebrock\EloquentSluggable\Services\SlugService;

use App\Helpers\ImageUpload;
use App\User;
use App\Category;
use App\Advertisement;
use App\AdvertisementCategory;
use App\AdvertisementImage;
use App\AdvertisementVideo;
use App\UserInterestInAdvertisement;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    public function __construct()
    {
        $this->objAdvertisement = new Advertisement();
        $this->objAdvertisementCategory = new AdvertisementCategory();
        $this->objAdvertisementImage = new AdvertisementImage();
        $this->objAdvertisementVideo = new AdvertisementVideo();
        $this->objUser = new User();
        $this->objCategory = new Category();
        $this->objUserInterestInAdvertisement = new UserInterestInAdvertisement();

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
        $this->log->pushHandler(new StreamHandler(storage_path() . '/logs/monolog-' . date('m-d-Y') . '.log'));

        $this->catgoryTempImage = Config::get('constant.CATEGORY_TEMP_PATH');
    }

    /**
     * Add or update the Advertisement for authenticated user.
     *
     */
    public function addOrUpdateAds(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $adsData = [];
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'ads_type' => ['required', 'digits_between:0,2'],
            /** 0 - Buy, 1 - Sell, 2 - Service */
            'name' => ['required', 'max:100'],
            'descriptions' => ['required', 'max:2000'],
            // 'category_id.*' => ['required'],
            // 'parent_category_id.*' => ['required'],
            // 'image_name.*' => ['mimes:pdf,png,jpeg,jpg,bmp,gif|max:5120'],
            'video_link.*' => ['url'],
        ]);

        if ($validator->fails()) {
            $this->log->error('API validation failed while saving advertisement ', array('login_user_id' => $user->id));
            $outputArray['status'] = 0;
            $outputArray['message'] = $validator->messages()->all()[0];
            $statusCode = 200;
            return response()->json($outputArray, $statusCode);
        }

        try {
            $adsData = $requestData;
            $adsData['user_id'] = $user->id;
            $adsData['id'] = 0;
            if (isset($requestData['id']) && $requestData['id'] > 0) {
                $adsData['id'] = $requestData['id'];
                $adsData['approved'] = 0;
            }

            if (isset($requestData['name'])) {
                $adsData['name'] = $requestData['name'];
            }

            if (isset($requestData['name'])) {
                $adsData['name'] = $requestData['name'];
                $adsName = trim($adsData['name']);
                if ($adsData['id'] == 0) {
                    $adsSlug = SlugService::createSlug(Advertisement::class, 'ads_slug', $adsName);
                    $adsData['ads_slug'] = (isset($adsSlug) && !empty($adsSlug)) ? $adsSlug : null;
                }
            }

            if (isset($requestData['latitude']) && isset($requestData['longitude'])) {
                $adsData['latitude'] = $requestData['latitude'];
                $adsData['longitude'] = $requestData['longitude'];
            }

            if (isset($requestData['id']) && $requestData['id'] != "") {
                unset($adsData['approved']);
                unset($adsData['approved_by']);
            }

            if ($request->get('category_id')) {
                $tmpCategoryIdArray = $request->get('category_id');
                if (isset($tmpCategoryIdArray) && count($tmpCategoryIdArray) > 0 && !empty($tmpCategoryIdArray)) {
                    $commaSeparatedIds = implode(',', $tmpCategoryIdArray);
                    $adsData['category_hierarchy'] = Helpers::getCategoryHierarchy($commaSeparatedIds);
                }
            }

            DB::beginTransaction();
            $adsSaveData = $this->objAdvertisement->insertUpdate($adsData);

            if ($adsSaveData) {
                $adsId = $adsSaveData->id;
                $advertisementDetails = Advertisement::find($adsId);

                if ($advertisementDetails) {
                    if ($request->get('parent_category_id')) {
                        $categoryIdArray = $request->get('parent_category_id');
                        if (isset($categoryIdArray) && count($categoryIdArray) > 0 && !empty($categoryIdArray)) {
                            $this->objAdvertisementCategory->removeCategoriesByIds($adsId, $categoryIdArray, 0);
                            foreach ($categoryIdArray as $categoryIdValue) {
                                if ($categoryIdValue > 0) {
                                    $this->saveCategory($adsId, $categoryIdValue, 0);
                                }
                            }
                        } else {
                            $this->objAdvertisementCategory->removeCategoriesByIds($adsId, [], 0);
                        }
                    } else {
                        $this->objAdvertisementCategory->removeCategoriesByIds($adsId, [], 0);
                    }

                    if ($request->get('category_id')) {
                        $categoryIdArray = $request->get('category_id');
                        if (isset($categoryIdArray) && count($categoryIdArray) > 0 && !empty($categoryIdArray)) {
                            $this->objAdvertisementCategory->removeCategoriesByIds($adsId, $categoryIdArray, 1);
                            foreach ($categoryIdArray as $categoryIdValue) {
                                if ($categoryIdValue > 0) {
                                    $this->saveCategory($adsId, $categoryIdValue, 1);
                                }
                            }
                        } else {
                            $this->objAdvertisementCategory->removeCategoriesByIds($adsId, [], 1);
                        }
                    } else {
                        $this->objAdvertisementCategory->removeCategoriesByIds($adsId, [], 1);
                    }

                    if ($request->get('video_link')) {
                        $videoLinkArray = $request->get('video_link');

                        $totalLinksTobeSaved = count($videoLinkArray);

                        if (isset($videoLinkArray) && $totalLinksTobeSaved > 0 && !empty($videoLinkArray)) {
                            /** Step 1: Find all the links which are not sent and it's deleted. so need to remove those links 1st */
                            $this->objAdvertisementVideo->removeVideos($adsId, $videoLinkArray);

                            /** Step 2: Find all the links which are saved, and update those link accordingly. */
                            $totalSavedVideoLinks = $this->objAdvertisementVideo->updateVideos($adsId, $videoLinkArray);

                            /** Step 3: Now check if new links are sent for new entry. Than remove saved urls and add new URL */
                            if ($totalSavedVideoLinks < $totalLinksTobeSaved) {
                                $videoLinkArray = array_slice($videoLinkArray, $totalSavedVideoLinks);
                            }

                            /** Step 4: Finally save remaining urls. */
                            foreach ($videoLinkArray as $videoLinkKey => $videoLinkValue) {
                                if ($videoLinkValue != "") {
                                    $this->objAdvertisementVideo->AddVideo($adsId, $videoLinkValue, 0);
                                }
                            }
                        } else {
                            $this->objAdvertisementVideo->removeAllVideos($adsId);
                        }
                    } else {
                        $this->objAdvertisementVideo->removeAllVideos($adsId);
                    }

                    $outputArray['status'] = 1;
                    if (isset($requestData['id']) && $requestData['id'] != "") {
                        $outputArray['message'] = trans('apimessages.ads_updated_success');
                    } else {
                        $outputArray['message'] = trans('apimessages.ads_added_success');
                    }

                    $outputArray['data'] = array();
                    $outputArray['data']['id'] = $advertisementDetails->id;
                    $outputArray['data']['ads_slug'] = $advertisementDetails->ads_slug;
                    $statusCode = 200;
                } else {
                    DB::rollback();
                    $this->log->error('API something went wrong while save business', array('login_user_id' => $user->id));
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
                DB::commit();
            } else {
                DB::rollback();
                $this->log->error('API something went wrong while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            DB::rollback();

            $this->log->error('API something went wrong while save business', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();

            $statusCode = $e->getStatusCode();

            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    protected function saveCategory($adsId, $categoryId, $categoryType)
    {
        $filter = [
            ['category_id', '=', $categoryId],
            ['category_type', '=', $categoryType],
            /** 0 - Parent, 1 - Child */
            ['advertisement_id', '=', $adsId]
        ];
        $category = $this->objAdvertisementCategory->filterSingleObect($filter);

        if (!$category) {
            $categoryInsert = [
                'advertisement_id' => $adsId,
                'category_id' => $categoryId,
                'category_type' => $categoryType
            ];
            $response = $this->objAdvertisementCategory->insertUpdate($categoryInsert);
        }
    }

    protected function getAdsByIdForView(Request $request)
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
        $outputArray = [];
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'advertisement_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->log->error('API validation failed while retriving advertisement ', array('login_user_id' => $loginUserId));
            $outputArray['status'] = 0;
            $outputArray['message'] = $validator->messages()->all()[0];
            $statusCode = 200;
            return response()->json($outputArray, $statusCode);
        }

        try {

            $adsSaveData = $this->objAdvertisement->frontendFindById($request->advertisement_id, true, $loginUserId);

            // \Log::info($adsSaveData);
            
            if ($adsSaveData) {
                /**
                 * We need to increase view count if owner is not viewing the Ads.
                 */
                if($loginUserId != $adsSaveData->user_id)
                {
                    $adsSaveData->visit_count = $adsSaveData->visit_count + 1;
                    $this->objAdvertisement->updateViewCount($adsSaveData->id);
                }

                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');



                if ($adsSaveData["owner_profile_image"] != '' && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$adsSaveData["owner_profile_image"])) {
                        $s3url =   Config::get('constant.s3url');
                        // return $s3url;
                         $adsimageName = $s3url.Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$adsSaveData["owner_profile_image"];
                    }else{
                        $adsimageName = url(Config::get('constant.DEFAULT_IMAGE'));
                    }
                            $adsSaveData["owner_profile_image"] = $adsimageName;

                // $adsSaveData["owner_profile_image"] = (($adsSaveData["owner_profile_image"] != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$adsSaveData["owner_profile_image"]) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH').$adsSaveData["owner_profile_image"]) : url(Config::get('constant.DEFAULT_IMAGE'));

                $outputArray['data'] = $adsSaveData->toArray();
                $outputArray['data']['default_image'] = url(Config::get('constant.RYEC_DEFAULT_BANNER_IMAGE'));

                $outputArray['data']['category_hierarchy'] = array();
                if($adsSaveData->child_category_ids && !empty($adsSaveData->child_category_ids))
                {
                    $explodeCategories = explode(',', $adsSaveData->child_category_ids);
                    foreach($explodeCategories as $categoryKey => $categoryValue)
                    {
                        if(!empty($categoryValue) && $categoryValue > 0)
                        {
                            $categories = Helpers::getCategoryReverseHierarchy($categoryValue);
                            if($categories && count($categories) > 0)
                            {
                                $outputArray['data']['category_hierarchy'][] = array_reverse($categories);
                            }
                        }
                    }
                }

                if($adsSaveData->user_id === $loginUserId) {
                    $outputArray['data']['interest'] = array();

                    $interestDetails = $adsSaveData->userInterestInAdvertisement()
                                        ->join('chats', function($join) {
                                            $join->on('chats.advertisement_id', '=', 'user_interest_in_advertisement.advertisement_id');
                                            $join->on('chats.customer_id', '=', 'user_interest_in_advertisement.user_id');
                                        })
                                        ->where('chats.id', '>', 0)
                                        ->orderBy('user_interest_in_advertisement.id', 'DESC')
                                        ->limit(Config::get('constant.BUSINESS_DETAILS_RATINGS_LIMIT'))
                                        ->select("user_interest_in_advertisement.*", 'chats.id AS thread_id')
                                        ->get();
                    if ($interestDetails) {
                        foreach ($interestDetails as $interestKey => $interestValue) {
                            if ($interestValue->thread_id != "" &&  $interestValue->thread_id > 0) {
                                $outputArray['data']['interest'][$interestKey]['id'] = $interestValue->id;
                                $outputArray['data']['interest'][$interestKey]['timestamp'] = (!empty($interestValue->updated_at)) ? strtotime($interestValue->updated_at)*1000 : '';
                                $outputArray['data']['interest'][$interestKey]['name'] = (isset($interestValue->user) && !empty($interestValue->user->name)) ? $interestValue->user->name : '';

                                $imgThumbUrl = '';


                                if ((isset($interestValue->user) && !empty($interestValue->user->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic)) {
                                    $s3url =   Config::get('constant.s3url');
                                    // return $s3url;
                                   $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic;
                              }else{
                                  $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                              }

                          // $imgThumbUrl = ((isset($interestValue->user) && !empty($interestValue->user->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                                $outputArray['data']['interest'][$interestKey]['image_url'] = $imgThumbUrl;
                                // $outputArray['data']['interest'][$interestKey]['user_id'] = $interestValue->user_id;

                                $outputArray['data']['interest'][$interestKey]['country_code'] = $interestValue->user->country_code;
                                $outputArray['data']['interest'][$interestKey]['phone_number'] = $interestValue->user->phone;
                                $outputArray['data']['interest'][$interestKey]['thread_id'] = $interestValue->thread_id;
                                $outputArray['data']['thread_id'] = $interestValue->thread_id;
                                $outputArray['data']['interest'][$interestKey]['comment'] = $interestValue->comment;
                                $outputArray['data']['interest'][$interestKey]['user_business_id'] = (isset($interestValue->user->singlebusiness) && $interestValue->user->singlebusiness->id != '')? (string)$interestValue->user->singlebusiness->id : '';
                                $outputArray['data']['interest'][$interestKey]['user_business_slug'] = (isset($interestValue->user->singlebusiness) && $interestValue->user->singlebusiness->business_slug != '')? (string)$interestValue->user->singlebusiness->business_slug : '';
                            }
                        }
                    }
                } else {
                    $interestDetails = $adsSaveData->userInterestInAdvertisement()
                                        ->join('chats', function($join) {
                                            $join->on('chats.advertisement_id', '=', 'user_interest_in_advertisement.advertisement_id');
                                            $join->on('chats.customer_id', '=', 'user_interest_in_advertisement.user_id');
                                        })
                                        ->withTrashed()
                                        ->where('chats.id', '>', 0)
                                        ->orderBy('user_interest_in_advertisement.id', 'DESC')
                                        ->select('chats.id AS thread_id')
                                        ->first();
                    if ($interestDetails) {
                        $outputArray['data']['thread_id'] = $interestDetails->thread_id;
                    }
                }
                $statusCode = 200;
            } else {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    protected function getAdsByIdForEdit(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $outputArray = [];
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'advertisement_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->log->error('API validation failed while retriving advertisement ', array('login_user_id' => $user->id));
            $outputArray['status'] = 0;
            $outputArray['message'] = $validator->messages()->all()[0];
            $statusCode = 200;
            return response()->json($outputArray, $statusCode);
        }

        try {
            $adsSaveData = $this->objAdvertisement->frontendFindById($request->advertisement_id);

            if ($adsSaveData) {
                $this->log->error('API something went wrong while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');

                $outputArray['data'] = $adsSaveData;
                $statusCode = 200;
            } else {
                $this->log->error('API something went wrong while save business', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete the Advertisement Images.
     */
    public function deleteAdsImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();
        try {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'advertisement_id' => ['required', 'exists:advertisements,id'],
                'image_ids.*' => ['required']
            ]);

            if ($validator->fails()) {

                DB::rollback();
                $this->log->error('API validation failed while delete ads images', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $imageIds = (isset($requestData['image_ids']) ? $requestData['image_ids'] : []);

            if (count($imageIds) > 0) {
                $advertisementId = (isset($requestData['advertisement_id']) && $requestData['advertisement_id'] > 0) ? $requestData['advertisement_id'] : 0;
                foreach ($imageIds as $imageId) {
                    $AdvertisementImageData = AdvertisementImage::find($imageId);

                    if ($AdvertisementImageData) {
                        $response = AdvertisementImage::where('id', $imageId)->where('advertisement_id', $advertisementId)->delete();

                        $AdvertisementImageName = $AdvertisementImageData->image_name;
                        $pathOriginal = $this->ADVERTISEMENT_ORIGINAL_IMAGE_PATH . $AdvertisementImageName;
                        $pathThumb = $this->ADVERTISEMENT_THUMBNAIL_IMAGE_PATH . $AdvertisementImageName;

        //              Delete Image From Storage
                        $originalImageDelete = Helpers::deleteFileToStorage($AdvertisementImageName, $this->ADVERTISEMENT_ORIGINAL_IMAGE_PATH, $this->ADVERTISEMENT_STORAGE_TYPE);
                        $thumbImageDelete = Helpers::deleteFileToStorage($AdvertisementImageName, $this->ADVERTISEMENT_THUMBNAIL_IMAGE_PATH, $this->ADVERTISEMENT_STORAGE_TYPE);

        //              Deleting Local Files
                        File::delete($pathOriginal, $pathThumb);
                    }
                }

                DB::commit();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.advertisement_image_deleted_successfully');
                $statusCode = 200;
            } else {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while delete advertiment image', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete the Advertisement video.
     */
    public function deleteAdsVideoLink(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];

        $requestData = $request->all();

        try {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'advertisement_id' => ['required', 'exists:advertisements,id'],
                'video_ids.*' => 'required'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                $this->log->error('API validation failed while delete ads video link', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $videoIds = $requestData['video_ids'];
            if(count($videoIds) > 0)

            $advertisementId = (isset($requestData['advertisement_id']) && $requestData['advertisement_id'] > 0) ? $requestData['advertisement_id'] : 0;
            $response = $this->objAdvertisementVideo->removeVideosByIds($advertisementId, $videoId);

            if ($response) {
                DB::commit();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.advertisement_video_deleted_successfully');
                $statusCode = 200;
            } else {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while delete advertiment video link', array('login_user_id' => $user->id, 'error' => $e->getMessage()));
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    protected function getMyAdvertisement(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $filters = $request->all();

        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;

        try {
            $filters['user_id'] = $user->id;
            $filters['logged_in_user_id'] = $user->id;

            if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                if (isset($request->limit) && !empty($request->limit)) {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                } elseif (isset($request->page) && !empty($request->page)) {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                if (isset($request->sortBy) && !empty($request->sortBy)) {
                    if ($request->sortBy == 'popular') {
                        $filters['sortBy'] = 'popular';
                    }
                }
            } else {
                $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                $offset = Helpers::getOffset($pageNo, $take);
                $filters['take'] = $take;
                $filters['skip'] = $offset;
            }
            $adsSaveData = $this->objAdvertisement->getFilterDataForFrontend($filters);
            $totalCount = $this->objAdvertisement->getFilterDataCountForFrontend($filters);

            if ($adsSaveData) {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');
                $outputArray['totalCount'] = $totalCount;

                if ($headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    if ($totalCount < Config::get('constant.WEBSITE_RECORD_PER_PAGE')) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $outputArray['loadMore'] = (count($totalCount) > 0) ? 1 : 0;
                    }
                } else {
                    $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                    if ($totalCount < $take) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $perPageCnt = $pageNo * $take;
                        if($totalCount > $perPageCnt) {
                            $outputArray['loadMore'] = 1;
                        } else {
                            $outputArray['loadMore'] = 0;
                        }
                    }
                }

                $tmpCount = count($adsSaveData);
                for($index = 0; $index < $tmpCount; $index++) {
                    if(isset($adsSaveData[$index]["advertisementImages"])) {
                          $tmpImgCount = count($adsSaveData[$index]["advertisementImages"]);

                        for($iIndex = 0; $iIndex < $tmpImgCount; $iIndex++) {
                              $imageName = $adsSaveData[$index]["advertisementImages"][$iIndex]["image_name"];
                              
                              if (!empty($imageName) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName)) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $imgORIGINAL = $s3url.Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName;
                          }else{
                              $imgORIGINAL = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["original_url"] = $imgORIGINAL;

                          if (!empty($imageName) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName)) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $imgTHUMBNAIL = $s3url.Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName;
                          }else{
                              $imgTHUMBNAIL = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["thumbnail_url"] = $imgTHUMBNAIL;
              }
          }
      }

                $outputArray['data']["ads"] = $adsSaveData;
                $statusCode = 200;
            } else {
                $this->log->error('Advertisement now found', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Retrive all the approved Advertisement along with filters
     */
    protected function getAllAdvertisement(Request $request)
    {

        //\Log::info('Advertisement finding'.$request);
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
        $filters = $request->all();

        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;

        try {
            /** We only need to display approved business */
            $filters['approved'] = 1;
            if($loginUserId != 0) {
                $filters['logged_in_user_id'] = $loginUserId;
            }           
             $filters['is_closed'] = 0;

            if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                if (isset($request->limit) && !empty($request->limit)) {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                } elseif (isset($request->page) && !empty($request->page)) {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                if (isset($request->sortBy) && !empty($request->sortBy)) {
                    if ($request->sortBy == 'popular') {
                        $filters['sortBy'] = 'popular';
                    }
                }
            } else {
                $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                $offset = Helpers::getOffset($pageNo, $take);
                $filters['take'] = $take;
                $filters['skip'] = $offset;
            }
            $adsSaveData = $this->objAdvertisement->getFilterDataForFrontend($filters);    
            $totalCount = $this->objAdvertisement->getFilterDataCountForFrontend($filters);

            if ($adsSaveData) {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');
                $outputArray['totalCount'] = $totalCount;

                if ($headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    if ($totalCount < Config::get('constant.WEBSITE_RECORD_PER_PAGE')) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $outputArray['loadMore'] = (count($totalCount) > 0) ? 1 : 0;
                    }
                } else {
                    $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                    if ($totalCount < $take) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $perPageCnt = $pageNo * $take;
                        if($totalCount > $perPageCnt) {
                            $outputArray['loadMore'] = 1;
                        } else {
                            $outputArray['loadMore'] = 0;
                        }
                    }
                }

                $tmpCount = count($adsSaveData);
                for($index = 0; $index < $tmpCount; $index++) {
                    if(isset($adsSaveData[$index]["advertisementImages"])) {
                        $tmpImgCount = count($adsSaveData[$index]["advertisementImages"]);

                        for($iIndex = 0; $iIndex < $tmpImgCount; $iIndex++) {
                            $imageName = $adsSaveData[$index]["advertisementImages"][$iIndex]["image_name"];
                            if (!empty($imageName) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName)) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $adsimageName = $s3url.Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName;
                          }else{
                              $adsimageName = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["original_url"] = $adsimageName;

                           if (!empty($imageName) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName)) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $adsthumbnailimageName = $s3url.Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName;
                          }else{
                              $adsthumbnailimageName = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["thumbnail_url"] = $adsthumbnailimageName;

                  // $adsSaveData[$index]["advertisementImages"][$iIndex]["original_url"] = (($imageName != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName) : url(Config::get('constant.DEFAULT_IMAGE'));
                  // $adsSaveData[$index]["advertisementImages"][$iIndex]["thumbnail_url"] = (($imageName != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName) : url(Config::get('constant.DEFAULT_IMAGE'));
              }
          }
      }

                $outputArray['data']["ads"] = $adsSaveData;
                $statusCode = 200;
            } else {
                $this->log->error('Advertisement not found');
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete the Advertisment for the user. If user is the owner of the Advertisement than it is going to delete otherwise not.
     */
    protected function removeAdvertisement(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $outputArray = [];
        $filters = $request->all();

        try {
            $filters["user_id"] = $user->id;
            /** We only need to display approved business */
            // $adsSaveData = $this->objAdvertisement->deleteAdvertisement($filters);

            /**  Close the advertisment on user request. Only user and Admin can set stutas to close. */
            $adsSaveData = $this->objAdvertisement->closeAdvertisement($filters);

            if ($adsSaveData) {
                $this->log->error('Advertisement status updated to closed successfully.', array('login_user_id' => $user->id));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.advertisement_closed_status_successfully');
                $statusCode = 200;
            } else {
                $this->log->error('Advertisement now found', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 404;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while deleting the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete the Advertisment. The ads will be removed from the list of showed interest items.
     */
    protected function removeInterestedAdvertisement(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $outputArray = [];
        $filters = $request->all();

        try {
            $filters["user_id"] = $user->id;
            $filters["interest_id"] = $request->interest_id;

            $adsSaveData = $this->objUserInterestInAdvertisement->deletedAdvertisement($filters);

            if ($adsSaveData) {
                $this->log->error('Advertisement delete successfully.', array('login_user_id' => $user->id));
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.advertisement_deleted_successfully');
                $statusCode = 200;
            } else {
                $this->log->error('Advertisement now found', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while deleting the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Retrive all the approved Advertisement along with filters
     */
    protected function getAllInterestedAdvertisement(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $filters = $request->all();

        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;

        try {
            /** We only need to display approved business */
            $filters['approved'] = 1;
            $filters['user_id'] = $user->id;

            if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                if (isset($request->limit) && !empty($request->limit)) {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                } elseif (isset($request->page) && !empty($request->page)) {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
                if (isset($request->sortBy) && !empty($request->sortBy)) {
                    if ($request->sortBy == 'popular') {
                        $filters['sortBy'] = 'popular';
                    }
                }
            } else {
                $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                $offset = Helpers::getOffset($pageNo, $take);
                $filters['take'] = $take;
                $filters['skip'] = $offset;
            }
            $adsSaveData = $this->objAdvertisement->getFilterInterestedAdsForFrontend($filters);
            $totalCount = $this->objAdvertisement->getFilterInterestedAdsCountForFrontend($filters);

            if ($adsSaveData) {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');
                $outputArray['totalCount'] = $totalCount;

                if ($headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    if ($totalCount < Config::get('constant.WEBSITE_RECORD_PER_PAGE')) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $outputArray['loadMore'] = (count($totalCount) > 0) ? 1 : 0;
                    }
                } else {
                    $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                    if ($totalCount < $take) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $perPageCnt = $pageNo * $take;
                        if($totalCount > $perPageCnt) {
                            $outputArray['loadMore'] = 1;
                        } else {
                            $outputArray['loadMore'] = 0;
                        }
                    }
                }

                $tmpCount = count($adsSaveData);
                for($index = 0; $index < $tmpCount; $index++) {
                    if(isset($adsSaveData[$index]["advertisementImages"])) {
                        $tmpImgCount = count($adsSaveData[$index]["advertisementImages"]);

                        for($iIndex = 0; $iIndex < $tmpImgCount; $iIndex++) {
                            $imageName = $adsSaveData[$index]["advertisementImages"][$iIndex]["image_name"];

                            if ($imageName != '' && (Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName) > 0) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $adsORIGINALimageName = $s3url.Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName;
                          }else{
                              $adsORIGINALimageName = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["original_url"] = $adsORIGINALimageName;

                  // $adsSaveData[$index]["advertisementImages"][$iIndex]["original_url"] = (($imageName != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH').$imageName) : url(Config::get('constant.DEFAULT_IMAGE'));

                          if ($imageName != '' && (Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName) > 0) {
                                $s3url =   Config::get('constant.s3url');
                                // return $s3url;
                               $adsTHUMBNAILimageName = $s3url.Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName;
                          }else{
                              $adsTHUMBNAILimageName = url(Config::get('constant.DEFAULT_IMAGE'));
                          }

                          $adsSaveData[$index]["advertisementImages"][$iIndex]["thumbnail_url"] = $adsTHUMBNAILimageName;


                  // $adsSaveData[$index]["advertisementImages"][$iIndex]["thumbnail_url"] = (($imageName != '') && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$imageName) : url(Config::get('constant.DEFAULT_IMAGE'));
              }
          }
      }

                $outputArray['data']["ads"] = $adsSaveData;
                $statusCode = 200;
            } else {
                $this->log->error('Advertisement now found', array('login_user_id' => $user->id));
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.advertisement_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertising', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * Add or update the Advertisement for authenticated user.
     *
     */
    public function uploadAdsImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $adsData = [];

        $validator = Validator::make($request->all(), [
            'advertisement_id' => ['required', 'exists:advertisements,id'],
            'image_name.*' => ['required', 'mimes:pdf,png,jpeg,jpg,bmp,gif|max:5120'],
        ]);

        if ($validator->fails()) {
            $this->log->error('API validation failed while saving advertisement ', array('login_user_id' => $user->id));
            $outputArray['status'] = 0;
            $outputArray['message'] = $validator->messages()->all()[0];
            $statusCode = 200;
            return response()->json($outputArray, $statusCode);
        }

        try {
            $adsId = $request->advertisement_id;

            DB::beginTransaction();

            if (Input::file('image_name')) {
                $fileImagesArray = Input::file('image_name');
                if (isset($fileImagesArray) && count($fileImagesArray) > 0 && !empty($fileImagesArray)) {
                    foreach ($fileImagesArray as $fileImageKey => $fileImageValue) {

                        $fileImgName = 'ads_' . Str::random(10). '.'. $fileImageValue->getClientOriginalExtension();
                        $pathThumb = (string) Image::make($fileImageValue->getRealPath())->resize($this->ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH, $this->ADVERTISEMENT_THUMBNAIL_IMAGE_HEIGHT)->encode();

                        $adsImageInsert = [];
                        $adsImageInsert['advertisement_id'] = $adsId;
                        $adsImageInsert['image_name'] = $fileImgName;
                        $response = $this->objAdvertisementImage->insertUpdate($adsImageInsert);

//                      Uploading on AWS
                       $originalImage = Helpers::addFileToStorage($fileImgName, $this->ADVERTISEMENT_ORIGINAL_IMAGE_PATH, $fileImageValue, $this->ADVERTISEMENT_STORAGE_TYPE);
                       $thumbImage = Helpers::addFileToStorage($fileImgName, $this->ADVERTISEMENT_THUMBNAIL_IMAGE_PATH, $pathThumb, $this->ADVERTISEMENT_STORAGE_TYPE);

                        if ($response === false) {
                            DB::rollback();
                            return response()->json([
                                'status' => 0,
                                'message' => trans('apimessages.image_upload_error'),
                            ], 200);
                        }
/********************************************** */
                        // $params = [
                        //     'originalPath' => ($this->ADVERTISEMENT_ORIGINAL_IMAGE_PATH),
                        //     'thumbPath' => ($this->ADVERTISEMENT_THUMBNAIL_IMAGE_PATH),
                        //     'thumbHeight' => $this->ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH,
                        //     'thumbWidth' => $this->ADVERTISEMENT_THUMBNAIL_IMAGE_WIDTH,
                        //     'previousImage' => ""
                        // ];

                        // $adsPhoto = ImageUpload::storageUploadWithThumbImage($fileImageValue, $params);
                        // $adsImageInsert = [
                        //     'advertisement_id' => $adsId,
                        //     'image_name' => $adsPhoto['imageName']
                        // ];

                        // if ($adsPhoto === false) {
                        //     DB::rollback();
                        //     return response()->json([
                        //         'status' => 0,
                        //         'message' => trans('apimessages.image_upload_error'),
                        //     ], 200);
                        // }

                        // $response = $this->objAdvertisementImage->insertUpdate($adsImageInsert);
                    }
                }
            }

            DB::commit();

            $outputArray['status'] = 1;
            $outputArray['message'] = trans('apimessages.advertisement_image_successfully');
            $statusCode = 200;

        } catch (Exception $e) {
            DB::rollback();

            $this->log->error('API something went wrong while save business', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();

            $statusCode = $e->getStatusCode();

            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }

    /**
     * getInterestResponses() Retrive all the list of interest.
     */
    protected function getInterestResponses(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $headerData = $request->header('Platform');
        $outputArray = [];
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'advertisement_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->log->error('API validation failed while retriving advertisement interest ', array('login_user_id' => $user->id));
            $outputArray['status'] = 0;
            $outputArray['message'] = $validator->messages()->all()[0];
            $statusCode = 200;
            return response()->json($outputArray, $statusCode);
        }

        try {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;

            $adsSaveData = $this->objAdvertisement->frontendFindById($request->advertisement_id, true, $user->id);

            if ($adsSaveData) {
                if (!empty($headerData) && $headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    if (isset($request->limit) && !empty($request->limit)) {
                        $take = $request->limit;
                        $skip = 0;
                    } elseif (isset($request->page) && !empty($request->page)) {
                        $offset = Helpers::getWebOffset($pageNo);
                        $take = Config::get('constant.WEBSITE_RECORD_PER_PAGE');
                        $skip = $offset;
                    }
                } else {
                    $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');
                    $offset = Helpers::getOffset($pageNo, $take);
                    $skip = $offset;
                }

                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.default_success_msg');
                $outputArray['data']= array();

                $totalCount = $adsSaveData->getUserInterestConut($request->advertisement_id);
                $outputArray['totalCount'] = $totalCount;

                if ($headerData == Config::get('constant.WEBSITE_PLATFORM')) {
                    if ($totalCount < Config::get('constant.WEBSITE_RECORD_PER_PAGE')) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $outputArray['loadMore'] = (count($totalCount) > 0) ? 1 : 0;
                    }
                } else {
                    $take = (isset($request->limit) && $request->limit > 0) ? $request->limit : Config::get('constant.API_RECORD_PER_PAGE');

                    if ($totalCount < $take) {
                        $outputArray['loadMore'] = 0;
                    } else {
                        $perPageCnt = $pageNo * $take;
                        if($totalCount > $perPageCnt) {
                            $outputArray['loadMore'] = 1;
                        } else {
                            $outputArray['loadMore'] = 0;
                        }
                    }
                }

                $interestDetails = $adsSaveData->getUserInterestList($request->advertisement_id, $take, $skip);

                foreach ($interestDetails as $interestKey => $interestValue)
                {
                    $outputArray['data'][$interestKey]['id'] = $interestValue->id;
                    $outputArray['data'][$interestKey]['timestamp'] = (!empty($interestValue->updated_at)) ? strtotime($interestValue->updated_at)*1000 : '';
                    $outputArray['data'][$interestKey]['name'] = (isset($interestValue->user) && !empty($interestValue->user->name)) ? $interestValue->user->name : '';
                    $imgThumbUrl = '';

                    if ((isset($interestValue->user) && !empty($interestValue->user->profile_pic)) && (Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) > 0) {
                        $s3url =   Config::get('constant.s3url');
                        // return $s3url;
                       $imgThumbUrl = $s3url.Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic;
                  }else{
                      $imgThumbUrl = url(Config::get('constant.DEFAULT_IMAGE'));
                  }


  
                    // $imgThumbUrl = ((isset($interestValue->user) && !empty($interestValue->user->profile_pic)) && Storage::disk(config('constant.DISK'))->exists(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) > 0) ? Storage::disk(config('constant.DISK'))->url(Config::get('constant.USER_THUMBNAIL_IMAGE_PATH').$interestValue->user->profile_pic) : url(Config::get('constant.DEFAULT_IMAGE'));
                    $outputArray['data'][$interestKey]['image_url'] = $imgThumbUrl;

                    $outputArray['data'][$interestKey]['country_code'] = $interestValue->user->country_code;
                    $outputArray['data'][$interestKey]['phone_number'] = $interestValue->user->phone;
                    $outputArray['data'][$interestKey]['comment'] = $interestValue->comment;

                    $outputArray['data'][$interestKey]['thread_id'] = $interestValue->thread_id;
                    $outputArray['data'][$interestKey]['user_business_id'] = (isset($interestValue->user->singlebusiness) && $interestValue->user->singlebusiness->id != '')? (string)$interestValue->user->singlebusiness->id : '';
                    $outputArray['data'][$interestKey]['user_business_slug'] = (isset($interestValue->user->singlebusiness) && $interestValue->user->singlebusiness->business_slug != '')? (string)$interestValue->user->singlebusiness->business_slug : '';

                }
                $statusCode = 200;
            } else {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.no_advertisement_interest_recordsfound');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $this->log->error('API something went wrong while retriving the advertisement interest', array('error' => $e->getMessage()));

            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        return response()->json($outputArray, $statusCode);
    }
}
