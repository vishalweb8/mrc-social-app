<?php

namespace App;


use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;

class BrandingList extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'branding_list';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'group_title', 'business_id'];

    
   
    
}
