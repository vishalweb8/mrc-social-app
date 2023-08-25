<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicPost extends Model
{
	use SoftDeletes;

    protected $fillable = ['user_id','title','category','content','source','external_link','post_keywords','moderator_keywords','type','post_type','status','city','latitude','longitude','share_count','views_count'];

	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    //protected $appends = [ 'views_count'];

    public function getShareCountAttribute()
	{
		$count = '';
		if($this->attributes['share_count'] > 0) {
            $count = thousandsCurrencyFormat($this->attributes['share_count']);
        }
    	return $count;
	}

    public function getViewsCountAttribute()
	{
		$count = '';
        if($this->attributes['views_count'] > 0) {
            $count = thousandsCurrencyFormat($this->attributes['views_count']);
        }
    	return $count;
	}
    
    /**
     * for get video
     *
     * @return void
     */
    public function videos()
    {
        return $this->hasOne(PublicPostVideo::class,'post_id');
    }

      /**
     * for get images
     *
     * @return void
     */
    public function images()
    {
        return $this->hasMany(PublicPostImage::class,'post_id');
    }

    /**
     * for get user detail
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * for get post likes
     *
     * @return void
     */
    public function likes()
    {
        return $this->belongsToMany(User::class,'public_post_likes','post_id','user_id')->orderBy('public_post_likes.id','desc');
    }

    /**
     * for get post views
     *
     * @return void
     */
    public function views()
    {
        return $this->belongsToMany(User::class,'public_post_views','post_id','user_id');
    }

	public function scopeGetAll($query, $filters, $count =false)
	{
   
        $status = 'active';
        $select = [];
        $userId = 0;
        if(\Auth::guard('api')->check()) {
            $userId = \Auth::guard('api')->id();
        }
        if(isset($filters['post_id']) && !empty($filters['post_id']))
		{
			$query->where('public_posts.id', $filters['post_id']);
		}		
		if(isset($filters['user_id']) && !empty($filters['user_id']))
		{
			$query->where('user_id', $filters['user_id']);
		}		
		
		if(isset($filters['category']) && !empty($filters['category']))
		{
			$query->where('category', $filters['category']);
		}
		
        
		if(isset($filters['type']) && !empty($filters['type']))
		{
            $query->where('public_posts.type', $filters['type']);
		}
        
        if(isset($filters['post_type']) && !empty($filters['post_type']) && !isset($filters['site_id']))
		{
            $query->where('post_type', $filters['post_type']);
		}
        
        if(isset($filters['site_id']) && !empty($filters['site_id']))
		{
            $query->join('site_contents','site_contents.content_id','=','public_posts.id')->where('site_contents.site_id',$filters['site_id']);
            $select = [
                \DB::raw("(SELECT EXISTS(SELECT * FROM public_post_likes WHERE public_post_likes.user_id = ".$userId." and public_post_likes.post_id = public_posts.id limit 1)) as is_like_by_user"),
                'site_contents.shared_by as user_id'
            ];
            $query->with('user.singlebusiness:id,name,user_id,business_slug,business_logo');
		}
        
		if(isset($filters['status']) && !empty($filters['status']))
		{
            $status = $filters['status'];
		}

		// if(isset($filters['title']) && !empty($filters['title']))
		// {
		// 	$query->where('title', $filters['title']);
		// }
        
        // if(isset($filters['source']) && !empty($filters['source']))
        // {
        // 	$query->where('source', $filters['source']);
        // }
		// if(isset($filters['post_keywords']) && !empty($filters['post_keywords']))
		// {
            // 	$keywords = $filters['post_keywords'];
            // 	$query->whereRaw('FIND_IN_SET(?,post_keywords)', [$keywords]);
            // }
            
		// if(isset($filters['moderator_keywords']) && !empty($filters['moderator_keywords']))
		// {
            // 	$query->where('moderator_keywords', $filters['moderator_keywords']);
            // }
            
		// if (isset($filters['searchText']) && $filters['searchText'] != '') {
		// 	$search = $filters['searchText'];
		// 	$query->where(function($q) use ($search) {
		// 		$q->orWhere('title', 'like', '%' . $search . '%');
		// 		$q->orWhere('category', 'like', '%' . $search . '%');
		// 		$q->orWhere('content', 'like', '%' . $search . '%');
		// 		$q->orWhere('source', 'like', '%' . $search . '%');
		// 		$q->orWhere('moderator_keywords', 'like', '%' . $search . '%');
		// 		$q->orWhereRaw('FIND_IN_SET(?,post_keywords)', [$search]);
		// 	});
		// }

		if (isset($filters['skip']) && $filters['skip'] >= 0 && isset($filters['take']) && $filters['take'] > 0 && $count == false) {
			$query->skip($filters['skip'])->take($filters['take']);
		}

		if(isset($filters['sortBy']) && !empty($filters['order']) && in_array($filters['sortBy'],['title','category','source','moderator_keywords']))
		{
			$query->orderBy($filters['sortBy'],$filters['order']);
		} elseif (isset($filters['near_by']) && $filters['near_by'] == true && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {

            $query->selectRaw('public_posts.*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

            if (isset($filters['radius']) && !empty($filters['radius'])) {
                $query->having('distance', '<', $filters['radius']);
            }
            $query->orderBy('public_posts.id', 'desc')->orderBy('distance', 'asc');
            
        } else {
			$query->orderBy('public_posts.id', 'desc');
		}
		$query->where('status', $status)->has('user');

		if($count) {
			$data = $query->count();
		} else {            
			$query->withCount('likes')
            ->with(['user:id,name,profile_pic','images:id,post_id,url','videos:id,post_id,url']);
            if(!empty($select)) {
                $query->addSelect($select);
            } else {
                $query->addSelect(\DB::raw("(SELECT EXISTS(SELECT * FROM public_post_likes WHERE public_post_likes.user_id = ".$userId." and public_post_likes.post_id = public_posts.id limit 1)) as is_like_by_user"),\DB::raw('(select id from business where user_id = public_posts.user_id and deleted_at is null limit 1) as business_id'),\DB::raw('(select business_slug from business where user_id = public_posts.user_id and deleted_at is null limit 1) as business_slug'),\DB::raw('(select name from business where user_id = public_posts.user_id and deleted_at is null limit 1) as business_name'));
            }
            $data = $query->get();
		}
		return $data;
	}
}
