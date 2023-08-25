<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\PlasmaDonor;
use Illuminate\Http\Request;
use Log;

class PlasmaDonorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			
            $filters = $request->all();
            $headerData = $request->header('Platform');
            $pageNo = $request->input('page',1);

            if (!empty($headerData) && $headerData == config('constant.WEBSITE_PLATFORM'))
            {
                if (isset($request->limit) && !empty($request->limit))
                {
                    $filters['take'] = $request->limit;
                    $filters['skip'] = 0;
                }
                elseif ($pageNo)
                {
                    $offset = Helpers::getWebOffset($pageNo);
                    $filters['take'] = config('constant.WEBSITE_RECORD_PER_PAGE');
                    $filters['skip'] = $offset;
                }
            }
            else
            {
                $offset = Helpers::getOffset($pageNo);
                $filters['take'] = config('constant.API_RECORD_PER_PAGE');
                $filters['skip'] = $offset;
            }
            $donors = PlasmaDonor::getAll($filters);
            $donorCount = PlasmaDonor::getAll($filters,true);
            $perPageCnt = $pageNo * $filters['take'];
            if($donorCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }
            $responseData['status'] = 1;
            $responseData['total'] = $donorCount;
            if(!$donors->isEmpty()) {
                $responseData['message'] = trans('apimessages.plasma_donor_getting');
                $responseData['data'] = $donors;
            } else {
                info('API getAllPlasma donors no records found');
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = array();
            }
			
			return response()->json($responseData, 200);
		} catch (\Exception $e) {
			Log::error("Getting error while listing plasma donor in api: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		$validator = $this->validateDonor($request);
        try {
			if ($validator->fails()) {
				Log::error("Plasma donor validation failed in api.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all();
				
            } else {
				$data = $request->all();
				PlasmaDonor::create($data);
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.plasma_donor_created');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while creating plasma donor in api: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        $validator = $this->validateDonor($request);
		$validator->sometimes('id', 'required|numeric', function () {
			return true;
		});
        try {
			if ($validator->fails()) {
				Log::error("Plasma donor update validation failed in api.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all();
				
            } else {
				$data = $request->only(['name','mobile_number','blood_group','covid_start_date','status','city']);
				PlasmaDonor::whereId($request->id)->update($data);
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.plasma_donor_updated');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while updating plasma donor in api: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
			$validator = \Validator::make($request->all(), 
				[                    
					'id' => 'required',
				]);
			if ($validator->fails()) {
				Log::error("Plasma donor delete validation failed in api.");
				$responseData['status'] = 0;
				$responseData['message'] = $validator->messages()->all();
				
			} else {
				PlasmaDonor::whereId($request->id)->delete();
				$responseData = ['status' => 1, 'message' => trans('apimessages.plasma_donor_deleted')];
			}
			return response()->json($responseData,200);
        } catch (\Exception $e) {
            Log::error("Getting error while deleting public plasma donor in api: ".$e);
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
			return response()->json($responseData,400);
        }
    }
}
