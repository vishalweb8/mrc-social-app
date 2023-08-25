<?php

namespace App;

use Auth;
use Config;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessImage extends Model
{
    use SoftDeletes;

    protected $table = 'business_images';
    protected $fillable = ['business_id', 'image_name'];
    protected $dates = ['deleted_at'];
    protected $appends = ['image_name_url'];
	/**
	* Get Business Images by businessId
	*/
    public function getBusinessImagesByBusinessId($businessId)
    {
       	return BusinessImage::where('business_id', $businessId)->get();
    }
   
    public function getImageNameUrlAttribute()
    {
        return Config::get('constant.s3url').Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$this->image_name;
    }

}
