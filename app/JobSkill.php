<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobSkill extends Model
{
    protected $table = 'job_skills';
    protected $fillable = ['job_vacancy_id', 'skill_name']; 

    public function jobvacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }
}
