<?php

namespace App\Http\Controllers\Api;

use App\Business;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Helpers;
use Log;

class BusinessCategoryController extends Controller
{   

    /**
     * store business category
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
	{
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$validator = Validator::make($request->all(), 
                [
                    'business_id'  =>  'required',
                    'sub_category_ids'  =>  'required',
                ]
            );
            
            if ($validator->fails()) {
				Log::error("business category validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all();
				
            } else {
				$postData['category_id'] = $request->sub_category_ids;            
            	$postData['category_hierarchy'] = Helpers::getCategoryHierarchy($postData['category_id']);
            	$postData['parent_category'] = Helpers::getParentCategoryIds($postData['category_id']);

				Business::whereId($request->business_id)->update($postData);
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.business_category_created');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing business category: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
	}
}
