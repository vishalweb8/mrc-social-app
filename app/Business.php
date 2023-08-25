<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;
use Storage;
use App\Category;
use Cviebrock\EloquentSluggable\Sluggable;

class Business extends Model
{
    use SoftDeletes, CascadeSoftDeletes, Sluggable;

    protected $table = 'business';

    protected $fillable = ['user_id', 'asset_type_id', 'created_by', 'name', 'business_slug', 'description', 'short_description', 'category_id', 'parent_category', 'category_hierarchy', 'business_logo', 'phone', 'country_code', 'mobile', 'latitude', 'longitude', 'metatags', 'promoted', 'membership_type', 'address', 'street_address', 'locality', 'country', 'state', 'city', 'taluka', 'district', 'pincode', 'establishment_year', 'email_id', 'website_url', 'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url','online_store_url', 'approved','document_approval', 'suggested_categories', 'agent_user','document_approval','url_slug','web_site_color_theme','seo_meta_tags','seo_meta_description','is_normal_view','location_id'];

    protected $dates = ['deleted_at'];


    protected $cascadeDeletes = ['products', 'services', 'businessImages', 'businessWorkingHours', 'businessActivities', 'getBusinessRatings', 'owners', 'business_address', 'getChats'];

    /**
     * Insert and Update Business\
     */

    protected $appends = ['categories','business_logo_url'];
    // protected $appends = ['membership_types'];

	/**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'verified' => 'boolean',
    ];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'business_slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getCategoriesAttribute()
    {
        if(isset($this->attributes['category_id']) && $this->attributes['category_id'] != '') {
            $arrCategoryIds = explode(",", $this->attributes['category_id']);
            $categories = Category::whereIn('id', $arrCategoryIds)->pluck('name');
            if (count($categories)) {
                return implode(",", $categories->toArray());
            } else {
                return '';
            }
        }
        return '';
    }

    public function getBusinessLogoUrlAttribute()
    {
        if(!empty($this->business_logo)) {
            $s3url = config('constant.s3url');
            $url = $s3url.config('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$this->business_logo; 
            if(Storage::disk(config('constant.DISK'))->exists($url)) {
                $url = $s3url.config('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$this->business_logo; 
            }
            else{
                $url = url(config('constant.DEFAULT_IMAGE'));
            }
        } else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
        return $url;
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
           
            return Business::where('id', $data['id'])->update($updateData);
        } else {
            $data['created_by'] = Auth::id();
            return Business::create($data);
        }
    }

    /**
     * get all Business for admin
     * @param: $filters array of search parameters
     * @param: $paginate boolean.
     * @param: $isFromAdminPanel boolean.
     */
    public function getAll($filters = array(), $paginate = false, $isFromAdminPanel = false)
    {
        $business = Business::whereNull('deleted_at');

        if(Auth::check() && !Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $business->whereRaw(Auth::user()->sql_query);
        }

        if (isset($filters) && !empty($filters)) {
            if (isset($filters['asset_type_id']) && !empty($filters['asset_type_id'])) {
                $business->where('asset_type_id', $filters['asset_type_id']);
            }
            if (isset($filters['agent_user']) && $filters['agent_user'] != '') {
                $business->where('agent_user', $filters['agent_user']);
            }
            if (isset($filters['offset']) && $filters['offset'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['offset'])->take($filters['take']);
            }
            if (isset($filters['city']) && $filters['city'] != '') {
                $business->where('city', $filters['city']);
            }
            if (isset($filters['autoCompleteText']) && $filters['autoCompleteText'] != '') {
                // $business->where('autoCompleteText', $filters['autoCompleteText']);
                $business->where('name', 'like',  $filters['autoCompleteText'] . '%');
                // return $filters['autoCompleteText'];
                $business->skip(0)->take(3);  
            }
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                //$business->where('approved', 1);

                $business->where(function ($business) use ($filters) {

                    $business->orWhere('name', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

                    $business->orWhere('mobile', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhere(function ($business) use ($filters) {
                        $business->whereHas('user', function ($query) use ($filters) {
                            $query->where('name', 'like', '%' . $filters['searchText'] . '%');
                        });
                    });

                    $business->orWhere(function ($business) use ($filters) {
                        $business->whereExists(function ($query) use ($filters) {
                            $query->from('categories')
                                ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                                ->where('categories.name', 'like', '%' . $filters['searchText'] . '%');
                        });
                    });

                    $business->orWhere(function ($business) use ($filters) {
                        $business->whereHas('businessParentCategory', function ($query) use ($filters) {
                            $query->where('name', 'like', '%' . $filters['searchText'] . '%');
                        });
                    });

                    $business->orWhere(function ($business) use ($filters) {
                        $business->whereHas('owners', function ($query) use ($filters) {
                            $query->where('full_name', 'like', '%' . $filters['searchText'] . '%');
                        });
                    });
                });
            }
            if (isset($filters['approved'])) {
                $business->where('approved', $filters['approved']);
            }
            if (isset($filters['created_by'])) {
                $business->where('created_by', $filters['created_by'])->where('user_id', '!=', $filters['created_by']);
            }
            if (isset($filters['promoted'])) {
                $business->where('promoted', $filters['promoted']);
            }
            if (isset($filters['membership_type'])) {
                if (isset($filters['membership_premium_lifetime_type'])) {
                    $business->where('membership_type', '<>', 0);
                } else {
                    $business->where('membership_type', $filters['membership_type']);
                }
            }

            if (isset($filters['user_id'])) {
                $business->where('user_id', $filters['user_id']);
            }
            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if ($filters['sortBy'] == 'popular') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('visits', 'DESC');
                } elseif ($filters['sortBy'] == 'ratings') {
                    $business->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    $business->orderBy('membership_type', 'DESC')->orderBy('average_rating', 'DESC');
                } elseif ($filters['sortBy'] == 'AtoZ') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'ASC');
                } elseif ($filters['sortBy'] == 'ZtoA') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'DESC');
                } elseif ($filters['sortBy'] == 'promoted') {
                    $business->orderBy('id', 'DESC');
                    $business->orderBy('promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'membership_type') {
                    $business->orderBy('membership_type', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }

                   //$business->orderBy('distance', 'ASC');
                    $business->orderBy(\DB::raw('-`distance`'), 'DESC');
                } elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }

                    $business->orderBy('membership_type', 'DESC')->orderBy(\DB::raw('-`distance`'), 'DESC');
                } else {
                    $business->orderBy('id', 'DESC');
                }
            } else {
                $business->orderBy('id', 'DESC');
            }
            if (isset($filters['skip']) && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['skip'])->take($filters['take']);
            }
        } else {
            $business->orderBy('id', 'DESC');
            
            if (isset($filters['page']) && $filters['page'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['page'])->take($filters['take']);
            } else {
                $business->skip(1)->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            }
        }
        $business->has("entityType")->with('entityType');

        if ($isFromAdminPanel == true) {
            if (isset($paginate) && $paginate == true && isset($filters['take']) && $filters['take'] > 0) {
                return $business->paginate($filters['take']);
            } elseif (isset($paginate) && $paginate == true) {
                return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $business->get();
            }
        } else {
            if (isset($paginate) && $paginate == true) {
                return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $business->get();
            }
        }
    }

    public function businessFilter($postData, $pagination = false, $search = false)
    {
        $selectColumnsAdminQuery = [
            'business.id',
            'business.name',
            'business.membership_type',
            DB::Raw("IFNULL(`business`.`latitude`, 0) AS latitude"),
            DB::Raw("IFNULL(`business`.`longitude`, 0) AS longitude"),
            DB::Raw("IFNULL(`business`.`address`, '') AS address"),
            DB::Raw("IFNULL(`business`.`document_approval`, '') AS document_approval"),
            DB::Raw("IFNULL(`business`.`street_address`, '') AS street_address"),
            DB::Raw("IFNULL(`business`.`country`, '') AS country"),
            DB::Raw("IFNULL(`business`.`state`, '') AS state"),
            DB::Raw("IFNULL(`business`.`city`, '') AS city"),
            DB::Raw("IFNULL(`business`.`locality`, '') AS locality"),
            DB::Raw("IFNULL(`business`.`taluka`, '') AS taluka"),
            DB::Raw("IFNULL(`business`.`district`, '') AS district"),
            DB::Raw("IFNULL(`business`.`pincode`, '') AS pincode"),
            DB::Raw("IFNULL(`business`.`country_code`, '') AS country_code"),
            DB::Raw("IFNULL(`business`.`mobile`, '') AS mobile"),
            DB::Raw("IFNULL(`business`.`website_url`, '') AS website_url"),
            DB::Raw("CASE `business`.`membership_type` 
                        WHEN 2 THEN '" . url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE')) . "'
                        WHEN 1 THEN '" . url(Config::get('constant.PREMIUM_ICON_IMAGE')) . "'
                        ELSE '" . url(Config::get('constant.BASIC_ICON_IMAGE')) . "'
                    END AS membership_type_icon"),
            'business.category_id',
            'business.business_logo',
            'business.parent_category',
            DB::Raw("IFNULL(`business`.`business_slug`, '') AS business_slug"),
            'business.approved',
            
            'business.user_id',
            'users.name AS user_full_name',
             
        ];

        if ($search == true) {
            // $business = Business::has('user');
            $business = Business::join("users", "users.id", "=", "business.user_id");
        } else {
            $business = Business::join("users", "users.id", "=", "business.user_id")
                        ->where('agent_user', Auth::id())->orderBy('id', 'DESC');
        }

        $business->select($selectColumnsAdminQuery);

        if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $business->whereRaw(Auth::user()->sql_query);
        }

        if (isset($postData['fieldtype']) && $postData['fieldtype'] != '' &&
            isset($postData['isNull']) && $postData['isNull'] != '') {

                switch ($postData['fieldtype']) {
                    case ('owners'):  
                        if ($postData['isNull'] == 0) {
                            $business->doesntHave('owners');
                        } else{
                            $business->has('owners');
                        }
                        break; 
                    case ('address'):  
                        if ($postData['isNull'] == 0) {
                            $business->doesntHave('business_address');
                        } else{
                            $business->has('business_address');
                        }
                        break; 
                    default:
                        if ($postData['isNull'] == 0) {
                            $business->whereNull($postData['fieldtype']);
                        } else {
                            $business->whereNotNull($postData['fieldtype']);
                        } 
                        break;            
                }       
        }
        
        if( (isset($postData['searchText']) && $postData['searchText'] != '') && 
            (isset($postData['fieldtype']) && $postData['fieldtype'] != '')) {
            switch ($postData['fieldtype']) {
                case ('users'):                    
                    $business->where(function ($business) use ($postData) {
                        $business->where('users.name', 'like', '%' . $postData['searchText'] . '%');
                    });
                    break;            
                case ('owners'):
                    $business->where(function ($business) use ($postData) {
                        $business->whereHas('owners', function ($query) use ($postData) {
                            $query->where('full_name', 'like', '%' . $postData['searchText'] . '%');
                        });
                    });
                    break;            
                case ('category_id'):
                    $business->where(function ($business) use ($postData) {
                        $business->whereExists(function ($query) use ($postData) {
                            $query->from('categories')
                                ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                                ->where('categories.name', 'like', '%' . $postData['searchText'] . '%');
                        });
                    });
                    break;
            
                default:
                    $business->where($postData['fieldtype'], 'like', '%' . $postData['searchText'] . '%');
                    break;            
            }            

        } else if( (!isset($postData['searchText']) && $postData['searchText'] == '') && 
                (isset($postData['fieldtype']) && $postData['fieldtype'] != '') && 
                (isset($postData['isNull']) && $postData['isNull'] != '') ) {
            
            if ($postData['isNull'] == 0) {
                $business->whereNull($postData['fieldtype']);
            } else {
                $business->whereNotNull($postData['fieldtype']);
            }

        } else if(( isset($postData['searchText']) && $postData['searchText'] != '') &&
             (isset($postData['fieldtype']) || $postData['fieldtype'] == '') ) {
            
            $business->where(function ($business) use ($postData) {

                $business->orWhere('business.name', 'like', '%' . $postData['searchText'] . '%');

               // $business->orWhereRaw("FIND_IN_SET('" . $postData['searchText'] . "', metatags)");

                $business->orWhere('mobile', 'like', '%' . $postData['searchText'] . '%');

                $business->orWhere(function ($business) use ($postData) {
                    $business->where('users.name', 'like', '%' . $postData['searchText'] . '%');
                });

                // $business->orWhere(function ($business) use ($postData) {
                //     $business->whereExists(function ($query) use ($postData) {
                //         $query->from('categories')
                //             ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                //             ->where('categories.name', 'like', '%' . $postData['searchText'] . '%');
                //     });
                // });

                $business->orWhere(function ($business) use ($postData) {
                    $business->whereHas('businessParentCategory', function ($query) use ($postData) {
                        $query->where('name', 'like', '%' . $postData['searchText'] . '%');
                    });
                });

                // $business->orWhere(function ($business) use ($postData) {
                //     $business->whereHas('owners', function ($query) use ($postData) {
                //         $query->where('full_name', 'like', '%' . $postData['searchText'] . '%');
                //     });
                // });
            });
        }
        /**
         * @date: 25th 
         * Following code is commentted because we have implemented new combination (smart search)
         * 
         */
        // if (isset($postData['fieldtype']) && $postData['fieldtype'] != '' && isset($postData['isNull']) && $postData['isNull'] != '' && (!isset($postData['searchText']) && $postData['searchText'] == '')) {
        //     if ($postData['isNull'] == 0) {
        //         $business->whereNull($postData['fieldtype']);
        //     } else {
        //         $business->whereNotNull($postData['fieldtype']);
        //     }
        // }
        // if ( ( isset($postData['fieldtype']) && $postData['fieldtype'] != '' ) &&
        //     isset($postData['searchText']) && $postData['searchText'] != '' ) {
        //     $business->where($postData['fieldtype'], 'like', '%' . $postData['searchText'] . '%');
        // }
        
        // if ( ( !isset($postData['fieldtype']) && $postData['fieldtype'] == '' ) && 
        //      ( !isset($postData['isNull']) && $postData['isNull'] == '' ) &&
        //     isset($postData['searchText']) && $postData['searchText'] != '' ) {

        //     $business->where(function ($business) use ($postData) {

        //         $business->orWhere('business.name', 'like', '%' . $postData['searchText'] . '%');

        //         $business->orWhereRaw("FIND_IN_SET('" . $postData['searchText'] . "', metatags)");

        //         $business->orWhere('mobile', 'like', '%' . $postData['searchText'] . '%');

        //         $business->orWhere(function ($business) use ($postData) {
        //             $business->where('users.name', 'like', '%' . $postData['searchText'] . '%');
        //         });

        //         $business->orWhere(function ($business) use ($postData) {
        //             $business->whereExists(function ($query) use ($postData) {
        //                 $query->from('categories')
        //                     ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
        //                     ->where('categories.name', 'like', '%' . $postData['searchText'] . '%');
        //             });
        //         });

        //         $business->orWhere(function ($business) use ($postData) {
        //             $business->whereHas('businessParentCategory', function ($query) use ($postData) {
        //                 $query->where('name', 'like', '%' . $postData['searchText'] . '%');
        //             });
        //         });

        //         $business->orWhere(function ($business) use ($postData) {
        //             $business->whereHas('owners', function ($query) use ($postData) {
        //                 $query->where('full_name', 'like', '%' . $postData['searchText'] . '%');
        //             });
        //         });
        //     });
        // }

        if (isset($postData['type']) && $postData['type'] != '') {
            if ($postData['type'] == 'created_by') {
                $business->where('business.created_by', Auth::id());
            }

            if ($postData['type'] == 'assign_to') {
                $business->where('business.created_by', '<>', Auth::id());
            }
        }

        if(isset($postData['approved']) && $postData['approved'] != '') {
            $business->where('business.approved', '=', $postData['approved']);
        }

        if(isset($postData['country_code']) && $postData['country_code'] != '') {
            $business->where('business.country_code', '=', $postData['country_code']);
        }

        if (isset($postData['page']) && $postData['page'] >= 0 && isset($postData['take']) && $postData['take'] > 0) {
            $business->skip($postData['page'])->take($postData['take']);
        } else {
            $business->skip(1)->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }

        if ($pagination == true) {
            return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $business->get();
        }


    }

    public function businessMembershipPlans()
    {
        return $this->hasMany('App\Membership')->orderBy('id', 'desc')->where('status',1);
    }

    public function businessCreatedBy()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    public function businessParentCategory()
    {
        return $this->belongsTo('App\Category', 'parent_category');
    }

    public function business_category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function business_category_hierarchy()
    {
        return $this->belongsTo('App\Category', 'category_hierarchy');
    }

    public function products()
    {
        return $this->hasMany('App\Product');
    }

    public function services()
    {
        return $this->hasMany('App\Service');
    }

    public function service()
    {
        return $this->hasOne('App\Service');
    }

    public function BusinessDoc()
    {
        return $this->hasMany('App\BusinessDoc');
    }
    public function businessImages()
    {
        return $this->hasMany('App\BusinessImage');
    }    

    public function getBusinessRatings()
    {
        return $this->hasMany('App\BusinessRatings', 'business_id');
    }

    public function businessImagesById()
    {
        return $this->hasOne('App\BusinessImage', 'business_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function entityType()
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function userVisitActivity()
    {
        return $this->hasMany(UserVisitActivity::class, 'entity_id');
    }

    public function businessWorkingHours()
    {
        return $this->hasOne('App\BusinessWorkingHours');
    }

    public function businessActivities()
    {
        return $this->hasMany('App\BusinessActivities', 'business_id');
    }

    public function otherLangDescriptions()
    {
        return $this->hasMany(EntityDescriptionLanguage::class, 'entity_id');
    }

    public function videos()
    {
        return $this->hasMany(EntityVideo::class, 'entity_id');
    }

    public function knowMores()
    {
        return $this->hasMany(EntityKnowMore::class, 'entity_id')->where('language','english');
    }
    
    public function customDetails()
    {
        return $this->hasMany(EntityCustomField::class, 'entity_id')->where('language','english');
    }

    public function nearByFilter()
    {
        return $this->hasMany(EntityNearbyFilter::class, 'entity_id');
    }

    public function reports()
    {
        return $this->hasMany(EntityReport::class, 'entity_id');
    }

    public function owners()
    {
        return $this->hasMany('App\Owners', 'business_id');
    }

    public function business_address()
    {
        return $this->hasOne('App\BusinessAddressAttributes', 'business_id');
    }

    public function getChats()
    {
        return $this->hasMany('App\Chats', 'business_id');
    }

    public function getBusinessListingByCategoryId($cId, $filters = array(), $cIds = array())
    {
        // info('category id:- '.$cId);
        // info('filters:- ',$filters);
        // info('sub category:- ',$cIds);
        $whereStr = "FIND_IN_SET(" . $cId . ", parent_category)";
        $whereArr = [];
       // $whereArr[] = "FIND_IN_SET(" . $cId . ", parent_category)";

        if (!empty($cIds)) {
            foreach ($cIds as $id) {
                $whereArr[] = "FIND_IN_SET(" . $id . ", category_id)";
            }
        }

        if (!empty($whereArr)) {
            $whereStr .= ' AND '. implode(' OR ', $whereArr);
        }

        $getData = Business::select('business.*',DB::Raw("(select name from asset_types where id = business.asset_type_id) as entity_type"))->whereRaw($whereStr);

        if (isset($filters) && !empty($filters)) {
            if (isset($filters['offset']) && $filters['offset'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $getData->skip($filters['offset'])->take($filters['take']);
            }
            if (isset($filters['approved'])) {
                $getData->where('approved', $filters['approved']);
            }
            if (isset($filters['searchText']) && !empty($filters['searchText'])) {
                info('filter by name');
                $getData->where('name','like',$filters['searchText'].'%');
            }
            if (isset($filters['city']) && !empty($filters['city'])) {
                $getData->where('city',$filters['city']);
            }
            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if ($filters['sortBy'] == 'popular') {
                    $getData->orderBy('membership_type', 'DESC')->orderBy('visits', 'DESC');
                } elseif ($filters['sortBy'] == 'ratings') {
                    $getData->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    $getData->orderBy('membership_type', 'DESC')->orderBy('average_rating', 'DESC');
                } elseif ($filters['sortBy'] == 'promoted') {
                    $getData->orderBy('promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'membership_type') {
                    $getData->orderBy('membership_type', 'DESC');
                } elseif ($filters['sortBy'] == 'AtoZ') {
                    $getData->orderBy('membership_type', 'DESC')->orderBy('name', 'ASC');
                } elseif ($filters['sortBy'] == 'ZtoA') {
                    $getData->orderBy('membership_type', 'DESC')->orderBy('name', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $getData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $getData->having('distance', '<', $filters['radius']);
                    }

                   //$getData->orderBy('distance', 'ASC');
                    $getData->orderBy(\DB::raw('-`distance`'), 'DESC');
                } elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $getData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $getData->having('distance', '<', $filters['radius']);
                    }

                   //$getData->orderBy('membership_type', 'DESC')->orderBy('distance', 'ASC');
                    $getData->orderBy('membership_type', 'DESC')->orderBy(\DB::raw('-`distance`'), 'DESC');
                } else {
                    $getData->orderBy('id', 'DESC');
                }
            } else {
                $getData->orderBy('id', 'DESC');
            }
        } else {
            $getData->orderBy('id', 'DESC');
        }
        return $getData->with('user:id,name','businessImagesById','owners')->get();
    }

    public function getBusinessesByRating($filters = array())
    {
        $skip = (isset($filters['skip']) && !empty($filters['skip'])) ? $filters['skip'] : 0;
        $take = (isset($filters['take']) && !empty($filters['take'])) ? $filters['take'] : Config::get('constant.WEBSITE_RECORD_PER_PAGE');
        $getData = Business::where('approved', 1);
        if (isset($filters) && !empty($filters)) {
            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if (isset($filters['promoted'])) {
                    $getData->where('promoted', $filters['promoted']);
                }
                if (isset($filters['skip']) && isset($filters['take']) && !empty($filters['take'])) {
                    $getData->skip($skip)->take($take);
                }
                if ($filters['sortBy'] == 'ratings') {
                    $getData->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    $getData->orderBy('membership_type', 'DESC')->orderBy('average_rating', 'DESC');
                } else {
                    $getData->orderBy('id', 'DESC');
                }
            } else {
                $getData->orderBy('id', 'DESC');
            }
        } else {
            $getData->orderBy('id', 'DESC');
        }
        return $getData->get();
    }

    public function getBusinessesByNearMe($filters = array())
    {
        $getData = Business::whereNull('deleted_at')->limit(200);
        if (isset($filters) && !empty($filters)) {
            if (isset($filters['approved'])) {
                $getData->where('approved', $filters['approved']);
            }
            if (isset($filters['promoted'])) {
                $getData->where('promoted', $filters['promoted']);
            }
            if (isset($filters['skip']) && isset($filters['take']) && !empty($filters['take'])) {
                $getData->skip($filters['skip'])->take($filters['take']);
            }
            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if ($filters['sortBy'] == 'nearMe' && isset($filters['radius']) && !empty($filters['radius']) && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    // 6371, 111.045, 3959
                    $getData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                    $getData->having('distance', '<', $filters['radius']);

                   //$getData->orderBy('distance', 'ASC');
                    $getData->orderBy(\DB::raw('-`distance`'), 'DESC');
                }
                else
                {
                    // 6371, 111.045, 3959
                    $getData->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                   //$getData->orderBy('distance', 'ASC');
                    $getData->having('distance', '>', 0);
                    $getData->orderBy(\DB::raw('`distance`'), 'ASC');
                    }
            }
             else {
                $getData->orderBy('id', 'DESC');
            }
        } else {
            $getData->orderBy('id', 'DESC');
        }
        if (isset($paginate) && $paginate == true) {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $getData->get();
        }
    }

    public static function userVendorData($uId)
    {
        $response = Business::where('user_id', $uId)->get();
        return $response;
    }


    /**
     * Get Business listing by filters
     * @param: $filters - Array for user
     */
    public function getAllForFrontAndMobileApp($filters = array(), $paginate = false)
    {
        $selectColumns = [
            'business.id',
            'business.name',
            'business.membership_type',
            'business.metatags',
            'business.is_normal_view',
            'business.user_id',
            DB::Raw("IFNULL(`business`.`latitude`, 0) AS latitude"),
            DB::Raw("IFNULL(`business`.`longitude`, 0) AS longitude"),
            DB::Raw("IFNULL(`business`.`address`, '') AS address"),
            DB::Raw("IFNULL(`business`.`document_approval`, '') AS document_approval"),
            DB::Raw("IFNULL(`business`.`street_address`, '') AS street_address"),
            DB::Raw("IFNULL(`business`.`country`, '') AS country"),
            DB::Raw("IFNULL(`business`.`state`, '') AS state"),
            DB::Raw("IFNULL(`business`.`city`, '') AS city"),
            DB::Raw("IFNULL(`business`.`locality`, '') AS locality"),
            DB::Raw("IFNULL(`business`.`taluka`, '') AS taluka"),
            DB::Raw("IFNULL(`business`.`district`, '') AS district"),
            DB::Raw("IFNULL(`business`.`pincode`, '') AS pincode"),
            DB::Raw("IFNULL(`business`.`website_url`, '') AS website_url"),
            DB::Raw("IFNULL(`business`.`description`, '') AS description"),
            DB::Raw("IFNULL(`business`.`phone`, '') AS phone"),
            DB::Raw("IFNULL(`business`.`country_code`, '') AS country_code"),
            DB::Raw("IFNULL(`business`.`mobile`, '') AS mobile"),
            DB::Raw("IFNULL(`business`.`email_id`, '') AS email_id"),
            DB::Raw("CASE `business`.`membership_type`
                WHEN 2 THEN '" . url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE')) . "'
                WHEN 1 THEN '" . url(Config::get('constant.PREMIUM_ICON_IMAGE')) . "'
                ELSE '" . url(Config::get('constant.BASIC_ICON_IMAGE')) . "'
                END AS membership_type_icon"),
            'business.category_id',
            'business.business_logo',
            'business.parent_category',

            DB::Raw("IFNULL(`business`.`business_slug`, '') AS business_slug"),
            DB::Raw("(select name from asset_types where id = business.asset_type_id) as entity_type"),
        ];
        $business = Business::whereNull('business.deleted_at')->where('approved', 1);

        if (isset($filters) && !empty($filters)) {
            
            if (isset($filters['asset_type_id'])) {
                $business->where('asset_type_id', $filters['asset_type_id']);
            }

            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                
                $business->where(function ($business) use ($filters) {

                    $business->orWhere('name', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

                    $business->orWhere('mobile', 'like', $filters['searchText'] . '%');

                    $business->orWhereIn('user_id', User::select('id')->where('name', 'like', '%' . $filters['searchText'] . '%'));

                    $business->orWhereRaw("CONCAT(',', (select GROUP_CONCAT(id) from categories where name like '%" . $filters['searchText'] . "%' and `deleted_at` is null),',') REGEXP CONCAT(',', REPLACE(`category_id`, ',', ',|,'),',|,',REPLACE(`parent_category`, ',', ',|,'),',')");
                    $business->orWhereIn('user_id', Owners::select('id')->where('full_name', 'like', '%' . $filters['searchText'] . '%'));
                });
            }
            if (isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                
                $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                if (isset($filters['radius']) && !empty($filters['radius'])) {
                    $business->having('distance', '<', $filters['radius']);
                }
                $business->orderBy(\DB::raw('-`distance`'), 'DESC'); 
            }


            if (isset($filters['city']) && $filters['city'] != '') {
                //$business->where('city', $filters['city']);
                $business->where(function ($busi) use ($filters) {
                    $busi->orWhere('city', $filters['city']);
                    $busi->orWhere('district', $filters['city']);
                    $busi->orWhere('state', $filters['city']);
                    $busi->orWhere('country', $filters['city']);
                });
            }

            // if(isset($filters['recent'])) {


            // $business2 = \DB::table('business')->where('approved', 1)->orderBy('id','DESC')->first();
            // $latestId = $business2->id;
            // $business->where('id','>',$latestId-200);

            // }

            if (isset($filters['created_by'])) {
                $business->where('created_by', $filters['created_by'])->where('user_id', '!=', $filters['created_by']);
            }
            if (isset($filters['promoted'])) {
                $business->where('promoted', $filters['promoted']);
            }
            if (isset($filters['membership_type'])) {
                if (isset($filters['membership_premium_lifetime_type'])) {
                    $business->where('membership_type', '<>', 0);
                } else {
                    $business->where('membership_type', $filters['membership_type']);
                }
            }

            if (isset($filters['user_id'])) {
                $business->where('user_id', $filters['user_id']);
            }

            if (isset($filters['skip']) && isset($filters['take']) && !empty($filters['take'])) {
                $business->skip($filters['skip'])->take($filters['take']);
            } elseif (isset($filters['offset']) && isset($filters['take']) && !empty($filters['take'])) {
                $business->skip($filters['offset'])->take($filters['take']);
            }

            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {

                if ($filters['sortBy'] == 'id') {

                    $business->orderBy('id', 'DESC');
                } elseif ($filters['sortBy'] == 'popular') {

                    $business->orderBy('visits', 'DESC');
                } elseif ($filters['sortBy'] == 'ratings') {
                    //$business->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    $selectColumns[] = \DB::raw('(SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    //$business->leftJoin('business_ratings', 'business_ratings.business_id', '=', 'business.id');
                    //$business->groupBy('business_ratings.business_id')
                    $business->orderBy('average_rating', 'DESC');
                } elseif ($filters['sortBy'] == 'AtoZ') {
                    $business->orderBy('name', 'ASC');
                } elseif ($filters['sortBy'] == 'ZtoA') {
                    $business->orderBy('name', 'DESC');
                } elseif ($filters['sortBy'] == 'promoted') {
                    $business->orderBy('promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'membership_type') {
                    $business->orderBy('membership_type', 'DESC'); 
                } 
                elseif (isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }
                    $business->orderBy(\DB::raw('-`distance`'), 'DESC');
                }
                elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {

                    $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }
                    $business->orderBy(\DB::raw('-`distance`'), 'DESC');
                } elseif ($filters['sortBy'] == 'relevance') {
                    if (isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                        $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                        if (isset($filters['radius']) && !empty($filters['radius'])) {
                            $business->having('distance', '<', $filters['radius']);
                        }
                        $business->orderBy('membership_type', 'DESC')->orderBy(\DB::raw('-`distance`'), 'DESC');
                    } else {
                        $business->orderBy('membership_type', 'DESC')->orderBy('id', 'DESC');
                    }
                } else {
                    $business->orderBy('membership_type', 'DESC')->orderBy('id', 'DESC');
                }
            } else {
                $business->orderBy('membership_type', 'DESC')->orderBy('id', 'DESC');
            }
        } else {
            $business->orderBy('membership_type', 'DESC')->orderBy('id', 'DESC');
        }
        
        $business->with(['owners:business_id,full_name', 'user:id,name', 'businessImages:business_id,image_name']); 
        $business->select($selectColumns);

        if (isset($paginate) && $paginate == true) {
            
            return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            // return $filters;
            if (!empty($filters['limit'])) {
                
                $business->limit($filters['limit']);
            }             
            return $business->get();
        }
    }
    public function getAllTags(){
        $meta = Business::whereNull('deleted_at')
        ->select('metatags')
        ->where('approved', 1)
        ->where('metatags', '!=', '')
        ->get(); 
        $allTags = [];
        // foreach ($meta as $key => $value) {
        //     $listArray = [];
        //     $tagsArray =  explode(',', $value->metatags);
        //     $listArray['tag'] = ($tagsArray);
        //     $tagArray[] = $listArray;
        // }

        foreach($meta as $tags){
           $tagsArray =  explode(',', $tags->metatags); 
            $allTags = array_merge($allTags, $tagsArray);
        }
        // $allTags = array_unique($allTags);
        return ($allTags);
    }
    public function getAllCountForFrontAndMobileApp($filters = array(), $paginate = false)
    {
        $business = Business::whereNull('deleted_at')
            ->where('approved', 1);

        if (isset($filters) && !empty($filters)) {
            if (isset($filters['asset_type_id'])) {
                $business->where('asset_type_id', $filters['asset_type_id']);
            }
            if (isset($filters['agent_user']) && $filters['agent_user'] != '') {
                $business->where('agent_user', $filters['agent_user']);
            }
            /*
            if (isset($filters['skip']) && $filters['skip'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['skip'])->take($filters['take']);
            } elseif (isset($filters['offset']) && $filters['offset'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['offset'])->take($filters['take']);
            }
            */
            if (isset($filters['city']) && $filters['city'] != '') {
                //$business->where('city', $filters['city']);
                $business->where(function ($busi) use ($filters) {
                    $busi->orWhere('city', $filters['city']);
                    $busi->orWhere('district', $filters['city']);
                    $busi->orWhere('state', $filters['city']);
                    $busi->orWhere('country', $filters['city']);
                });
            }

        //     if(isset($filters['recent'])) {

                  
        //             $business2 = \DB::table('business')->where('approved', 1)->orderBy('id','DESC')->first();
        //             $latestId = $business2->id;
        //             $business->where('id','>',$latestId-200);
                    
        //    }

           if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $business->where(function ($business) use ($filters) {

                    $business->orWhere('name', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

                    $business->orWhere('mobile', 'like', $filters['searchText'] . '%');
                        
                    $business->orWhereIn('user_id', User::select('id')->where('name', 'like', '%' . $filters['searchText'] . '%'));

                    $business->orWhereRaw("CONCAT(',', (select GROUP_CONCAT(id) from categories where name like '%" . $filters['searchText'] . "%' and `deleted_at` is null),',') REGEXP CONCAT(',', REPLACE(`category_id`, ',', ',|,'),',|,',REPLACE(`parent_category`, ',', ',|,'),',')");
                    $business->orWhereIn('user_id', Owners::select('id')->where('full_name', 'like', '%' . $filters['searchText'] . '%'));
                });
            }
            // if(isset($filters['approved'])) {
            //     $business->where('approved', $filters['approved']);
            // }
            if (isset($filters['created_by'])) {
                $business->where('created_by', $filters['created_by'])->where('user_id', '!=', $filters['created_by']);
            }
            if (isset($filters['promoted'])) {
                $business->where('promoted', $filters['promoted']);
            }
            if (isset($filters['membership_type'])) {
                if (isset($filters['membership_premium_lifetime_type'])) {
                    $business->where('membership_type', '<>', 0);
                } else {
                    $business->where('membership_type', $filters['membership_type']);
                }
            }

            if (isset($filters['user_id'])) {
                $business->where('user_id', $filters['user_id']);
            }
        }

        return $business->count();
    }

    /**
     * Get all Business for admin
     * 
     * @param: $filters array of search parameters
     * @param: $paginate boolean.
     * @param: $isFromAdminPanel boolean.
     */
    public function getAllForAdmin($filters = array(), $paginate = false, $isFromAdminPanel = false)
    {                 
        // array_map('trim', $filters);
        $selectColumnsAdminQuery = [
            'business.id',
            'business.name',
            'business.membership_type',
            'asset_types.name as entity_type',
            DB::Raw("IFNULL(`business`.`latitude`, 0) AS latitude"),
            DB::Raw("IFNULL(`business`.`longitude`, 0) AS longitude"),
            DB::Raw("IFNULL(`business`.`address`, '') AS address"),
            DB::Raw("IFNULL(`business`.`document_approval`, '') AS document_approval"),
            DB::Raw("IFNULL(`business`.`street_address`, '') AS street_address"),
            DB::Raw("IFNULL(`business`.`country`, '') AS country"),
            DB::Raw("IFNULL(`business`.`state`, '') AS state"),
            DB::Raw("IFNULL(`business`.`city`, '') AS city"),
            DB::Raw("IFNULL(`business`.`locality`, '') AS locality"),
            DB::Raw("IFNULL(`business`.`taluka`, '') AS taluka"),
            DB::Raw("IFNULL(`business`.`district`, '') AS district"),
            DB::Raw("IFNULL(`business`.`pincode`, '') AS pincode"),
            DB::Raw("IFNULL(`business`.`country_code`, '') AS country_code"),
            DB::Raw("IFNULL(`business`.`mobile`, '') AS mobile"),
            DB::Raw("IFNULL(`business`.`website_url`, '') AS website_url"),
            DB::Raw("CASE `business`.`membership_type` 
                        WHEN 2 THEN '" . url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE')) . "'
                        WHEN 1 THEN '" . url(Config::get('constant.PREMIUM_ICON_IMAGE')) . "'
                        ELSE '" . url(Config::get('constant.BASIC_ICON_IMAGE')) . "'
                    END AS membership_type_icon"),
            'business.category_id',
            'business.business_logo',
            'business.parent_category',
            
            'business.approved',
            DB::Raw("IFNULL(`business`.`business_slug`, '') AS business_slug"),
            'business.user_id',
            'users.name AS user_full_name',
            'users.email AS email',
            DB::raw("(SELECT group_concat(categories.name) FROM categories  WHERE categories.id IN (business.parent_category) ) AS categoryName"),
          

        ];

        $business = Business::whereNull('business.deleted_at')
                        ->leftJoin("users", "users.id", "=", "business.user_id")
                        ->leftJoin("asset_types", "asset_types.id", "=", "business.asset_type_id");

                        //->join("business_ratings", "business_ratings.business_id", "=", "business.id");
                        //->join("categories", "categories.id", "=", "business.parent_category");

        $business->select($selectColumnsAdminQuery);

        if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $business->whereRaw(Auth::user()->sql_query);
        }

        if (isset($filters) && !empty($filters)) {
            if (isset($filters['agent_user']) && $filters['agent_user'] != '') {
                $business->where('agent_user', $filters['agent_user']);
            }
            if (isset($filters['offset']) && $filters['offset'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['offset'])->take($filters['take']);
            }
            if (isset($filters['city']) && $filters['city'] != '') {
                $business->where('city', $filters['city']);
            }
            
            if( (isset($filters['searchText']) && $filters['searchText'] != '') && (isset($filters['fieldtype']) && $filters['fieldtype'] != '')) {
                
                $filters['searchText'] = trim($filters['searchText']);

                switch ($filters['fieldtype']) {
                    case ('users'):                    
                        $business->where(function ($business) use ($filters) {
                            $business->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                        });
                        break;            
                    case ('owners'):
                        $business->where(function ($business) use ($filters) {
                            $business->whereHas('owners', function ($query) use ($filters) {
                                $query->where('full_name', 'like', '%' . $filters['searchText'] . '%');
                            });
                        });
                        break;            
                    case ('category_id'):
                        $business->where(function ($business) use ($filters) {
                            $business->whereExists(function ($query) use ($filters) {
                                $query->from('categories')
                                    ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                                    ->where('categories.name', 'like', '%' . $filters['searchText'] . '%');
                            });
                        });
                        break;
                
                    default:
                        $business->where($filters['fieldtype'], 'like', '%' . $filters['searchText'] . '%');
                        break;            
                }            

            } else if( (!isset($filters['searchText']) && $filters['searchText'] == '') && 
                    (isset($filters['fieldtype']) && $filters['fieldtype'] != '') && 
                    (isset($filters['isNull']) && $filters['isNull'] != '') ) {
                
                if ($filters['isNull'] == 0) {
                    $business->whereNull($filters['fieldtype']);
                } else {
                    $business->whereNotNull($filters['fieldtype']);
                }

            } else if(( isset($filters['searchText']) && $filters['searchText'] != '') &&
                (isset($filters['fieldtype']) || $filters['fieldtype'] == '') ) {

                $filters['searchText'] = trim($filters['searchText']);

                $business->where(function ($business) use ($filters) {

                    $business->orWhere('business.name', 'like', '%' . $filters['searchText'] . '%');

                 //   $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

                    $business->orWhere('mobile', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhere(function ($business) use ($filters) {
                        $business->where('users.name', 'like', '%' . $filters['searchText'] . '%');
                    });

                    // $business->orWhere(function ($business) use ($filters) {
                    //     $business->whereExists(function ($query) use ($filters) {
                    //         $query->from('categories')
                    //             ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                    //             ->where('categories.name', 'like', '%' . $filters['searchText'] . '%');
                    //     });
                    // });

                    $business->orWhere(function ($business) use ($filters) {
                        $business->whereHas('businessParentCategory', function ($query) use ($filters) {
                            $query->where('name', 'like', '%' . $filters['searchText'] . '%');
                        });
                    });

                    // $business->orWhere(function ($business) use ($filters) {
                    //     $business->whereHas('owners', function ($query) use ($filters) {
                    //         $query->where('full_name', 'like', '%' . $filters['searchText'] . '%');
                    //     });
                    // });
                    // $business->orWhere(function ($business) use ($filters) {
                    //     $business->whereIn('business.id', function ($query) use ($filters) {
                    //         $query->select('owners.business_id')
                    //                 ->from('owners')
                    //                 ->where('full_name', 'like', '%' . $filters['searchText'] . '%');
                    //     });
                    // });
                });
            }

            // if (isset($filters['searchText']) && $filters['searchText'] != '') {
            //     //$business->where('approved', 1);
            //     $business->where(function ($business) use ($filters) {

            //         $business->orWhere('business.name', 'like', '%' . $filters['searchText'] . '%');

            //         $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

            //         $business->orWhere('mobile', 'like', '%' . $filters['searchText'] . '%');

            //         $business->orWhere(function ($business) use ($filters) {
            //             $business->where('users.name', 'like', '%' . $filters['searchText'] . '%');
            //         });

            //         $business->orWhere(function ($business) use ($filters) {
            //             $business->whereExists(function ($query) use ($filters) {
            //                 $query->from('categories')
            //                     ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
            //                     ->where('categories.name', 'like', '%' . $filters['searchText'] . '%');
            //             });
            //         });

            //         $business->orWhere(function ($business) use ($filters) {
            //             $business->whereHas('businessParentCategory', function ($query) use ($filters) {
            //                 $query->where('name', 'like', '%' . $filters['searchText'] . '%');
            //             });
            //         });

            //         $business->orWhere(function ($business) use ($filters) {
            //             $business->whereHas('owners', function ($query) use ($filters) {
            //                 $query->where('full_name', 'like', '%' . $filters['searchText'] . '%');
            //             });
            //         });
            //     });
            // }

            if (isset($filters['approved']) && $filters['approved'] != '') {
                $business->where('approved', $filters['approved']);
            }

            if (isset($filters['created_by']) && $filters['created_by'] != '') {
                $business->where('created_by', $filters['created_by'])->where('user_id', '!=', $filters['created_by']);
            }

            if (isset($filters['promoted']) && $filters['promoted'] != '') {
                $business->where('promoted', $filters['promoted']);
            }

            if (isset($filters['membership_type']) && $filters['membership_type'] != '') {
                if (isset($filters['membership_premium_lifetime_type'])) {
                    $business->where('membership_type', '<>', 0);
                } else {
                    $business->where('membership_type', $filters['membership_type']);
                }
            }

            if (isset($filters['user_id']) && $filters['user_id'] != '') {
                $business->where('user_id', $filters['user_id']);
            }
                
            if(isset($filters['country_code']) && $filters['country_code'] != '')
            {
                $business->where('business.country_code', $filters['country_code']);
            }

            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if ($filters['sortBy'] == 'popular') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('visits', 'DESC');
                } elseif ($filters['sortBy'] == 'ratings') {
                    $business->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    $business->orderBy('membership_type', 'DESC')->orderBy('average_rating', 'DESC');
                } elseif ($filters['sortBy'] == 'AtoZ') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'ASC');
                } elseif ($filters['sortBy'] == 'ZtoA') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'DESC');
                } elseif ($filters['sortBy'] == 'promoted') {
                    $business->orderBy('id', 'DESC');
                    $business->orderBy('promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'membership_type') {
                    $business->orderBy('membership_type', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }

                   //$business->orderBy('distance', 'ASC');
                    $business->orderBy(\DB::raw('-`distance`'), 'DESC');
                } elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    $business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    if (isset($filters['radius']) && !empty($filters['radius'])) {
                        $business->having('distance', '<', $filters['radius']);
                    }

                    $business->orderBy('membership_type', 'DESC')->orderBy(\DB::raw('-`distance`'), 'DESC');
                } else {
                    $business->orderBy('business.id', 'DESC');
                }
            } else {
                $business->orderBy('business.id', 'DESC');
            }
        } else {
            $business->orderBy('business.id', 'DESC');
            
            if (isset($filters['page']) && $filters['page'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
                $business->skip($filters['page'])->take($filters['take']);
            } else {
                $business->skip(1)->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            }
        }

        if ($isFromAdminPanel == true) {
            if (isset($paginate) && $paginate == true && isset($filters['take']) && $filters['take'] > 0) {
                return $business->paginate($filters['take']);
            } elseif (isset($paginate) && $paginate == true) {
                return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $business->get();
            }
        } else {
            if (isset($paginate) && $paginate == true) {
                return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $business->get();
            }
        }
    }

    public function getLifetimeMembersCount($filters = array())
    {
        $business = Business::whereNull('deleted_at')
                    ->where('approved', 1)
                    ->where('membership_type', $filters['membership_type']);

        return $business->count();
    }

    /**
     * Get Business listing of Life time members.
     * 
     * @param: $filters - Array for user
     */
    public function getLifetimeMembers($filters = array(), $paginate = false)
    {
        $selectColumns = [
            'business.id',
            'business.name',
            DB::Raw("SUBSTRING(`business`.`description`, 1, 50) AS description"),
            'bOwners.full_name',
            'bOwners.photo',
        ];

        $SQLQuery = "(SELECT business_id, full_name, photo FROM owners GROUP BY business_id) AS bOwners";
        
        $business = Business::join(DB::raw($SQLQuery), 'business.id', '=', 'bOwners.business_id')
                    ->whereNull('business.deleted_at')
                    ->where('approved', 1)
                    ->where('membership_type', $filters['membership_type']);

        $business->with(['owners:business_id,full_name']);
        $business->select($selectColumns);
        $business->orderBy('approved');

        if (isset($paginate) && $paginate == true) {
            return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $business->get();
        }
    }

    public function getAllHomeBusinesses($filters = array(), $paginate = false)
    {
        $selectColumns = [
            'business.id',
            'business.name',
            'business.membership_type',
            DB::Raw("IFNULL(`business`.`latitude`, 0) AS latitude"),
            DB::Raw("IFNULL(`business`.`longitude`, 0) AS longitude"),
            DB::Raw("IFNULL(`business`.`address`, '') AS address"),
            DB::Raw("IFNULL(`business`.`document_approval`, '') AS document_approval"),
            DB::Raw("IFNULL(`business`.`street_address`, '') AS street_address"),
            DB::Raw("IFNULL(`business`.`country`, '') AS country"),
            DB::Raw("IFNULL(`business`.`state`, '') AS state"),
            DB::Raw("IFNULL(`business`.`city`, '') AS city"),
            DB::Raw("IFNULL(`business`.`locality`, '') AS locality"),
            DB::Raw("IFNULL(`business`.`taluka`, '') AS taluka"),
            DB::Raw("IFNULL(`business`.`district`, '') AS district"),
            DB::Raw("IFNULL(`business`.`pincode`, '') AS pincode"),
            DB::Raw("IFNULL(`business`.`website_url`, '') AS website_url"),
            DB::Raw("IFNULL(`business`.`description`, '') AS description"),
            DB::Raw("IFNULL(`business`.`phone`, '') AS phone"),
            DB::Raw("IFNULL(`business`.`country_code`, '') AS country_code"),
            DB::Raw("IFNULL(`business`.`mobile`, '') AS mobile"),
            DB::Raw("IFNULL(`business`.`email_id`, '') AS email_id"),
            DB::Raw("CASE `business`.`membership_type` 
                        WHEN 2 THEN '" . url(Config::get('constant.LIFETIME_PREMIUM_ICON_IMAGE')) . "'
                        WHEN 1 THEN '" . url(Config::get('constant.PREMIUM_ICON_IMAGE')) . "'
                        ELSE '" . url(Config::get('constant.BASIC_ICON_IMAGE')) . "'
                    END AS membership_type_icon"),
            'business.category_id',
            'business.business_logo',
            'business.parent_category',
            
            DB::Raw("IFNULL(`business`.`business_slug`, '') AS business_slug"),
            'business.user_id',
        ];
        $business = Business::whereNull('business.deleted_at')->where('approved', 1);

        if (isset($filters) && !empty($filters)) {
            if (isset($filters['searchText']) && $filters['searchText'] != '') {
                $business->where(function ($business) use ($filters) {

                    $business->orWhere('name', 'like', '%' . $filters['searchText'] . '%');

                    $business->orWhereRaw("FIND_IN_SET('" . $filters['searchText'] . "', metatags)");

                    $business->orWhere('mobile', 'like', $filters['searchText'] . '%');
                    
                   /* $business->orWhere(function($business) use ($filters){
                        $business->whereHas('user', function($query) use ($filters){
                            $query->where('name', 'like', '%'.$filters['searchText'].'%');
                        });    
                    });*/
                    $business->orWhereIn('user_id', User::select('id')->where('name', 'like', '%' . $filters['searchText'] . '%'));

                  /*  $business->orWhere(function($business) use ($filters){
                        $business->whereExists(function ($query) use ($filters){
                            $query->from('categories')
                                  ->whereRaw("FIND_IN_SET(categories.id, business.category_id)")
                                  ->where('categories.name', 'like', '%'.$filters['searchText'].'%');
                        });    
                    });

                    $business->orWhere(function($business) use ($filters){
                        $business->whereHas('businessParentCategory', function($query) use ($filters){
                            $query->where('name', 'like', '%'.$filters['searchText'].'%');
                        });
                    }); */
                    $business->orWhereRaw("CONCAT(',', (select GROUP_CONCAT(id) from categories where name like '%" . $filters['searchText'] . "%' and `deleted_at` is null),',') REGEXP CONCAT(',', REPLACE(`category_id`, ',', ',|,'),',|,',REPLACE(`parent_category`, ',', ',|,'),',')");
               /*     $business->orWhere(function($business) use ($filters){
                        $business->whereHas('owners', function($query) use ($filters){
                            $query->select('business_id','full_name');
                            $query->where('full_name', 'like', '%'.$filters['searchText'].'%');
                        });    
                    });*/
                    $business->orWhereIn('user_id', Owners::select('id')->where('full_name', 'like', '%' . $filters['searchText'] . '%'));
                });
            }

            if (isset($filters['sortBy']) && !empty($filters['sortBy'])) {
                if ($filters['sortBy'] == 'popular') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('visits', 'DESC');
                } elseif ($filters['sortBy'] == 'ratings') {
                    //$business->selectRaw('*, (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id) As average_rating');
                    // $selectColumns[] = \DB::raw(' (SELECT AVG(business_ratings.rating) FROM business_ratings WHERE business_ratings.business_id = business.id GROUP BY business_ratings.business_id) As average_rating');                    
                    // $business->orderBy('membership_type', 'DESC')->orderBy('average_rating', 'DESC');                    
                    
                    $selectColumns[] = \DB::raw('AVG(business_ratings.rating) As average_rating');                    
                    $business->leftJoin('business_ratings', 'business_ratings.business_id', '=', 'business.id');
                    $business->groupBy('business_ratings.business_id')
                            ->orderBy('membership_type', 'DESC')
                            ->orderBy('average_rating', 'DESC');
                } elseif ($filters['sortBy'] == 'AtoZ') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'ASC');
                } elseif ($filters['sortBy'] == 'ZtoA') {
                    $business->orderBy('membership_type', 'DESC')->orderBy('name', 'DESC');
                } elseif ($filters['sortBy'] == 'promoted') {
                    $business->orderBy('id', 'DESC');
                    $business->orderBy('promoted', 'DESC');
                } elseif ($filters['sortBy'] == 'membership_type') {
                    $business->orderBy('membership_type', 'DESC');
                } elseif ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    //$business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');                    
                    

                   //$business->orderBy('distance', 'ASC');
                    
                } elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
                    //$business->selectRaw('*, ( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');

                    $selectColumns[] = \DB::raw('( 6371 * acos( cos( radians(' . $filters['latitude'] . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $filters['longitude'] . ') ) + sin( radians(' . $filters['latitude'] . ') ) * sin( radians( latitude ) ) ) ) AS distance');
                    

                                       
                } else {
                    $business->orderBy('id', 'DESC');
                }
            } else {
                $business->orderBy('id', 'DESC');
            }
        } else {
            $business->orderBy('id', 'DESC');
        }
        
        $business->with(['owners:business_id,full_name', 'user:id,name']);
        $business->select($selectColumns);

        if ($filters['sortBy'] == 'nearMe' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters['longitude']) && !empty($filters['longitude'])) {
            if (isset($filters['radius']) && !empty($filters['radius'])) {
                $business->having('distance', '<', $filters['radius']);
            }
            $business->orderBy(\DB::raw('-`distance`'), 'DESC');
        } elseif ($filters['sortBy'] == 'relevance' && isset($filters['latitude']) && !empty($filters['latitude']) && isset($filters[
            'longitude']) && !empty($filters['longitude'])) {
            if (isset($filters['radius']) && !empty($filters['radius'])) {
                $business->having('distance', '<', $filters['radius']);
            }
            $business->orderBy('membership_type', 'DESC')->orderBy(\DB::raw('-`distance`'), 'DESC'); 
        }

        if (isset($paginate) && $paginate == true) {
            return $business->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $business->limit(25)->get();
        }
    }
}
