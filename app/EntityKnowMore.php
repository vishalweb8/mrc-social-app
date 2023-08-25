<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityKnowMore extends Model
{
    protected $fillable = ['entity_id', 'language', 'title', 'description'];
}
