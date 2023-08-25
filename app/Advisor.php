<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advisor extends Model
{
	use SoftDeletes;
	
    protected $fillable = ['name','email','mobile_number','position','image','description','status'];

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
}
