<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssetTypeField extends Model
{
    protected $fillable = ['asset_type_id', 'category_id','selected_fields'];
}
