<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessBrandingInquiry extends Model
{
    protected $fillable = ['business_id','business_name','city','name','mobile_number','status','type','feedback'];
	
	/**
	 * Relationship to business model
	 *
	 * @return void
	 */
	public function business(){
		return $this->belongsTo(Business::class,'business_id');
	}
}
