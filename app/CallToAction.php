<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Storage;
class CallToAction extends Model
{
    // protected $table = 'call_to_actions';
    protected $fillable = ['application_id', 'name', 'icon','target','placement', 'status'];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    { 
        if(!empty($this->icon)) { 
            $url = config('constant.ICON_IMAGE_PATH').$this->icon;  
            if(Storage::disk(config('constant.DISK'))->exists($url)) {  
                $url = Storage::disk(config('constant.DISK'))->url($url);
            }
            else{ 
                $url = url(config('constant.DEFAULT_IMAGE'));
            }
        } else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
        return $url;

    } 
    
    public function application()
    {
        return $this->belongsTo(Application::class,"application_id");
    }

     

}
