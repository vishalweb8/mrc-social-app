<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $fillable = ['type','asset_type_id','reason'];
    
    /**
     * assetType
     *
     * @return void
     */
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
}
