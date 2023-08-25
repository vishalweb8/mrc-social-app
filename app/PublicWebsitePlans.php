<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicWebsitePlans extends Model
{
     use SoftDeletes;
	
    protected $table = 'public_website_plans';
    public $primaryKey = 'id';
    protected $fillable = ['pw_plan_name', 'pw_plan_features','pw_plan_mrp','pw_plan_amount','pw_plan_duration','status'];
}
