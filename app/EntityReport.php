<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityReport extends Model
{
    protected $fillable = ['entity_id','post_id','site_id','asset_type_id','comment','report_by'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d, H:m',
    ];

    public function scopeHasRelation($query){
        $query->where(function($q){
            return $q->has('entity')->orHas('post')->orHas('site');
        });
        return $query->has('reportBy');
    }
    
    /**
     * for get name of reported user
     *
     * @return void
     */
    public function reportBy()
    {
        return $this->belongsTo(User::class,'report_by');
    }
    
    /**
     * for get entity detail
     *
     * @return void
     */
    public function entity()
    {
        return $this->belongsTo(Business::class,'entity_id');
    }
    
    /**
     * for get post detail
     *
     * @return void
     */
    public function post()
    {
        return $this->belongsTo(PublicPost::class,'post_id');
    }
    
    /**
     * for get site detail
     *
     * @return void
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * assetType
     *
     * @return void
     */
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
    
    /**
     * for get reasons of report
     *
     * @return void
     */
    public function reasons()
    {
        return $this->belongsToMany(Reason::class,'entity_report_reasons','entity_report_id','reason_id');
    }
}
