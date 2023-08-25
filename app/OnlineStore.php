<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OnlineStore extends Model
{
    protected $fillable = ['name','logo','status'];

    public function getLogoAttribute()
	{
		$logo = '';
		if(!empty($this->attributes['logo']) && \Storage::disk(config('constant.DISK'))->exists($this->attributes['logo'])) {
			$logo = \Storage::disk(config('constant.DISK'))->url($this->attributes['logo']);
		}
    	return $logo;
	}
}
