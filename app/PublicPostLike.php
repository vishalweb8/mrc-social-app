<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicPostLike extends Model
{
    protected $fillable = ['post_id','user_id','latitude','longitude','ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(User::class, 'post_id');
    }
    
}
