<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\SiteContent;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function validateDonor($request)
	{
		$validator = \Validator::make($request->all(), 
			[                    
				'name' => 'required',
                'mobile_number' => 'required',
                'covid_start_date' => 'date_format:Y-m-d',
				'status' => 'in:active,inactive',
			]
		);

		return $validator;
	}

    public function getFirstLastName($name)
	{
		$names = explode(' ',$name);

		return $names;
	}

    public function getSettings()
    {
        $isEnableUpi = Helpers::isOnSettings('is_upi_enable');
        $data = [
            'is_enable_upi' => !!$isEnableUpi
        ];
        $responseData = ['status' => 1, 'message' => trans('apimessages.get_settings_msg'),'data'=> $data];
        return response()->json($responseData,200); 
    }
    
    /**
     * create site content
     *
     * @param  mixed $data
     * @param  mixed $isShared
     * @return void
     */
    public function createSiteContent($data,$isShared = false)
    {
        try {
            $data['shared_by'] = auth()->id();
            $query = $data;
            $data['is_shared'] = $isShared;
            SiteContent::updateOrCreate($query, $data);
        } catch (\Throwable $th) {
            Log::error('getting error while creating site content.'.$th);
        }
    }
}
