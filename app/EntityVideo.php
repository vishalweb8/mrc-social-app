<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EntityVideo extends Model
{
    protected $fillable = ['entity_id','created_by','title','description','thumbnail','video'];

    protected $appends = [ 'thumbnail_url','video_url'];
	
	/**
	 * add custom image_url key and value
	 *
	 * @return void
	 */
	public function getThumbnailUrlAttribute()
	{
		if(!empty($this->thumbnail)) {
			$url = \Storage::disk(config('constant.DISK'))->url($this->thumbnail);
		} else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
    	return $url;
	}
    
    /**
     * for get url of video
     *
     * @return void
     */
    public function getVideoUrlAttribute()
	{
        $url = '';
		if(!empty($this->video)) {
			$url = \Storage::disk(config('constant.DISK'))->url($this->video);
		}
    	return $url;
	}

    /**
     * getCreatedAtAttribute
     *
     * @return void
     */
    public function getCreatedAtAttribute($createdAt)
	{
        if(!empty($createdAt)) {
            return Carbon::parse($createdAt)->diffForHumans(null,null,true);
        }
	}
}
