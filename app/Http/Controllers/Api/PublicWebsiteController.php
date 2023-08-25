<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PublicWebsite;
use App\Advisor;

class PublicWebsiteController extends Controller
{    
    /**
     * for get website detail
     *
     * @param  mixed $request
     * @return void
     */
    public function getDetail(Request $request)
	{
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$website = PublicWebsite::where('website_slug_name',$request->slug)->first();
			$responseData['status'] = 1;
			$responseData['message'] = trans('apimessages.website_getting');
			$data = [];
			if($website) {
				$data = [
					'business_id' => $website->business_id,
					'website_slug' => $website->website_slug_name,
					'template_name' => isset($website->templetName) ? $website->templetName->template_name : '' ,
					'template_theme' => $website->template_theme,
					'status' => ($website->status == 2) ? 'Active' : 'Inactive',
				];
			}
			$responseData['data'] = $data;
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			\Log::error("Getting error while fetching public website detail: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
	}
	
	/**
	 * For Get All Advisors
	 *
	 * @return void
	 */
	public function getAllAdvisors()
	{
		$advisors = Advisor::latest()->get()->makeHidden(['created_at','updated_at','deleted_at']);
		$responseData = ['status' => 1, 'message' => trans('apimessages.advisor_getting'), 'data'=> $advisors];
		return response()->json($responseData,200);
	}
}
