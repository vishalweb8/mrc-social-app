<?php

namespace App;

use Auth;
use Config;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Http\Request;
use JWTAuth;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;
    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'status','name', 'created_by', 'isRajput', 'email', 'password','country_code', 'phone', 'dob', 'occupation', 'profile_pic', 'subscription','designation','work_company','city','state','district','country','education','associated_orgs','interest','gender','agent_approved','manual_entry','notification', 'reset_password_otp', 'reset_password_otp_date', 'sql_query','location_id','facebook_id','facebook_id','google_id','google_token','apple_id','apple_token','is_verified_phone'];
 
    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $cascadeDeletes = ['userMetaData', 'getInvestmentIdeasData', 'investmentIdeasInterest', 'getBusinessRatings','businesses','agentUser'];

    /**
	 * for add custom key
	 *
	 * @var array
	 */
	protected $appends = [ 'profile_url'];
	
	/**
	 * add custom profile url key and value
	 *
	 * @return void
	 */
	public function getProfileUrlAttribute()
	{
		$url = '';
		if(!empty($this->profile_pic)) {
			$url = \Storage::disk(config('constant.DISK'))->url(config('constant.USER_ORIGINAL_IMAGE_PATH').$this->profile_pic);
		}
    	return $url;
	}

    // public function setPasswordAttribute($password) {
    //     $this->attributes['password'] = bcrypt($password);
    // }

    /**
     * Insert and Update User
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) {
            $updateData = [];
            foreach ($this->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            return User::where('id', $data['id'])->update($updateData);
        } else {
            $data['created_by'] = Auth::id();
            return User::create($data);
        }
    }

    /**
     * get all User for admin
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $selectFields = [
            'users.id',
            'users.name',
            'users.country_code',
            'users.phone',
            'users.deleted_at',
            'users.created_at',
            'users.status',
            'users.agent_approved',
            'users.isRajput',
            \DB::raw('(CASE WHEN business.id IS NULL THEN 0 ELSE 1 END) AS isVendor'),
            \DB::raw("(CASE 
                WHEN business.membership_type IS NULL THEN '-'
                WHEN business.membership_type = 2 THEN 'LifeTime Premium'
                WHEN business.membership_type = 1 THEN 'Premium'
                WHEN business.membership_type = 0 THEN 'Basic'
                ELSE '-' 
                END) AS membership_type")
        ];
        /*
        $User= USer::leftJoin('business', 'users.id', '=', 'business.user_id')
                ->whereHas('user_role', function ($query) {
                    $query->where('role_id', Config::get('constant.USER_ROLE_ID'));
                })->orderBy('id', 'DESC');
                */
        
        $User= User::leftJoin('business', 'users.id', '=', 'business.user_id')
                //->join('user_role', 'users.id', '=', 'user_role.user_id')
                //->where('user_role.role_id', Config::get('constant.USER_ROLE_ID'))
                ->whereNull('business.deleted_at')
                ->orderBy('users.id', 'DESC')
                ->with('roles');

        if(isset($filters) && !empty($filters)) 
        { 
            if(isset($filters['deleted']) && $filters['deleted'] == 'all')
            {
                $User->withTrashed();
            }
            if(isset($filters['created_by']) && isset($filters['user_ids']))
            {
                $User->where('created_by',$filters['created_by'])->orWhereIn('id',$filters['user_ids']);
            }            
        }
        else
        {
            $User->where('status','1');
        }

        if(isset($filters['country_code']))
        {
            $User->where('users.country_code', $filters['country_code']);
        }

        if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $User->whereRaw(Auth::user()->sql_query);
        }
        
        $User->select($selectFields);

        if(isset($paginate) && $paginate == true) {
            return $User->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $User->get();
        }
    }

    public function user_role()
    {
        return $this->hasOne('App\UserRole');
    }

    public function jobVacancy()
    {
        return $this->hasMany(JobVacancy::class, 'user_id');
    }

    public function jobapplied()
    {
        return $this->hasMany(JobApplied::class, 'user_id');
    }

    public function userMetaData()
    {
        return $this->hasOne('App\UserMetaData', 'user_id');
    }

    public function getInvestmentIdeasData()
    {
        return $this->hasMany('App\InvestmentIdeas','user_id');
    }

    public function investmentIdeasInterest()
    {
        return $this->hasMany('App\InvestmentIdeasInterest', 'user_id');
    }

    public function getBusinessRatings()
    {
        return $this->hasMany('App\BusinessRatings', 'user_id');
    }

    public function getActiveUserSubscription()
    {
        return User::where('subscription', '1')->get();
    }

    public function checkCurrentPassword($userId,$password)
    {
        if ($user = JWTAuth::attempt(['id' => $userId, 'password' => $password]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function devices()
    {
        return $this->hasMany(UsersDevice::class);
    }

    public function businesses()
    {
        return $this->hasMany('App\Business');
    }

    public function singlebusiness()
    {
        return $this->hasOne('App\Business');
    }
    
    public function location()
    {
        return $this->belongsTo(location::class, 'location_id');
    }

    public function agentRequest()
    {
        return $this->hasOne('App\AgentRequest');
    }

    public function agentUser()
    {
        return $this->hasOne('App\AgentUser','user_id');
    }
    
    /**
     * for check login user is super admin or not
     *
     * @return void
     */
    public function isSuperAdmin() {
        return $this->hasRole(['Super Admin']);
    }

    public function userFilter($postData)
    {
        $selectFields = [
            'users.id',
            'users.name',
            'users.country_code',
            'users.phone',
            'users.deleted_at',
            'users.created_at',
            'users.status',
            'users.agent_approved',
            'users.isRajput',
            \DB::raw('(CASE WHEN business.id IS NULL THEN 0 ELSE 1 END) AS isVendor'),
            \DB::raw("(CASE 
                WHEN business.membership_type IS NULL THEN '-'
                WHEN business.membership_type = 2 THEN 'LifeTime Premium'
                WHEN business.membership_type = 1 THEN 'Premium'
                WHEN business.membership_type = 0 THEN 'Basic'
                ELSE '-' END) AS membership_type")
        ];

        /*
        $User = User::leftJoin('business', 'users.id', '=', 'business.user_id')
                ->whereHas('user_role', function ($query) {
                    $query->where('role_id', Config::get('constant.USER_ROLE_ID'));
                })->orderBy('users.id', 'DESC');
        */
        $User= User::leftJoin('business', function($join) {
                    $join->on('users.id', '=', 'business.user_id');
                    $join->whereNull('business.deleted_at');
                })
                ->orderBy('users.id', 'DESC')->with('roles');

        if(isset($postData['searchtext']) && $postData['searchtext'] != '')
        {
            $User->where(function($query) use ($postData){
                $query->where('users.name', 'like', '%'.$postData['searchtext'].'%')
                    ->orWhere('users.email', 'like', '%'.$postData['searchtext'].'%')
                    ->orWhere('users.phone', 'like', '%'.$postData['searchtext'].'%');
                    
            });
            $User->where('users.status','1');
        }
        if(isset($postData['usertype']) && $postData['usertype'] != '')
        {
            if($postData['usertype'] == 'vendor')
            {
                $User->whereIn('users.id',function($query){
                   $query->select('user_id')->from('business');
                });
                $User->where('users.status','1');
            }

            if($postData['usertype'] == 'customer')
            {
                $User->whereNotIn('users.id',function($query){
                   $query->select('user_id')->from('business');
                });
                $User->where('users.status','1');
            }

            if($postData['usertype'] == 'agent')
            {
                $User->where('users.agent_approved', 1)->where('status','1');
            }

            if($postData['usertype'] == 'deactive')
            {
                $User->where('users.status','0');
            }

            if($postData['usertype'] == 'active')
            {
                $User->where('users.status','1');
            }

            if($postData['usertype'] == 'deleted')
            {
                $User->onlyTrashed();
            }

        }
        if(isset($postData['country_code']) && !empty($postData['country_code']))
        {
            $User->where('users.country_code', $postData['country_code']);
        }
        if(isset($postData['created_by']))
        {
            $User->where('users.created_by',$postData['created_by']);
        }

        if(isset($postData['fieldname']) && $postData['fieldname'] != '' && isset($postData['fieldtype']) && $postData['fieldtype'] != '')
        {
            
            if($postData['fieldtype'] == 0) 
            {
                if($postData['fieldname'] == 'gender' || $postData['fieldname'] == 'isRajput')
                {
                    $User->where('users.'.$postData['fieldname'],0);
                }   
                else
                {
                    $User->whereNull('users.'.$postData['fieldname']);    
                }
            }                                
            else
            {
                if($postData['fieldname'] == 'gender' || $postData['fieldname'] == 'isRajput')
                {
                    $User->where('users.'.$postData['fieldname'],1);
                }   
                else
                {
                    $User->whereNotNull('users.'.$postData['fieldname']);   
                }
                
            }
        }

        $User->select($selectFields);
        return $User->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));

    }

    public function getUsers(Request $request, $withBusiness = true)
    {
        $users = User::select('users.id','users.name','users.phone','users.country_code')
        ->where('users.name','<>','');
        if($withBusiness) {
            $users->with('singlebusiness');
        }

        if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $users->whereHas('singlebusiness', function ($query) {
                $query->whereRaw(Auth::user()->sql_query);
            });         
        }

        $senderType = $request->sender_type;
        if($senderType == 'all_business' || $senderType == 'target_business') {
            $users->has('singlebusiness');
        } else if($senderType == 'all_member' || $senderType == 'target_member') {
            $users->doesnthave('singlebusiness');
        } 
        
        if(!empty($request->user_id)) {
            $ids = $request->user_id;                    
            $users->whereIn('users.id',$ids);
        }

        if(!empty($request->education)) {
            $educations = $request->education;                    
            $users->whereIn('users.education',$educations);
        }
        if(!empty($request->gender)) {               
            $users->where('users.gender',$request->gender);
        }                      

        if(!empty($request->business_id)) {
            $ids = $request->business_id;                    
            $users->whereHas('singlebusiness', function ($query) use ($ids) {
                $query->whereIn('business.id',$ids);
            });
        }

        if($senderType == 'target_member') {

            if(!empty($request->country)) {              
                $users->whereIn('users.country',$request->country);
            }

            if(!empty($request->state)) {              
                $users->whereIn('users.state',$request->state);
            }
            if(!empty($request->city)) {              
                $users->whereIn('users.city',$request->city);
            }
            if(!empty($request->district)) {              
                $users->whereIn('users.district',$request->district);
            }
        } else {
            if(!empty($request->country)) {              
                $users->whereHas('singlebusiness', function ($query) use ($request) {
                    $query->whereIn('business.country',$request->country);
                });
            }

            if(!empty($request->state)) {              
                $users->whereHas('singlebusiness', function ($query) use ($request) {
                    $query->whereIn('business.state',$request->state);
                });
            }
            if(!empty($request->city)) {              
                $users->whereHas('singlebusiness', function ($query) use ($request) {
                    $query->whereIn('business.city',$request->city);
                });
            }
            if(!empty($request->district)) {              
                $users->whereHas('singlebusiness', function ($query) use ($request) {
                    $query->whereIn('business.district',$request->district);
                });
            }
        }

        if(!empty($request->membership_type)) {              
            $users->whereHas('singlebusiness', function ($query) use ($request) {
                $query->where('business.membership_type',$request->membership_type);
            });
        }

        if(!empty($request->caste)) {              
            $users->whereHas('singlebusiness.owners', function ($query) use ($request) {
                $query->whereIn('kul_gotra',$request->caste);
            });
        }

        if(!empty($request->category_id)) {
            $categoryIds = $request->category_id;
            // $users->whereHas('singlebusiness', function ($query) use ($categoryId) {
            //     $query->whereRaw("FIND_IN_SET(".$categoryId.", business.parent_category)");
            // });

            $whereArr = [];
            foreach ($categoryIds as $id) {
                $whereArr[] = "FIND_IN_SET(" . $id . ", business.parent_category)";
            }
    
            if (!empty($whereArr)) {
                $whereStr = implode(' OR ', $whereArr);
                $users->whereHas('singlebusiness', function ($query) use ($whereStr) {
                    $query->whereRaw($whereStr);
                });
            }
        }  

        if(!empty($request->sub_category_id)) {
            $subCategoryIds = $request->sub_category_id;
            $whereArr = [];
            foreach ($subCategoryIds as $id) {
                $whereArr[] = "FIND_IN_SET(" . $id . ", business.category_id)";
            }
    
            if (!empty($whereArr)) {
                $whereStr = implode(' OR ', $whereArr);
                $users->whereHas('singlebusiness', function ($query) use ($whereStr) {
                    $query->whereRaw($whereStr);
                });
            }
        }

        if(!empty($request->meta_tags)) {
            $tags = explode(',',$request->meta_tags);
            $whereArr = [];
            foreach ($tags as $tag) {
                $whereArr[] = "FIND_IN_SET('" . $tag . "', business.metatags)";
            }
    
            if (!empty($whereArr)) {
                $whereStr = implode(' OR ', $whereArr);
                $users->whereHas('singlebusiness', function ($query) use ($whereStr) {
                    $query->whereRaw($whereStr);
                });
            }
        }

        if(!empty($request->age_groups)) {
            $ages = explode(',',$request->age_groups);
            $whereArr = [];
            foreach ($ages as $age) {
                if(!empty($age)) {
                    $ageRange = explode('-',$age);
                    if(isset($ageRange[0]) && isset($ageRange[1]) && is_numeric($ageRange[0])  && is_numeric($ageRange[1])) {
                        $whereArr[] = "timestampdiff(year, dob, curdate()) between ".$ageRange[0]." and ".$ageRange[1];
                    }
                }                
            }
    
            if (!empty($whereArr)) {
                $whereStr = implode(' OR ', $whereArr);
                $users->whereRaw($whereStr);
            }
        }

        return $users;
    }

}
