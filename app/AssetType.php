<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class AssetType extends Model
{
    protected $fillable = ['name','parent'];

    
    /**
     * for get parent data of asset type
     *
     * @return void
     */
    public function parent()
    {
        return $this->belongsTo(AssetType::class,'parent');
    }
        
    /**
     * for get child data of asset type
     *
     * @return void
     */
    public function childs()
    {
        return $this->hasMany(AssetType::class,'parent')->orderBy('name','ASC');
    }
    
    /**
     * for get selected field of sub asset type
     *
     * @return void
     */
    public function fields()
    {
        return $this->hasMany(AssetTypeField::class,'asset_type_id');
    }
}
