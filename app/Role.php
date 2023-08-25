<?php

namespace App;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use Sluggable, SoftDeletes;

    protected $table = 'roles';
    protected $fillable = ['guard_name', 'name','type','slug'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
    
    public function scopeSiteManagement($query)
	{
        $query->where('type','like','site')->where('name','<>','Member');
    }
}
