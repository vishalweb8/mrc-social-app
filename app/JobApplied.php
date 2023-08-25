<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobApplied extends Model
{
    protected $table = 'job_applieds';
    protected $fillable = ['job_vacancy_id', 'user_id', 'email', 'mobile_no', 'document_file'];

    protected $appends = ['document_fullurl'];

    public function getDocumentFullurlAttribute()
    { 
        if(!empty($this->document_file)) { 
            $url = config('constant.JOB_APPLY_IMAGE_PATH').$this->document_file; 
            if(\Storage::disk(config('constant.DISK'))->exists($url)) {  
                $url = \Storage::disk(config('constant.DISK'))->url($url);
            }
            else{ 
                $url = url(config('constant.DEFAULT_IMAGE'));
            }
        } else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
        return $url;
    }

    public function jobvacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function getAll($filters = array(), $paginate = false)
    {
        $selectColumns = [
            'job_applieds.id',
            'job_applieds.job_vacancy_id',
            'job_applieds.user_id',
            'job_applieds.email',
            'job_applieds.mobile_no',
            'job_applieds.experience',   
            'job_applieds.document_file',        
        ];
        $jobApply = JobApplied::query();

        if (isset($filters) && !empty($filters)) {
             
            if (isset($filters['job_vacancy_id']) && $filters['job_vacancy_id'] != '') { 
                $jobApply->where('job_applieds.job_vacancy_id', $filters['job_vacancy_id'] );
            }
            if (isset($filters['experience']) && $filters['experience'] != '') { 
                $jobApply->where('job_applieds.experience', $filters['experience'] );
            }  
            
            if (isset($filters['mobile_no']) && $filters['mobile_no'] != '') {  
                $jobApply->where('job_applieds.mobile_no', 'like', '%' . $filters['mobile_no'] . '%');
            } 

            if (isset($filters['email']) && $filters['email'] != '') {  
                $jobApply->where('job_applieds.email', 'like', '%' . $filters['email'] . '%');
            }  
        } 
        if (!$paginate && !empty($filters['skip'])) { 
			$jobApply->skip($filters['skip'])->take($filters['take']);
		}

        if($paginate) {
			return $jobApply->count();
		}
        else{
            $jobApply->with(['jobVacancy:id,title,description', 'user:id,name']);  
            $jobApply->select($selectColumns);
            return $jobApply->get();  
        }


        

           
    }
}
