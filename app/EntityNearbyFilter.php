<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityNearbyFilter extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['entity_id','title', 'is_enable_filter', 'top_limit', 'asset_type_id','sql_query'];
}
