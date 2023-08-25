<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;

class BusinessDoc extends Model
{
    use SoftDeletes;

    protected $table = 'business_doc';
    protected $fillable = ['business_id', 'doc_name','front_image','back_image'];
    protected $dates = ['deleted_at'];

	/**
	* Get Business Images by businessId
	*/
    public function getBusinessImagesByBusinessId($businessId)
    {
       	return BusinessImage::where('business_id', $businessId)->get();
    }
   
}
