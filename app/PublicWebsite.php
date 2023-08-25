<?php

namespace App;

use App\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicWebsite extends Model
{
     use SoftDeletes;

    protected $table = 'public_websites';
    public $primaryKey = 'id';
    protected $fillable = ['business_id', 'user_id','website_name','template_id','template_theme','pw_template_color_id','pw_plan_id','pw_plan_start_date','pw_plan_end_date','pw_payment_id','pw_domain','pw_type','status'];

     public function businessName(){
     	return $this->hasOne(Business::class, 'id', 'business_id');
     }

      public function templetName(){
     	return $this->hasOne(PublicWebsiteTemplets::class, 'id', 'template_id');
     }
     public function plantName(){
     	return $this->hasOne(PublicWebsitePlans::class, 'id', 'pw_plan_id');
     }
}
