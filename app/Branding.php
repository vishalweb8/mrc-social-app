<?php

namespace App;


use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;

class Branding extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'branding';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'type', 'business_id', 'page_name'];

    
    public function business()
    {
        return $this->belongsTo('App\Business', 'business_id');
    }
    
}
