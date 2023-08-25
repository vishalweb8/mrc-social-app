<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['site_id','content_id','shared_by','is_shared','type'];
}
