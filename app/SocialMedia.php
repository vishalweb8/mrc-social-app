<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['site_id','name','url'];
}
