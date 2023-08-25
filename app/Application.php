<?php

namespace App;
use Config;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['name', 'slug', 'setting'];
    
    public function getAll($filters = array(), $paginate = false)
    {
        $app = Application::orderBy('name', 'ASC');
        
        if(isset($paginate) && $paginate == true) {
            return $app->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $app->get();
        }
    }
    public function callToAction()
    { 
        return $this->hasMany(CallToAction::class,'application_id'); 
    } 

}
