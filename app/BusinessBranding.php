<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessBranding extends Model
{
    protected $fillable = ['business_id','start_date','end_date','views','clicks','image','status'];
	
	/**
	 * for add custom key
	 *
	 * @var array
	 */
	protected $appends = [ 'image_url'];
	
	/**
	 * add custom image_url key and value
	 *
	 * @return void
	 */
	public function getImageUrlAttribute()
	{
		$url = '';
		if(!empty($this->image) && \Storage::disk(config('constant.DISK'))->exists($this->image)) {
			$url = \Storage::disk(config('constant.DISK'))->url($this->image);
		}
    	return $url;
	}
	
	/**
	 * Relationship to business model
	 *
	 * @return void
	 */
	public function business(){
		return $this->belongsTo(Business::class,'business_id');
	}
}
