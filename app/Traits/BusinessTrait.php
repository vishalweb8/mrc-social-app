<?php

namespace App\Traits;

use App\AssetType;
use App\Business;
use App\Category;
use App\Helpers\Helpers;
use App\OnlineStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use DB;

trait BusinessTrait {
    
    /**
     * for get array of business parent category
     *
     * @param  mixed $parentCategoryIds
     * @return object
     */
    public function getBusinessParentCategory($parentCategoryIds)
    {
        try {
            $categories = [];
            if(!empty($parentCategoryIds)) {
                $ids = explode(',', $parentCategoryIds);
                $cacheName = 'cat_'. str_replace(",", "_", $parentCategoryIds);
                $seconds = 1800;
                
                $categories = Cache::remember($cacheName, $seconds, function () use ($ids) {
                    return Category::whereIn('id', $ids)
                    ->select('id AS category_id', 
                            'name AS category_name', 
                            'category_slug', 
                            'cat_logo')
                    ->get(); 
                });
            }
            return $categories;
        } catch (\Throwable $th) {
            Log::error('getting error while getting business parent category:- '.$th);
            return [];
        }
    }
    
    /**
     * get entity id by name
     *
     * @param  mixed $entity
     * @return void
     */
    public function getEntityIdByName($entityName)
    {
        try {
            $entityId = null;
            if(!empty($entityName)) {
                $cacheName = 'asset_type_id'.$entityName;
                $seconds = 3600;
                
                $entityId = Cache::remember($cacheName, $seconds, function () use ($entityName) {
                    $entityType = AssetType::where('name','like',$entityName.'%')->first();
                    $entityId = null;
                    if($entityType) {
                        $entityId = $entityType->id;
                    }
                    return $entityId; 
                });
            }
            return $entityId;
        } catch (\Throwable $th) {
            Log::error('getting error while getting entity id by name:- '.$th);
            return null;
        }
    }
    
    /**
     * getPromotedEntity
     *
     * @param  mixed $entityType
     * @return void
     */
    public function getPromotedEntity($entityType)
    {
        try {
            $filters['promoted'] = 1;
            $entityTypeId = $this->getEntityIdByName($entityType);
            if(!empty($entityTypeId)) {
                $filters['asset_type_id'] = $entityTypeId;
            }
            $entities = Business::select('business.id','business.name','business.business_slug','business.business_logo','business.category_id','business.description','business.metatags',DB::raw('(select round(AVG(rating),1) from business_ratings where business_id = business.id) as avg_rating'),DB::raw('(select COUNT(id) from business_ratings where business_id = business.id) as total_review'),DB::raw('(select name from asset_types where id = business.asset_type_id) as entity_type'))
            ->where('approved',1)
            ->where($filters)
            ->limit(5)->get()->makeHidden(['category_id','business_logo']);
            return $entities;
        } catch (\Throwable $th) {
            Log::error("getting error while fetch promoted entity:- ".$th);
            return [];
        }
    }
    
    /**
     * convert online store detail json to array
     *
     * @param  mixed $stores
     * @return void
     */
    public function getEntityOnlineStores($storeUrl)
    {
        try {
            $onlineStores = [];
            if(!empty($storeUrl)) {
                $stores = json_decode($storeUrl);
                $storeIds = array_column($stores,'id');
                $activeStores = OnlineStore::whereIn('id',$storeIds)->whereStatus(1)->get();
                foreach ($activeStores as $key => $activeStore) {
                    $onlineStores[$key]['id'] = $activeStore->id;
                    $onlineStores[$key]['name'] = $activeStore->name;
                    $onlineStores[$key]['logo'] = $activeStore->logo;
                    $index = array_search($activeStore->id,$storeIds);
                    $onlineStores[$key]['url'] = $stores[$index]->url;
                }
            }
            return $onlineStores;
        } catch (\Throwable $th) {
            Log::error("getting error while convert json to array of stores detail :- ".$th);
            return [];
        }
    }    
        
    /**
     * getExtendedEntityDetail
     *
     * @param  mixed $entity
     * @return array
     */
    public function getExtendedEntityDetail($entity)
    {
        try {
            $data['id'] = $entity->id;
            $data['name'] = $entity->name;
            $data['asset_type_id'] = $entity->asset_type_id;
            $data['business_slug'] = (!empty($entity->business_slug)) ? $entity->business_slug : '';
            $data['entity_type'] = (isset($entity->entityType)) ? $entity->entityType->name : 'Business';
            $data['user_id'] = (!empty($entity->user)) ? $entity->user->id : '';
            $data['user_name'] = (!empty($entity->user)) ? $entity->user->name : '';
            $data['business_logo_url'] = $entity->business_logo_url;
            $data['business_images'] = $this->getEntityImages($entity->businessImages);
            $data['full_address'] = $entity->address;
            $data['street_address'] = $entity->street_address;
            $data['locality'] = $entity->locality;
            $data['country'] = $entity->country;
            $data['state'] = $entity->state;
            $data['city'] = $entity->city;
            $data['taluka'] = $entity->taluka;
            $data['district'] = $entity->district;
            $data['pincode'] = $entity->pincode;
            $data['phone'] = $entity->phone;
            $data['country_code'] = $entity->country_code;
            $data['mobile'] = $entity->mobile;
            $data['email'] = $entity->email_id;
            $data['latitude'] = (!empty($entity->latitude)) ? $entity->latitude : 0;
            $data['longitude'] = (!empty($entity->longitude)) ? $entity->longitude : 0;
            $data['descriptions'] = (!empty($entity->description)) ? $entity->description : '';
            $data['short_description'] = (!empty($entity->short_description)) ? $entity->short_description : '';
            $data['metatags'] = (!empty($entity->metatags)) ? $entity->metatags : '' ;
            $data['seo_meta_tags'] = (!empty($entity->seo_meta_tags)) ? $entity->seo_meta_tags : '' ;
            $data['seo_meta_description'] = (!empty($entity->seo_meta_description)) ? $entity->seo_meta_description : '' ;
            $data['approved'] = $entity->approved;
            $data['verified'] = $entity->verified;
            $data['visits'] = $entity->visits;

            // for language drop downs
            $languages = trim(Helpers::isOnSettings('other_languages'));
            $othersLangs = [];
            if(!empty($languages)) {
                $othersLangs = explode(',',$languages);
            }
            array_unshift($othersLangs,'english');
            $data['languages'] = $othersLangs;

            $parentCatArray = $this->getBusinessParentCategory($entity->parent_category);
            $data['parent_categories'] = $parentCatArray;
            $data['parent_category_name'] = (!empty($parentCatArray)) ? implode(', ',$parentCatArray->pluck('category_name')->toArray()) : '';

            return $data;
        } catch (\Throwable $th) {
            Log::error("getting error while getting extended entity detail :- ".$th);
            return [];
        }
    }
    
    /**
     * getEntityImages
     *
     * @param  mixed $images
     * @return void
     */
    public function getEntityImages($images)
    {
        $g = 0;
        $data = [];
        foreach($images as $businessImgValue)
        {
            if(!empty($businessImgValue->image_name))
            {
                if ($businessImgValue->image_name != '' &&  config('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name){
                    $s3url =   config('constant.s3url');
                    $imgThumbUrl = $s3url.config('constant.BUSINESS_ORIGINAL_IMAGE_PATH').$businessImgValue->image_name;
                }else{
                    $imgThumbUrl = '';
                }

                if(!empty($imgThumbUrl))
                {
                    $data[$g]['id']= $businessImgValue->id;
                    $data[$g]['image_name']= $imgThumbUrl;
                    $g++;
                }
            }
        }
        return $data;
    }
}
