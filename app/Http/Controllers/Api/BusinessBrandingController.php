<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BusinessBranding;
use App\BusinessBrandingInquiry;
use Validator;
use Log;

class BusinessBrandingController extends Controller
{    
    /**
     * for get listing of business branding
     *
     * @return void
     */
    public function getBusinessBrandings()
	{
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$brandings = BusinessBranding::select('id','business_id','start_date','end_date','views','clicks','image','status')->get();
			$responseData = ['status' => 1, 'message' => trans('apimessages.business_branding_getting'), 'data' => $brandings];
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while getting business branding: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
	}
	
	/**
	 * For Add Busines Branding Inquiry
	 *
	 * @param  mixed $request
	 * @return void
	 */
	public function addBusinesBrandingInquiry(Request $request)
	{
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$validator = Validator::make($request->all(), 
                [                    
                    'business_name'  =>  'required',
                    'name' => 'required',
                    'mobile_number' => 'required|numeric|digits:10',
                    'city' => 'required',
                ]
            );
            
            if ($validator->fails()) {
				Log::error("Business branding inquiry validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all();
				
            } else {
				BusinessBrandingInquiry::create($request->all());
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.business_branding_inquiry_created');
                $responseData['data'] = [];
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while adding business branding inquiry: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
	}
}
