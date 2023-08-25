<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVisitActivity extends Model
{
    protected $fillable = ['user_id', 'entity_id','entity_type','latitude','longitude','ip_address'];

    public function entity()
    {
        return $this->belongsTo(Business::class, 'entity_id');
    }
}
