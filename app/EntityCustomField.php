<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityCustomField extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['entity_id','language','title','description'];
}
