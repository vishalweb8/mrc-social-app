<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Site extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['name','description','logo','link','asset_type_id','created_by','approved_by','is_approved','visibility','is_enable_request','status','is_agree','approved_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d, H:m',
        'approved_at' => 'datetime:Y-m-d, H:m',
        'is_enable_request'=> 'boolean',
        'is_agree'=> 'boolean',
    ];
    
    /**
     * for convert logo in full url
     *
     * @param  mixed $logo
     * @return void
     */
    public function getLogoAttribute($logo)
	{
		if(!empty($logo)) {
			$url = \Storage::disk(config('constant.DISK'))->url($logo);
		} else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
    	return $url;
	}

    public function setIsEnableRequestAttribute($value)
	{
        $this->attributes['is_enable_request'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

    public function setVisibilityAttribute($visibility)
	{
        $this->attributes['visibility'] = filter_var($visibility, FILTER_VALIDATE_BOOLEAN);
	}
    
    
    /**
     * getVisibilityAttribute
     *
     * @param  mixed $visibility
     * @return string
     */
    public function getVisibilityAttribute($visibility)
	{
		if($visibility) {
			$str = "Private";
		} else {
            $str = "Public";
        }
    	return $str;
	}
    
    /**
     * getIsApprovedAttribute
     *
     * @param  mixed $isApproved
     * @return string
     */
    public function getIsApprovedAttribute($isApproved)
	{
		if($isApproved == 1) {
			$str = "Approved";
		} else if($isApproved == 2) {
			$str = "Rejected";
		} else {
            $str = "Pending";
        }
    	return $str;
	}
    
    /**
     * getStatusAttribute
     *
     * @param  mixed $status
     * @return void
     */
    public function getStatusAttribute($status)
	{
		if($status) {
			$str = "Active";
		} else {
            $str = "Inactive";
        }
    	return $str;
	}
    
    /**
     * get contact detail
     *
     * @return void
     */
    public function contact()
    {
        return $this->hasOne(SiteContactDetail::class);
    }

    /**
     * for get images
     *
     * @return void
     */
    public function images()
    {
        return $this->hasMany(SiteImage::class);
    }
    
    /**
     * members
     *
     * @return void
     */
    public function members()
    {
        return $this->hasMany(SiteUser::class);
    }
    
    /**
     * posts
     *
     * @return void
     */
    public function posts()
    {
        return $this->hasMany(SiteContent::class)->whereType(1);
    }
    
    /**
     * socials
     *
     * @return void
     */
    public function socials()
    {
        return $this->hasMany(SocialMedia::class);
    }

    /**
     * for get user detail
     *
     * @return void
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    
    /**
     * approvedBy
     *
     * @return void
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    /**
     * assetType
     *
     * @return void
     */
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
    
    /**
     * boot
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::created (function($site)
        {
            $role = Role::where('name','like','creator')->first();
            $data = [
                'site_id' => $site->id,
                'user_id' => $site->created_by,
                'role_id' => ($role) ? $role->id : NULL
            ];
            SiteUser::create($data);
        });

        static::updating (function($site)
        {
            if($site->isDirty('created_by')){
                $role = Role::where('name','like','creator')->first();
                $query = [
                    'site_id' => $site->id,
                    'user_id' => $site->created_by
                ];
                $data = $query;
                $data['role_id'] = ($role) ? $role->id : NULL;
                SiteUser::updateOrCreate($query,$data);
                SiteUser::where('site_id',$site->id)->where('user_id',$site->getOriginal('created_by'))->delete();
            }
        });
    }

    public function scopeGetAll($query, Request $request, $count =false, $byAdmin = false)
	{
        if(!$byAdmin) {
            $query->where('status',1)->where('is_approved',1);
        }

		if(!empty($request->created_by))
		{
			$query->where('sites.created_by', $request->created_by);
		}		
		$visibility = $request->visibility;
		if(!empty($visibility) || ($visibility == 0 && is_numeric($visibility)))
		{
            $query->where('sites.visibility', $visibility);
		}
        
        $is_approved = $request->is_approved;
        if(!empty($is_approved) || ($is_approved == 0 && is_numeric($is_approved)))
		{
			$query->where('sites.is_approved', $is_approved);
		}

		if(!empty($request->asset_type_id))
		{
			$query->where('sites.asset_type_id', $request->asset_type_id);
		}

        if(!empty($request->searchText)) {
			$search = $request->searchText;
            $query->where('sites.name', 'like', '%' . $search . '%');
		}

        if(!empty($request->sortBy)) {
			$order = ($request->sortBy == "AtoZ") ? 'asc' : 'desc' ;
            $query->orderBy('sites.name',$order);
		} else if(!$byAdmin) {
            $query->orderBy('sites.id','desc');
        }
        

		if (!$count && !empty($request->skip)) {
			$query->skip($request->skip)->take($request->take);
		}

		$query->has('createdBy');

		if($count) {
			$data = $query->count();
		} else {
            $query->select('sites.id','sites.name','sites.description','sites.logo','sites.created_by','sites.created_at','sites.visibility','asset_type_id','is_enable_request');
            if($byAdmin) {
                $data = $query->with('createdBy:id,name','assetType')->addSelect('is_approved','approved_at','sites.status');
            } else {
                $userId = 0;
                if(\Auth::guard('api')->check()) {
                    $userId = \Auth::guard('api')->id();
                }
                $data = $query->with('createdBy:id,name','assetType')->addSelect(\DB::raw("(SELECT EXISTS(SELECT id FROM site_users WHERE site_users.user_id = ".$userId." and site_users.site_id = sites.id limit 1)) as is_joined_user"),\DB::raw("(SELECT EXISTS(SELECT id FROM site_requests WHERE site_requests.user_id = ".$userId." and site_requests.site_id = sites.id limit 1)) as is_joined_request"))->get();
            }
		}
		return $data;
	}
}
