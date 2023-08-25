<?php

namespace App;

// use AWS\CRT\Log;
use Storage;
use Illuminate\Database\Eloquent\Model;
use Log;
use Config;
class JobVacancy extends Model
{
    protected $table = 'job_vacancies';
    protected $fillable = ['application_id', 'location_id', 'user_id', 'title', 'description', 'external_link', 'address', 'qualification', 'experience', 'workplace_type', 'employment_type', 'company_name', 'type', 'image_url', 'status'];
    protected $appends = ['image_fullurl'];

    public function getImageFullurlAttribute()
    { 
        if(!empty($this->image_url)) { 
            $url = config('constant.JOB_IMAGE_PATH').$this->image_url;  
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

    public function jobskill()
    {
        return $this->hasMany(JobSkill::class, 'job_vacancy_id');
    }

    public function jobApplied()
    {
        return $this->hasMany(JobApplied::class, 'job_vacancy_id');
    }
    
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    } 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }   
    public function getAll($filters = array(), $paginate = false)
    {
        $selectColumns = [
            'job_vacancies.id',
            'job_vacancies.application_id',
            'job_vacancies.location_id',
            'job_vacancies.user_id',
            'job_vacancies.title',
            'job_vacancies.description',   
            'job_vacancies.external_link',   
            'job_vacancies.qualification',   
            'job_vacancies.experience',   
            'job_vacancies.workplace_type',   
            'job_vacancies.employment_type',   
            'job_vacancies.company_name',   
            'job_vacancies.type',   
            'job_vacancies.status', 
            'job_vacancies.image_url', 
                  
        ];
        $jobs = JobVacancy::where('status', 1)->where("type", "Job Hiring");

        if (isset($filters) && !empty($filters)) {
             
            if (isset($filters['title']) && $filters['title'] != '') { 
                $jobs->where('job_vacancies.title', 'like', '%' . $filters['title'] . '%');
            }  
            
            if (isset($filters['employment_type']) && $filters['employment_type'] != '') { 
                $jobs->where('job_vacancies.employment_type', $filters['employment_type'] );
            } 

            if (isset($filters['workplace_type']) && $filters['workplace_type'] != '') { 
                $jobs->where('job_vacancies.workplace_type', $filters['workplace_type'] );
            } 

            if (isset($filters['company_name']) && $filters['company_name'] != '') { 
                $jobs->where('job_vacancies.company_name', 'like', '%' . $filters['company_name'] . '%');
            } 

            if (isset($filters['skills']) && $filters['skills'] != '') { 
                $jobs->whereHas('jobskill' ,function ($query) use($filters) {
                    return $query->where('skill_name', 'like', '%' . $filters['skills'] . '%');
                });  
            } 
            
            if (isset($filters['location_id']) && $filters['location_id'] != '') { 
                $jobs->where('job_vacancies.location_id', $filters['location_id'] );
            }  
        }  
        if (!$paginate && !empty($filters['skip'])) { 
			$jobs->skip($filters['skip'])->take($filters['take']);
		}

        if($paginate) {
			return $jobs->count();
		}
        else{
            $jobs->with(['jobskill:job_vacancy_id,skill_name', 'user:id,name', 'location:id,city,state,country,district,pincode']);  
            $jobs->select($selectColumns);  
            return $jobs->get();  
        }

        
        
    }

    

}
