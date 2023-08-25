<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicWebsiteTemplets extends Model
{
     use SoftDeletes;
	
    protected $table = 'public_website_templets';
    public $primaryKey = 'id';
    protected $fillable = ['template_name','preview_image','preview_image_thumb','template_html','template_theme','status'];

}
