<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AdvertisementRequest extends Request {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {

        if ($this->get('id') == 0) 
        {
            return [
                'ads_type' => ['required', 'digits_between:0,2'],  /** 0 - Buy, 1 - Sell, 2 - Service */
                'name' => ['required', 'max:100'],
                'descriptions' => ['required', 'max:2000'],
                'image_name.*' => ['mimes:pdf,png,jpeg,jpg,bmp,gif|max:5120'],
                'video_link.*' => ['url'],                            
            ];
        }
        else
        {
            return [
                'ads_type' => ['required', 'digits_between:0,2'],  /** 0 - Buy, 1 - Sell, 2 - Service */
                'name' => ['required', 'max:100'],
                'descriptions' => ['required', 'max:2000'],
                'image_name.*' => ['mimes:pdf,png,jpeg,jpg,bmp,gif|max:5120'],
                'video_link.*' => ['url'],          
            ];
        }              
    }
}
