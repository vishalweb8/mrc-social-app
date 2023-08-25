<?php

namespace App\Http\Controllers\Api;

use App\Advertisement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CallToAction;
use App\Advisor;
use App\Business;
use App\Category;
use App\PublicPost;
use App\UserVisitActivity;
use DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\JWTAuthException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class ApplicationsController extends Controller
{

    public function __construct()
    {
        $this->objButton = new CallToAction();
        $this->objAdvertisement = new Advertisement();
    }
    public function homePage(Request $request)
    {

        $responseData = ['status' => 500, 'message' => trans('apimessages.default_error_msg')]; 
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
        try {
            
            // Home Page Butons
            $topButtons = CallToAction::where('placement', '=', "homepageTop")
                ->where('status', '=', 0)
                ->select("name", "icon", "target", "application_id")
                ->limit(2)
                ->get();

            // Category

            $category = Category::where("trending_category", '=', 1)
                ->where('is_active', '=', 1)
                ->select('id', 'name','category_slug','cat_logo','banner_img')
                ->get();

            // Signup Strip

            // Sponsered Business  

            $lat = $request->latitude;
            $lon = $request->longitude;

            $sponsoredBusiness = Business::select(
                'business.id',
                'business.name',
                'business.business_slug',
                'business.business_logo',
                'business.category_id',
                'business.description',
                'business.metatags',
                DB::raw('(select round(AVG(rating),1) from business_ratings where business_id = business.id) as avg_rating'),
                DB::raw('(select COUNT(id) from business_ratings where business_id = business.id) as total_review'),
                DB::raw('(select name from asset_types where id = business.asset_type_id) as entity_type'),
                DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(" . $lon . ")) 
                + sin(radians(" . $lat . ")) 
                * sin(radians(latitude))) AS distance")
            )
            ->where('approved', 1)
            ->where("promoted", "=", "1")
            ->orderby("id", "DESC")
            ->limit(4)->get(); 

            // Market Place Add 
            $marketPlaceAdsData = $this->objAdvertisement->getFilterAdsHomepage([]);
            // return $marketPlaceAdsData;
            $marketPlaceAds=[];
            $i=0;
            foreach($marketPlaceAdsData as $dataads)
            {
                $marketPlaceAds[$i]['id']= $dataads->id;
                $marketPlaceAds[$i]['user_id']= $dataads->user_id;
                $marketPlaceAds[$i]['name']= $dataads->name;
                $marketPlaceAds[$i]['descriptions']= $dataads->descriptions;
                $marketPlaceAds[$i]['address']= $dataads->address;
                $marketPlaceAds[$i]['street_address']= $dataads->street_address;
               
                if(sizeof($dataads->advertisementImages)>0){
                    foreach($dataads->advertisementImages as $imgs){ 
                        $marketPlaceAds[$i]['advertisement_images']= $imgs['image_name'];  
                    } 
                }
                else{ 
                    $url = url(config('constant.DEFAULT_IMAGE'));
                    $marketPlaceAds[$i]['advertisement_images']= $url;
                } 
                $i++;
            }
            
            // Center Buttons 
            $centerButtons = CallToAction::where('placement', '=', "homepageCenter")
                ->where('status', '=', 0)
                ->select("name", "icon", "target", "application_id")
                ->limit(4)
                ->get();

            // UserVisitActivity 
            $userVisitActivity = UserVisitActivity::where('user_id', '=', $loginUserId)
            ->has('entity')->with('entity')  
            ->groupby('entity_id') 
            ->limit(5)
            ->get(); 

            // Recommanded Business
            $getRecommandedBusiness = Business::select(
                'business.id',
                'business.name',
                'business.business_slug',
                'business.business_logo',
                'business.category_id',
                'business.description',
                'business.metatags',
                DB::raw('(select round(AVG(rating),1) from business_ratings where business_id = business.id) as avg_rating'),
                DB::raw('(select COUNT(id) from business_ratings where business_id = business.id) as total_review'),
                DB::raw('(select name from asset_types where id = business.asset_type_id) as entity_type'),
                DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(" . $lon . ")) 
                + sin(radians(" . $lat . ")) 
                * sin(radians(latitude))) AS distance")
            )
                ->where('approved',1) 
                ->orderby("id", "DESC")
                ->limit(4)->get(); 
            
            // Center Buttons 
            $bottomButtons = CallToAction::where('placement', '=', "homepageBottom")
                ->where('status', '=', 0)
                ->select("name", "icon", "target", "application_id")
                ->limit(4)
                ->get();

            $responseData['status'] = 200;
            $responseData['message'] = trans('apimessages.data_found');
            $data = [];

            $data = [
                'topButtons' => $topButtons,
                'category' => $category,
                'sponsoredBusiness' => $sponsoredBusiness,
                'marketPlaceAds' => $marketPlaceAds,
                'centerButtons' => $centerButtons,
                'userVisitActivity' => $userVisitActivity,
                'getRecommandedBusiness'=>$getRecommandedBusiness,
                'bottomButtons'=>$bottomButtons
            ];

            $responseData['data'] = $data;
            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            \Log::error("Getting error while fetching public website detail: " . $e);
            $responseData['message'] = $e->getMessage();
            return response()->json($responseData, 400);
        }
    }
}
