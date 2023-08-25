<?php

namespace App\Http\Controllers\Api;

use Log;
use Helpers;
use Validator;
use App\Business;
use App\PublicReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublicReviewController extends Controller
{
    /**
     * store public review
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
                    'name' => 'required',
                    'mobile_number' => 'required',
                    'message' => 'required',
                    'business_id'  =>  'required',
                    'rating'  =>  'required|between:1,5',
                ]
            );
            
            if ($validator->fails()) {
				Log::error("Public review validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all();
				
            } else {
				PublicReview::create($request->all());
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.review_created');

				// for send public review mail
				$this->sendMail($request);
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing public review: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
	}

    /**
	 * for sent public review email to business
	 *
	 * @param  mixed $request
	 * @return void
	 */
	public function sendMail($request)
	{
		try {
			$business = Business::find($request->business_id);
			if($business && !empty($business->email_id)) {

				$replaceArray = array_change_key_case($request->all(), CASE_UPPER);
				$email = [
					'toEmail' => $business->email_id
				];
				$replaceArray["BUSINESS"] = $business->name;
				$replaceArray["URL"] = "https://ryuva.club/website/".$business->url_slug;
				
				Helpers::sendMailByTemplate($replaceArray,'public-review',$email ,'');
				Log::info("Public review email sent to business, email id: ".$business->email_id);
			} else {
				Log::info("Business email id is empty");
			}
		} catch (\Exception $e) {
			Log::error("Getting error while sending public review mail: ".$e);
		}
	}
}
