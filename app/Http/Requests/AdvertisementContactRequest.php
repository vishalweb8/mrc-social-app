<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AdvertisementContactRequest extends Request {

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

        return [
            'address' => 'max:100',
            'street_address' => 'max:255',
            'city' => 'max:100',
            'pincode' => 'min:5|max:6',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
        ];
       
    }

    public function messages() {
        return [
            'address.required' => trans('labels.addressrequired'),
        ];
    }

}
