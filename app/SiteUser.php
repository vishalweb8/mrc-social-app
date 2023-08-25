<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteUser extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['site_id','user_id','role_id'];
}
