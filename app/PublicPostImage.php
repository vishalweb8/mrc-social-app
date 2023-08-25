<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicPostImage extends Model
{
    protected $fillable = ['post_id','url'];

    /**
	 * for add custom key
	 *
	 * @var array
	 */
	protected $appends = [ 'full_url'];
	
	/**
	 * add custom image_url key and value
	 *
	 * @return void
	 */
	public function getFullUrlAttribute()
	{
		$url = '';
		if(!empty($this->url)) {
			$url = \Storage::disk(config('constant.DISK'))->url($this->url);
		}
    	return $url;
	}
}
