<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;
use Storage;
use App\Chats;
use App\Category;
use App\AdvertisementCategory;
use Cviebrock\EloquentSluggable\Sluggable;

class Advertisement extends Model
{
    //
    use SoftDeletes, CascadeSoftDeletes, Sluggable;

    protected $table = 'advertisements';

    protected $fillable = [
        'ads_type', 'user_id', 'name', 'descriptions', 'ads_slug', 'price', 'address', 'street_address',
        'country', 'state', 'city', 'pincode', 'latitude', 'longitude', 'promoted', 'interest_count', 'visit_count',
        'approved', 'approved_by', 'category_hierarchy', 'is_closed'
    ]; 

    protected $dates = ['deleted_at']; 

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function advertisementImages()
    {
        return $this->hasMany(AdvertisementImage::class);
    }
    
    public function advertisementImagesById()
    {
        return $this->hasOne('App\AdvertisementImage', 'advertisement_id');
    }

    public function advertisementVideos()
    {
        return $this->hasMany(AdvertisementVideo::class);
    }
    
    public function advertisementCategories()
    {
        return $this->hasMany(AdvertisementCategory::class, 'advertisement_id');
    }
    
    public function userInterestInAdvertisement()
    {
        return $this->hasMany(UserInterestInAdvertisement::class, 'advertisement_id');
    }
    
    public function getChats()
    {
        return $this->hasMany('App\Chats', 'advertisement_id');
    }
 

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'ads_slug' => [
                'source' => 'name'
            ]
        ];
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
            Advertisement::where('id', $data['id'])->update($updateData);
            return Advertisement::find($data['id']);
        } else {
            $data['created_by'] = Auth::id();
            $data['is_closed'] = 0;
            return Advertisement::create($data);
        }
    }

    /**
     * Retrive the advertisement by ads id.
     * 
     * @param: 
     *  $adsId: Id of request advertisement
     *  $approved: Its optional parameter, which helps to retrive data based on user access level api.
     */
    public function frontendFindById($adsId, $includeApprovedOnly = false, $loggedInUserId = 0)
    {
        $selectFields = [
            'advertisements.id',
            'advertisements.ads_type',
            DB::raw("(CASE advertisements.ads_type 
                            WHEN 0 THEN '" . Config::get('constant.ADVERTISEMENT_BUY_TYPE') . "'
                            WHEN 1 THEN '" . Config::get('constant.ADVERTISEMENT_SELL_TYPE') . "'
                            WHEN 2 THEN '" . Config::get('constant.ADVERTISEMENT_SERVICE_TYPE') . "'
                        END) AS ads_type_text"),
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
            DB::raw("IFNULL(advertisements.ads_slug, '') AS ads_slug"), 
            DB::raw("IFNULL(advertisements.price, '') AS price"), 
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address"),
            'advertisements.country', 'advertisements.state', 
            DB::raw("IFNULL(state.name, '') AS state_name"), 
            DB::raw("IFNULL(country.name, '') AS country_name"), 
            DB::raw("IFNULL(advertisements.city, '') AS city"), 
            DB::raw("IFNULL(advertisements.pincode, '') AS pincode"), 
            DB::raw("IFNULL(advertisements.latitude, '') AS latitude"), DB::raw("IFNULL(advertisements.longitude, '') AS longitude"), 
            'advertisements.promoted', 
            'advertisements.interest_count', 'advertisements.visit_count',  
            'advertisements.approved', DB::raw("IFNULL(advertisements.approved_by, '') AS approved_by"), 
            'advertisements.created_at AS created_at', 'advertisements.updated_at AS updated_at',
            DB::raw('(UNIX_TIMESTAMP(advertisements.created_at) * 1000) AS created_date'), 
            DB::raw('(UNIX_TIMESTAMP(advertisements.updated_at) * 1000) AS updated_date'),
            DB::raw('users.id AS owner_id'), DB::raw('users.name AS owner_name'), 
            DB::raw('users.email AS owner_email'),  DB::raw('users.country_code AS country_code'), 
            DB::raw('users.phone AS owner_contact_number'), 
            DB::raw("IFNULL(users.profile_pic, '') AS owner_profile_image"),
            DB::raw("IFNULL(parent_category.category_id, '') AS parent_category_ids"),
            DB::raw("IFNULL(parent_category.category_name, '') AS parent_category_names"),
            DB::raw("IFNULL(child_category.category_id, '') AS child_category_ids"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            DB::raw("IFNULL(business.id, '') AS business_id"),
            DB::raw("IFNULL(business.name, '') AS business_name"),
            DB::raw("IFNULL(business.business_slug, '') AS business_slug"),
            'advertisements.category_hierarchy',
            DB::raw("IFNULL(chat_thread.id, 0) AS thread_id"),
            'advertisements.is_closed'         
        ];

          if ($loggedInUserId > 0) {
            array_push($selectFields,DB::raw("(CASE WHEN is_interest.advertisement_id  IS NULL THEN 0
                            WHEN is_interest.advertisement_id > 0 THEN 1
                        END) AS is_interest"));
         }

        $queryBuilder = Advertisement::join('users', 'users.id', '=', 'advertisements.user_id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state')
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 0 
                        GROUP BY advertisement_id) AS parent_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'parent_category.advertisement_id');
                        })
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 1 
                        GROUP BY advertisement_id) AS child_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'child_category.advertisement_id');
                        })
            ->leftJoin('business', function($join)
            {
                $join->on('business.user_id', '=', 'advertisements.user_id');
                $join->where('business.approved', 1);
            })                 
            ->with(["advertisementImages" => function($query) {
                $query->addSelect("id",
                        "advertisement_id",
                        'image_name',                        
                        DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH')) . "', image_name) ELSE '' END) AS original_url"),
                        DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". Storage::disk(config('constant.DISK'))->url(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH')) . "', image_name) ELSE '' END) AS thumbnail_url")
                    );
            }])
            ->with("advertisementVideos:id,advertisement_id,video_link")
            ->with(["advertisementVideos" => function($query) {
                $query->select(['id', 'advertisement_id', 'video_link AS url', 'video_id', 'thumbnail']);
            }])
            ->where('advertisements.id', '=', $adsId);
            						
            if ($loggedInUserId > 0) {
                $queryBuilder->leftJoin(DB::raw("(SELECT advertisement_id, user_id
                            FROM `user_interest_in_advertisement` ac 
                            WHERE user_id = '".$loggedInUserId."') AS is_interest"),
                            function($join)
                            {
                                $join->on('advertisements.id', '=', 'is_interest.advertisement_id');
                            });
                $queryBuilder->leftJoin(DB::raw("(SELECT id,advertisement_id, member_id
                            FROM `chats` cc 
                            WHERE member_id = '".$loggedInUserId."') AS chat_thread"),
                            function($join)
                            {
                                $join->on('advertisements.id', '=', 'chat_thread.advertisement_id');
                            });
            }
        // if($includeApprovedOnly) {
        //     $queryBuilder->where('advertisements.approved', '=', 1);
        // }

        $queryBuilder->select($selectFields);

        return $queryBuilder->first();
    }

    /**
     * Retrive the advertisement by ads id.
     * 
     * @param: 
     *  $adsId: Id of request advertisement
     *  $approved: Its optional parameter, which helps to retrive data based on user access level api.
     */
    public function backendFindById($adsId)
    {
        $selectFields = [
            'advertisements.id',
            'advertisements.ads_type',
            DB::raw("(CASE advertisements.ads_type 
                            WHEN 0 THEN '" . Config::get('constant.ADVERTISEMENT_BUY_TYPE') . "'
                            WHEN 1 THEN '" . Config::get('constant.ADVERTISEMENT_SELL_TYPE') . "'
                            WHEN 2 THEN '" . Config::get('constant.ADVERTISEMENT_SERVICE_TYPE') . "'
                        END) AS ads_type_text"),
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
            DB::raw("IFNULL(advertisements.ads_slug, '') AS ads_slug"), 
            DB::raw("IFNULL(advertisements.price, '') AS price"), 
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address"),
            'advertisements.country', 'advertisements.state', 
            DB::raw("IFNULL(state.name, '') AS state_name"), 
            DB::raw("IFNULL(country.name, '') AS country_name"), 
            DB::raw("IFNULL(advertisements.city, '') AS city"), 
            DB::raw("IFNULL(advertisements.pincode, '') AS pincode"), 
            DB::raw("IFNULL(advertisements.latitude, '') AS latitude"), DB::raw("IFNULL(advertisements.longitude, '') AS longitude"), 
            'advertisements.promoted', 
            'advertisements.interest_count', 'advertisements.visit_count',  
            'advertisements.approved', DB::raw("IFNULL(advertisements.approved_by, '') AS approved_by"), 
            'advertisements.created_at', 'advertisements.updated_at',
            DB::raw('(UNIX_TIMESTAMP(advertisements.created_at) * 1000) AS created_date'), 
            DB::raw('(UNIX_TIMESTAMP(advertisements.updated_at) * 1000) AS updated_date'),
            DB::raw('users.id AS owner_id'), DB::raw('users.name AS owner_name'), 
            DB::raw('users.email AS owner_email'),  DB::raw('users.country_code AS country_code'), 
            DB::raw('users.phone AS owner_contact_number'), DB::raw("IFNULL(users.profile_pic, '') AS owner_profile_image"),
            DB::raw("IFNULL(parent_category.category_id, '') AS parent_category_ids"),
            DB::raw("IFNULL(parent_category.category_name, '') AS parent_category_names"),
            DB::raw("IFNULL(child_category.category_id, '') AS child_category_ids"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            DB::raw("IFNULL(business.id, '') AS business_id"),
            DB::raw("IFNULL(business.name, '') AS business_name"),
            DB::raw("IFNULL(business.business_slug, '') AS business_slug"),
            'advertisements.category_hierarchy',
            'advertisements.deleted_at',
            'advertisements.is_closed'
        ];

        $queryBuilder = Advertisement::join('users', 'users.id', '=', 'advertisements.user_id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state')
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 0 
                        GROUP BY advertisement_id) AS parent_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'parent_category.advertisement_id');
                        })
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 1 
                        GROUP BY advertisement_id) AS child_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'child_category.advertisement_id');
                        })
            ->leftJoin('business', function($join)
            {
                $join->on('business.user_id', '=', 'advertisements.user_id');
                $join->where('business.approved', 1);
            })              
            ->withTrashed()
            ->with("advertisementImages:id,advertisement_id,image_name", "advertisementVideos:id,advertisement_id,video_link")            
            ->where('advertisements.id', '=', $adsId);

        $queryBuilder->select($selectFields);

        return $queryBuilder->first();
    }

    public function geAllWithFilterForAdmin($filters = array(), $paginate = false) {

        $selectFields = [
            'advertisements.id',
            'advertisements.ads_type',
            DB::raw("(CASE advertisements.ads_type 
                            WHEN 0 THEN '" . Config::get('constant.ADVERTISEMENT_BUY_TYPE') . "'
                            WHEN 1 THEN '" . Config::get('constant.ADVERTISEMENT_SELL_TYPE') . "'
                            WHEN 2 THEN '" . Config::get('constant.ADVERTISEMENT_SERVICE_TYPE') . "'
                        END) AS ads_type_text"),
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
            DB::raw("IFNULL(advertisements.ads_slug, '') AS ads_slug"), 
            DB::raw("IFNULL(advertisements.price, '') AS price"), 
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address"),
            'advertisements.country', 'advertisements.state', 
            DB::raw("IFNULL(state.name, '') AS state_name"), 
            DB::raw("IFNULL(country.name, '') AS country_name"), 
            DB::raw("IFNULL(advertisements.city, '') AS city"), 
            DB::raw("IFNULL(advertisements.pincode, '') AS pincode"), 
            DB::raw("IFNULL(advertisements.latitude, '') AS latitude"), DB::raw("IFNULL(advertisements.longitude, '') AS longitude"), 
            'advertisements.promoted', 
            'advertisements.interest_count', 'advertisements.visit_count',  
            'advertisements.approved', DB::raw("IFNULL(advertisements.approved_by, '') AS approved_by"), 
            'advertisements.created_at', 'advertisements.updated_at', 'advertisements.deleted_at',
            DB::raw('(UNIX_TIMESTAMP(advertisements.created_at) * 1000) AS created_date'), 
            DB::raw('(UNIX_TIMESTAMP(advertisements.updated_at) * 1000) AS updated_date'),
            'users.name AS user_full_name',
            'advertisements.category_hierarchy',
            'advertisements.is_closed'
        ];

        $queryBuilder = Advertisement::join('users', 'users.id', '=', 'advertisements.user_id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state')
            ->withTrashed();

        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $queryBuilder->whereHas('user.singlebusiness', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
    
        if (isset($filters) && !empty($filters)) {
            if (isset($postData['fieldtype']) && $postData['fieldtype'] != '' && isset($postData['isNull']) && $postData['isNull'] != '') {
                if ($postData['isNull'] == 0) {
                    $queryBuilder->whereNull($filters['fieldtype']);
                } else {
                    $queryBuilder->whereNotNull($filters['fieldtype']);
                }
            }           

            if (isset($filters['city']) && $filters['city'] != '') {
                $queryBuilder->where('city', $filters['city']);
            }
            
            if (isset($filters['approved']) && $filters['approved'] != '') {
                $queryBuilder->where('approved', $filters['approved']);
            }
            
            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $queryBuilder->where('advertisements.user_id', $filters['user_id']);
            }
            
            if (isset($filters['searchText']) && $filters['searchText'] != '') {                
                $queryBuilder->where(function ($queryBuilder) use ($filters) {

                    $queryBuilder->orWhere('advertisements.name', 'like', '%' . $filters['searchText'] . '%');

                    $queryBuilder->orWhere(function ($queryBuilder) use ($filters) {
                        $queryBuilder->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });
                });
            }
            
            if (isset($filters['offset']) && $filters['offset'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $queryBuilder->skip($filters['offset'])->take($filters['take']);
            }
            $queryBuilder->orderBy('advertisements.id', 'DESC');
        } else {
            $queryBuilder->orderBy('advertisements.id', 'DESC');
            $queryBuilder->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        $queryBuilder->select($selectFields);

        if (isset($paginate) && $paginate == true) {
            return $queryBuilder->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        return $queryBuilder->get();
    }

    /**
     * Filter the product based on your passed detail.
     * 
     * @param:
     *      $filters: Array key value pair of search criteria
     * 
     * @return
     *      Returns the list of advertisement data.
     */
    public function getFilterDataForFrontend($filters = array(), $paginate = false) {

        $mod = Config::get('constant.AD_GROUP_BY_DISTANCE_MOD');
        $selectFields = [
            'advertisements.id',
            'advertisements.ads_type',
            DB::raw("(CASE advertisements.ads_type 
                            WHEN 0 THEN '" . Config::get('constant.ADVERTISEMENT_BUY_TYPE') . "'
                            WHEN 1 THEN '" . Config::get('constant.ADVERTISEMENT_SELL_TYPE') . "'
                            WHEN 2 THEN '" . Config::get('constant.ADVERTISEMENT_SERVICE_TYPE') . "'
                        END) AS ads_type_text"),
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
            DB::raw("IFNULL(advertisements.ads_slug, '') AS ads_slug"), 
            DB::raw("IFNULL(advertisements.price, '') AS price"), 
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address"),
            DB::raw("IFNULL(advertisements.country, '') AS country"), DB::raw("IFNULL(advertisements.state, '') AS country"), 
            DB::raw("IFNULL(state.name, '') AS state_name"), 
            DB::raw("IFNULL(country.name, '') AS country_name"), 
            DB::raw("IFNULL(advertisements.city, '') AS city"), 
            DB::raw("IFNULL(advertisements.pincode, '') AS pincode"), 
            DB::raw("IFNULL(advertisements.latitude, '') AS latitude"), DB::raw("IFNULL(advertisements.longitude, '') AS longitude"), 
            'advertisements.promoted', 
            'advertisements.interest_count', 'advertisements.visit_count',  
            'advertisements.approved', DB::raw("IFNULL(advertisements.approved_by, '') AS approved_by"), 
            'advertisements.created_at', 'advertisements.updated_at',
            DB::raw('(UNIX_TIMESTAMP(advertisements.created_at) * 1000) AS created_date'), 
            DB::raw('(UNIX_TIMESTAMP(advertisements.updated_at) * 1000) AS updated_date'),
            'users.name AS user_full_name',
            DB::raw("IFNULL(parent_category.category_id, '') AS parent_category_ids"),
            DB::raw("IFNULL(parent_category.category_name, '') AS parent_category_names"),
            DB::raw("IFNULL(child_category.category_id, '') AS child_category_ids"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            'advertisements.category_hierarchy',
            'advertisements.is_closed'        
        ];

        /*

        if (isset($filters) && !empty($filters)) {       
            if (isset($filters['logged_in_user_id']) && $filters['logged_in_user_id'] > 0) {
                 array_push($selectFields,DB::raw("(CASE WHEN is_interest.advertisement_id  IS NULL THEN 0
                            WHEN is_interest.advertisement_id > 0 THEN 1 END) AS is_interest"));
             }
        }
        */

        $queryBuilder = Advertisement::with(["advertisementImages" => function($query) {
                    $query->addSelect("id",
                            "advertisement_id",
                            'image_name',
                            DB::raw("image_name AS original_url"),
                            DB::raw("image_name AS thumbnail_url")
                        );
                    // $query->addSelect("id",
                    //         "advertisement_id",
                    //         'image_name',
                    //         DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". URL(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH')) . "/', image_name) ELSE '' END) AS original_url"),
                    //         DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". URL(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH')) . "/', image_name) ELSE '' END) AS thumbnail_url")
                    //     );
            }])
            ->join('users', 'users.id', '=', 'advertisements.user_id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state')
            ->leftJoin('city', 'city.name', '=', 'advertisements.city')
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 0 
                        GROUP BY advertisement_id) AS parent_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'parent_category.advertisement_id');
                        })
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 1 
                        GROUP BY advertisement_id) AS child_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'child_category.advertisement_id');
                        });

        // $queryBuilder->whereNull('advertisements.deleted_at');

        if (isset($filters) && !empty($filters)) {       
            /*
            if (isset($filters['logged_in_user_id']) && $filters['logged_in_user_id'] > 0) {
                $queryBuilder->leftJoin(DB::raw("(SELECT advertisement_id, user_id
                            FROM `user_interest_in_advertisement` ac 
                            WHERE user_id = '".$filters['logged_in_user_id']."') AS is_interest"),
                            function($join)
                            {
                                $join->on('advertisements.id', '=', 'is_interest.advertisement_id');
                            });
            }
            */

            if (isset($filters['is_closed']) && $filters['is_closed'] !== '') {
                $queryBuilder->where('advertisements.is_closed', $filters['is_closed']);
            }

            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $queryBuilder->where('advertisements.user_id', $filters['user_id']);
            }
            
            if (isset($filters['ads_type']) && $filters['ads_type'] != '') {
                $queryBuilder->where('ads_type', $filters['ads_type']);
            }

            if (isset($filters['city']) && $filters['city'] != '') {
                $queryBuilder->where('advertisements.city', $filters['city']);
            }
            
            if (isset($filters['approved']) && $filters['approved'] != '') {
                $queryBuilder->where('approved', $filters['approved']);
            }
                                    
            if (isset($filters['category_id']) && $filters['category_id'] != '') {
                $queryBuilder->whereHas('advertisementCategories', function($queryBuilder) use ($filters) {
                    $queryBuilder->where('category_id', '=', $filters['category_id']);
                });
            }
            
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $queryBuilder->where(function ($queryBuilder) use ($filters) {

                    $queryBuilder->orWhere('advertisements.name', 'like', '%' . $filters['searchText'] . '%');

                    $queryBuilder->orWhere(function ($queryBuilder) use ($filters) {
                        $queryBuilder->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });
                });
            }
            
            if (isset($filters['sortBy']) && $filters['sortBy'] != '') {
                if ($filters['sortBy'] == 'promoted') {
                    $queryBuilder->orderBy('advertisements.promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'relevance') {
                     $queryBuilder->orderBy('advertisements.id', 'DESC');
                
                /*
                elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $selectFields[] = DB::raw('IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( advertisements.latitude ) ) * cos( radians( advertisements.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( advertisements.latitude ) ) ) ), "") AS distance');
                    // $selectFields[] = DB::raw('CASE WHEN advertisements.latitude IS NOT NULL 
                    //        THEN 
                    //         (IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( advertisements.latitude ) ) * cos( radians( advertisements.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( advertisements.latitude ) ) ) ), "")% '.$mod.' ) AS distance_mod
                    //        ELSE
                    //         (IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( city.latitude ) ) * cos( radians( city.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( city.latitude ) ) ) ), "")% '.$mod.' ) AS distance_mod');

                    $selectFields[] = DB::raw('(
                            CASE WHEN advertisements.latitude IS NULL OR advertisements.latitude <=> "0" THEN(
                              (
                                FLOOR(
                                  (
                                    6371 * ACOS(
                                      COS(RADIANS('.$filters["latitude"].')) * COS(RADIANS(city.latitude)) * COS(
                                        RADIANS(city.longitude) - RADIANS('.$filters["longitude"].')
                                      ) + SIN(RADIANS('.$filters["latitude"].')) * SIN(RADIANS(city.latitude))
                                    )
                                  ) / '.$mod.'
                                )
                              )
                            ) ELSE(
                              (
                                FLOOR(
                                  (
                                    6371 * ACOS(
                                      COS(RADIANS('.$filters["latitude"].')) * COS(
                                        RADIANS(advertisements.latitude)
                                      ) * COS(
                                        RADIANS(advertisements.longitude) - RADIANS('.$filters["longitude"].')
                                      ) + SIN(RADIANS('.$filters["latitude"].')) * SIN(
                                        RADIANS(advertisements.latitude)
                                      )
                                    )
                                  ) / '.$mod.'
                                )
                              )
                            )
                          END
                        ) AS distance_mod');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $queryBuilder->having('distance', '<', $filters['radius']);
                    }

                    $queryBuilder->orderBy('advertisements.promoted', 'DESC');
                    // $queryBuilder->orderBy(\DB::raw('-`distance`'), 'DESC');
                    $queryBuilder->orderBy('distance_mod', 'ASC');
                    $queryBuilder->orderBy('created_date', 'DESC');
                    */

                } else if ($filters['sortBy'] == 'popular') {
                    $queryBuilder->orderBy('advertisements.visit_count', 'DESC');
                } else if (strtolower($filters['sortBy']) == 'atoz') {
                    $queryBuilder->orderBy('advertisements.name', 'ASC');
                } else if (strtolower($filters['sortBy']) == 'ztoa') {
                    $queryBuilder->orderBy('advertisements.name', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $selectFields[] = DB::raw('IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( advertisements.latitude ) ) * cos( radians( advertisements.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( advertisements.latitude ) ) ) ), "") AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $queryBuilder->having('distance', '<', $filters['radius']);
                    }
                    $queryBuilder->orderBy(\DB::raw('-`distance`'), 'DESC');
                } else {
                    $queryBuilder->orderBy('advertisements.id', 'DESC');
                }                
            } else {
                $queryBuilder->orderBy('advertisements.id', 'DESC');
                // if (isset($filters['user_id']) && $filters['user_id'] != '') {
                //     $queryBuilder->orderBy('advertisements.promoted', 'DESC');
                // }else {
                //     $queryBuilder->orderBy('advertisements.id', 'DESC');
                // }
            }
            if (isset($filters['skip']) && isset($filters['take']) && !empty($filters['take'])) {
                $queryBuilder->skip($filters['skip'])->take($filters['take']);
            }
        } else {
            $queryBuilder->orderBy('advertisements.id', 'DESC');
            $queryBuilder->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        $queryBuilder->select($selectFields);
        if (isset($paginate) && $paginate == true) {
            return $queryBuilder->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        $queryBuilder->where(function ($query) {
            $query->whereNotNull('advertisements.latitude')
                ->orWhereNotNull('city.latitude');
        });
        return $queryBuilder->get();
    }
    
    public function getFilterAdsHomepage($filters = array(), $paginate = false) {

        $mod = Config::get('constant.AD_GROUP_BY_DISTANCE_MOD');
        $selectFields = [
            'advertisements.id', 
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
             
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address")        
        ];
 
        $queryBuilder = Advertisement::with(["advertisementImages" => function($query) {
        $query->addSelect("id",
                "advertisement_id",
                'image_name',
                DB::raw("image_name AS original_url"),
                DB::raw("image_name AS thumbnail_url")
            )->first(); 
        }])
        ->join('users', 'users.id', '=', 'advertisements.user_id') ;

        $queryBuilder->whereNull('advertisements.deleted_at'); 
        $queryBuilder->orderBy('advertisements.id', 'DESC');  
        $queryBuilder->select($selectFields);
        $queryBuilder->limit(4);
          
        $queryBuilder->where('approved','=',1);
        return $queryBuilder->get();
    }
    /**
     * Filter the product based on your passed detail.
     * 
     * @param:
     *      $filters: Array key value pair of search criteria
     * 
     * @return
     *      Returns count of advertisement.
     */
    public function getFilterDataCountForFrontend($filters = array()) {

        $queryBuilder = Advertisement::join('users', 'users.id', '=', 'advertisements.user_id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state');

        // $queryBuilder->whereNull('advertisements.deleted_at');

        if (isset($filters) && !empty($filters)) {                       
            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $queryBuilder->where('advertisements.user_id', $filters['user_id']);
            }
            
            if (isset($filters['is_closed']) && $filters['is_closed'] !== '') {
                $queryBuilder->where('advertisements.is_closed', $filters['is_closed']);
            }
            
            if (isset($filters['ads_type']) && $filters['ads_type'] != '') {
                $queryBuilder->where('ads_type', $filters['ads_type']);
            }

            if (isset($filters['city']) && $filters['city'] != '') {
                $queryBuilder->where('advertisements.city', $filters['city']);
            }
            
            if (isset($filters['approved']) && $filters['approved'] != '') {
                $queryBuilder->where('approved', $filters['approved']);
            }
                        
            if (isset($filters['category_id']) && $filters['category_id'] != '') {
                $queryBuilder->whereHas('advertisementCategories', function($queryBuilder) use ($filters) {
                    $queryBuilder->where('category_id', '=', $filters['category_id']);
                });
            }
            
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $queryBuilder->where(function ($queryBuilder) use ($filters) {

                    $queryBuilder->orWhere('advertisements.name', 'like', '%' . $filters['searchText'] . '%');

                    $queryBuilder->orWhere(function ($queryBuilder) use ($filters) {
                        $queryBuilder->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });
                });
            }
        }

        return $queryBuilder->count();
    }

    public function deleteAdvertisement($filters) {
        $queryBuilder = Advertisement::where('user_id', '=', $filters["user_id"])
                            ->where('id', '=', $filters["advertisement_id"])
                            ->first();
        if($queryBuilder) {
            return $queryBuilder->delete();
        }
        return false;
    }

    /**
     * Filter the product based on your passed detail.
     * 
     * @param:
     *      $filters: Array key value pair of search criteria
     * 
     * @return
     *      Returns the list of advertisement data.
     */
    public function getFilterInterestedAdsForFrontend($filters = array(), $paginate = false) {

        $selectFields = [
            'advertisements.id',
            'advertisements.ads_type',
            DB::raw("(CASE advertisements.ads_type 
                            WHEN 0 THEN '" . Config::get('constant.ADVERTISEMENT_BUY_TYPE') . "'
                            WHEN 1 THEN '" . Config::get('constant.ADVERTISEMENT_SELL_TYPE') . "'
                            WHEN 2 THEN '" . Config::get('constant.ADVERTISEMENT_SERVICE_TYPE') . "'
                        END) AS ads_type_text"),
            'advertisements.user_id', 
            'advertisements.name', 
            DB::raw("IFNULL(advertisements.descriptions, '') AS descriptions"),
            DB::raw("IFNULL(advertisements.ads_slug, '') AS ads_slug"), 
            DB::raw("IFNULL(advertisements.price, '') AS price"), 
            DB::raw("IFNULL(advertisements.address, '') AS address"), 
            DB::raw("IFNULL(advertisements.street_address, '') AS street_address"),
            DB::raw("IFNULL(advertisements.country, '') AS country"), DB::raw("IFNULL(advertisements.state, '') AS country"), 
            DB::raw("IFNULL(state.name, '') AS state_name"), 
            DB::raw("IFNULL(country.name, '') AS country_name"), 
            DB::raw("IFNULL(advertisements.city, '') AS city"), 
            DB::raw("IFNULL(advertisements.pincode, '') AS pincode"), 
            DB::raw("IFNULL(advertisements.latitude, '') AS latitude"), DB::raw("IFNULL(advertisements.longitude, '') AS longitude"), 
            'advertisements.promoted', 
            'advertisements.interest_count', 'advertisements.visit_count',  
            'advertisements.approved', DB::raw("IFNULL(advertisements.approved_by, '') AS approved_by"), 
            'advertisements.created_at', 'advertisements.updated_at',
            DB::raw('(UNIX_TIMESTAMP(advertisements.created_at) * 1000) AS created_date'), 
            DB::raw('(UNIX_TIMESTAMP(advertisements.updated_at) * 1000) AS updated_date'),
            'users.name AS user_full_name',
            DB::raw("IFNULL(parent_category.category_id, '') AS parent_category_ids"),
            DB::raw("IFNULL(parent_category.category_name, '') AS parent_category_names"),
            DB::raw("IFNULL(child_category.category_id, '') AS child_category_ids"),
            DB::raw("IFNULL(child_category.category_name, '') AS child_category_names"),
            'advertisements.category_hierarchy',
            'advertisements.is_closed'            
        ];

        $queryBuilder = Advertisement::with(["advertisementImages" => function($query) {
                    $query->addSelect("id",
                            "advertisement_id",
                            'image_name',
                            DB::raw("image_name AS original_url"),
                            DB::raw("image_name AS thumbnail_url")
                        );
                    // $query->addSelect("id",
                    //         "advertisement_id",
                    //         'image_name',
                    //         DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". URL(Config::get('constant.ADVERTISEMENT_ORIGINAL_IMAGE_PATH')) . "/', image_name) ELSE '' END) AS original_url"),
                    //         DB::raw("(CASE WHEN image_name <> '' THEN CONCAT('". URL(Config::get('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH')) . "/', image_name) ELSE '' END) AS thumbnail_url")
                    //     );
            }])
            ->join('users', 'users.id', '=', 'advertisements.user_id')
            ->join('user_interest_in_advertisement', 'user_interest_in_advertisement.advertisement_id', '=', 'advertisements.id')
            ->leftJoin('country', 'country.id', '=', 'advertisements.country')
            ->leftJoin('state', 'state.id', '=', 'advertisements.state')
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 0 
                        GROUP BY advertisement_id) AS parent_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'parent_category.advertisement_id');
                        })
            ->leftJoin(DB::raw("(SELECT advertisement_id, GROUP_CONCAT(DISTINCT c.name ORDER BY ac.id ASC SEPARATOR ', ') category_name, GROUP_CONCAT(ac.category_id) category_id
                        FROM `advertisement_categories` ac JOIN `categories` c ON ac.category_id = c.id 
                        WHERE category_type = 1 
                        GROUP BY advertisement_id) AS child_category"),
                        function($join)
                        {
                            $join->on('advertisements.id', '=', 'child_category.advertisement_id');
                        });

        if (isset($filters) && !empty($filters)) {                      
            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $queryBuilder->where('user_interest_in_advertisement.user_id', $filters['user_id']);
            }
            
            if (isset($filters['ads_type']) && $filters['ads_type'] != '') {
                $queryBuilder->where('ads_type', $filters['ads_type']);
            }

            if (isset($filters['city']) && $filters['city'] != '') {
                $queryBuilder->where('city', $filters['city']);
            }
            
            if (isset($filters['approved']) && $filters['approved'] != '') {
                $queryBuilder->where('approved', $filters['approved']);
            }
            
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $queryBuilder->where(function ($queryBuilder) use ($filters) {

                    $queryBuilder->orWhere('advertisements.name', 'like', '%' . $filters['searchText'] . '%');

                    $queryBuilder->orWhere(function ($queryBuilder) use ($filters) {
                        $queryBuilder->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });
                });
            }

            if (isset($filters['sortBy']) && $filters['sortBy'] != '') {
                if ($filters['sortBy'] == 'promoted') {
                    $queryBuilder->orderBy('advertisements.promoted', 'DESC');
                } 
                elseif ($filters['sortBy'] == 'relevance') {
                    $queryBuilder->orderBy('advertisements.id', 'DESC');
                
                /*elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $selectFields[] = DB::raw('IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( advertisements.latitude ) ) * cos( radians( advertisements.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( advertisements.latitude ) ) ) ), "") AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $queryBuilder->having('distance', '<', $filters['radius']);
                    }

                    $queryBuilder->orderBy('advertisements.id', 'DESC');
                    $queryBuilder->orderBy('advertisements.promoted', 'DESC');
                    $queryBuilder->orderBy(\DB::raw('-`distance`'), 'DESC');                   
                */
                } else if ($filters['sortBy'] == 'popular') {
                    $queryBuilder->orderBy('advertisements.visit_count', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $selectFields[] = DB::raw('IFNULL(( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( advertisements.latitude ) ) * cos( radians( advertisements.longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( advertisements.latitude ) ) ) ), "") AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $queryBuilder->having('distance', '<', $filters['radius']);
                    }
                    $queryBuilder->orderBy(\DB::raw('-`distance`'), 'DESC');
                } else {
                    $queryBuilder->orderBy('advertisements.id', 'DESC');
                }
                
            } else {
                $queryBuilder->orderBy('advertisements.id', 'DESC');
            }
            if (isset($filters['skip']) && $filters['skip'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $queryBuilder->skip($filters['skip'])->take($filters['take']);
            }
        } else {
            $queryBuilder->orderBy('advertisements.id', 'DESC');
            $queryBuilder->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        $queryBuilder->select($selectFields);

        if (isset($paginate) && $paginate == true) {
            return $queryBuilder->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        return $queryBuilder->get();
    }

    /**
     * Filter the product based on your passed detail.
     * 
     * @param:
     *      $filters: Array key value pair of search criteria
     * 
     * @return
     *      Returns count of advertisement.
     */
    public function getFilterInterestedAdsCountForFrontend($filters = array()) {

        $queryBuilder = Advertisement::join('users', 'users.id', '=', 'advertisements.user_id')
                ->join('user_interest_in_advertisement', 'user_interest_in_advertisement.advertisement_id', '=', 'advertisements.id')
                ->leftJoin('country', 'country.id', '=', 'advertisements.country')
                ->leftJoin('state', 'state.id', '=', 'advertisements.state');

        // $queryBuilder->whereNull('advertisements.deleted_at');

        if (isset($filters) && !empty($filters)) {                       
            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $queryBuilder->where('user_interest_in_advertisement.user_id', $filters['user_id']);
            }
            
            if (isset($filters['ads_type']) && $filters['ads_type'] != '') {
                $queryBuilder->where('ads_type', $filters['ads_type']);
            }

            if (isset($filters['city']) && $filters['city'] != '') {
                $queryBuilder->where('city', $filters['city']);
            }
            
            if (isset($filters['approved']) && $filters['approved'] != '') {
                $queryBuilder->where('approved', $filters['approved']);
            }
            
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $queryBuilder->where(function ($queryBuilder) use ($filters) {

                    $queryBuilder->orWhere('advertisements.name', 'like', '%' . $filters['searchText'] . '%');

                    $queryBuilder->orWhere(function ($queryBuilder) use ($filters) {
                        $queryBuilder->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });
                });
            }            
        }

        return $queryBuilder->count();
    }

    
    public function restoreAdvertisement($filters) {
        return Advertisement::where('user_id', '=', $filters["user_id"])
                            ->where('id', '=', $filters["advertisement_id"])
                            ->restore();
    }
    
    /**
     * Retrive the thread ID for the selected user and ads
     */
    public function getThreadIdByAdsAndUser($advertisementId, $userId) {
        return Chats::where('member_id', '=', $userId)
                            ->where('advertisement_id', '=', $advertisementId)
                            ->first();
    }    
    
    /**
     * Retrive the list of interest showed on Ads.
     */
    public function getUserInterestList($advertisementId, $take, $skip) {
        return $this->userInterestInAdvertisement()
                    ->join('chats', function($join) { 
                        $join->on('chats.customer_id', '=', 'user_interest_in_advertisement.user_id'); 
                        $join->on('chats.advertisement_id', '=', 'user_interest_in_advertisement.advertisement_id'); 
                    })
                    ->where('chats.id', '>', 0)
                    ->orderBy('user_interest_in_advertisement.id', 'DESC')
                    ->select("user_interest_in_advertisement.*", 'chats.id AS thread_id')
                    ->skip($skip)
                    ->paginate($take);
    }    
    
    /**
     * get count of interest showed on Ads.
     */
    public function getUserInterestConut($advertisementId = 0) {
        return $this->userInterestInAdvertisement()
                    ->join('chats', function($join) { 
                        $join->on('chats.customer_id', '=', 'user_interest_in_advertisement.user_id'); 
                        $join->on('chats.advertisement_id', '=', 'user_interest_in_advertisement.advertisement_id'); 
                    })
                    ->count();
    }   
        
    /**
     * Close the advertisment on user request. Only user and Admin can set stutas to close.
     */
    public function closeAdvertisement($filters) {
        $queryBuilder = Advertisement::where('user_id', '=', $filters["user_id"])
                            ->where('id', '=', $filters["advertisement_id"])
                            ->first();
        if($queryBuilder) {
            $queryBuilder->is_closed = 1;
            $queryBuilder->save();
            return true;
            // return $queryBuilder->delete();
        }
        return false;
    }

    public function updateViewCount($id) {
        $adsSaveData = Advertisement::find($id);
        if($adsSaveData) {
            $adsSaveData->visit_count = $adsSaveData->visit_count + 1;
            $adsSaveData->save();
        }
    }
}
