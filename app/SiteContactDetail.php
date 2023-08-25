<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteContactDetail extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['site_id','name','mobile_no','location_id','address'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
