<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicInquiry extends Model
{
	use SoftDeletes;

    protected $fillable = ['business_id', 'email', 'name', 'mobile_number', 'message'];

	public function business(){
		return $this->belongsTo(Business::class,'business_id');
	}
}
