<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityDescriptionSuggestion extends Model
{
    protected $fillable = ['user_id','entity_id','entity_know_more_id','description'];
    
    protected $casts = [
        'created_at' => 'datetime:Y-m-d, H:m',
    ];
    /**
     * user relationship
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
