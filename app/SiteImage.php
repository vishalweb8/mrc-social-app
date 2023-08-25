<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteImage extends Model
{
    protected $fillable = ['site_id', 'url'];

    public $timestamps = false;
    
    /**
     * for get full url of image
     *
     * @param  mixed $logo
     * @return void
     */
    public function getUrlAttribute($logo)
	{
		if(!empty($logo)) {
			$url = \Storage::disk(config('constant.DISK'))->url($logo);
		} else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
    	return $url;
	}
}
