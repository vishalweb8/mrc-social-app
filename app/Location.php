<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
 
    protected $fillable = ['country_code', 'country', 'state', 'district', 'tehsil', 'city', 'locality', 'pincode', 'latitude', 'longitude', 'type', 'status', 'position', 'flag'];
    
    public function jobVacancy()
    {
        return $this->hasMany(JobVacancy::class, 'location_id');
    }
    /**
     * get all Location for admin
     */
    public function getallcountry($filters = array(), $paginate = false)
    {
        $location = Location::orderBy('country', 'ASC')->groupby("country"); 
        
        if(isset($paginate) && $paginate == true) {
            return $location->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $location->get();
        }
    }

     /**
     * get all Location for admin
     */
    public function getallstate($filters = array(), $paginate = false)
    {
        $location = Location::orderBy('state', 'ASC')->groupby("state"); 
        
        if(isset($paginate) && $paginate == true) {
            return $location->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $location->get();
        }
    }

     /**
     * get all getallCities for admin
     */
    public function getallCities($filters = array(), $paginate = false)
    {
        $location = Location::orderBy('city', 'ASC')->groupby("city"); 
        
        if(isset($paginate) && $paginate == true) {
            return $location->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $location->get();
        }
    }
    
     /**
     * get all getalldistrict for admin
     */
    public function getalldistrict($filters = array(), $paginate = false)
    {
        $location = Location::orderBy('district', 'ASC')->groupby("district"); 
        
        if(isset($paginate) && $paginate == true) {
            return $location->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $location->get();
        }
    }


         /**
     * get all Location for admin
     */
    public function getCitiesByPin($pincode)
    {
        $location = Location::orderBy('city', 'ASC')->where('pincode', 'like', '%'.$pincode.'%'); 
        
        if(isset($paginate) && $paginate == true) {
            return $location->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $location->get();
        }
    }
 
}
