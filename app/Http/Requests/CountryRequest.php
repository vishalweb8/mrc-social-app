<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CountryRequest extends Request {

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
                'name' => 'required|unique:country,name',
                'country_code' => 'regex:/^\+\d{1,3}$/'
            ];
        }
        else
        {
            return [
                'name' => 'required|unique:country,name,'.$this->get('id'),
                'country_code' => 'regex:/^\+\d{1,3}$/'
            ];
        }
        
       
    }

    

}
