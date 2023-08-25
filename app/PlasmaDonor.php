<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlasmaDonor extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','mobile_number','blood_group','covid_start_date','status','city'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

	public function scopeGetAll($query, $filters, $count =false)
	{
		$status = 'active';	
		
		if(isset($filters['name']) && !empty($filters['name']))
		{
			$query->where('name', $filters['name']);
		}

        if(isset($filters['city']) && !empty($filters['city']))
		{
			$query->where('city', $filters['city']);
		}
		
		if(isset($filters['mobile_number']) && !empty($filters['mobile_number']))
		{
			$query->where('mobile_number', $filters['mobile_number']);
		}
		
		if(isset($filters['blood_group']) && !empty($filters['blood_group']))
		{
			$query->where('blood_group', $filters['blood_group']);
		}

		if(isset($filters['status']) && !empty($filters['status']))
		{
			$status = $filters['status'];
		}	

		if (isset($filters['searchText']) && $filters['searchText'] != '') {
			$search = $filters['searchText'];
			$query->where(function($q) use ($search) {
				$q->orWhere('name', 'like', '%' . $search . '%');
				$q->orWhere('city', 'like', '%' . $search . '%');
				$q->orWhere('mobile_number', 'like', '%' . $search . '%');
				$q->orWhere('blood_group', 'like', '%' . $search . '%');
				$q->orWhere('covid_start_date', 'like', '%' . $search . '%');
			});
		}

		if (isset($filters['skip']) && $filters['skip'] >= 0 && isset($filters['take']) && $filters['take'] > 0 && $count == false) {
			$query->skip($filters['skip'])->take($filters['take']);
		}

		if(isset($filters['sortBy']) && !empty($filters['order']) && in_array($filters['sortBy'],['name','mobile_number','blood_group','covid_start_date','city']))
		{
			$query->orderBy($filters['sortBy'],$filters['order']);
		} else {
			$query->latest();
		}
		$query->where('status', $status);

		if($count) {
			$data = $query->count();
		} else {
			$data = $query->get()->makeHidden(['created_at','updated_at']);
		}
		return $data;
	}
}
