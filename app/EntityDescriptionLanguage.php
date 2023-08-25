<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityDescriptionLanguage extends Model
{
    protected $fillable = ['entity_id','language','description','short_description'];
}
