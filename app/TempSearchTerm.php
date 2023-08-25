<?php

namespace App;


use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;

class TempSearchTerm extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_search_activities';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['search_term', 'city','user_id','result_count','latitude', 'longitude','ip_address'];
    	
	/**
	 * for get user detail
	 *
	 * @return void
	 */
	public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    
}
