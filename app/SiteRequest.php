<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteRequest extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['site_id','sender_by','user_id','status','joined_at'];
}
