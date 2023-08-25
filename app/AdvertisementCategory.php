<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdvertisementCategory extends Model
{
    //
    protected $table = 'advertisement_categories';

    /**
     * category_type --> 0 - Parent, 1 - Child
     */
    protected $fillable = ['advertisement_id', 'category_id', 'category_type'];

    public function advertisement()
    {
        return $this->belongsTo('App\Advertisement', 'advertisement_id');
    }
    
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
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
            return AdvertisementCategory::where('id', $data['id'])->update($updateData);
        } else {
            return AdvertisementCategory::create($data);
        }
    }

    /**
     * Get list of Category for Ads, according to category type
     * 
     * @param: 
     *  $adsId : Selected Ads id
     *  $categoryType : 0 - Parent, 1 - Child
     */
    public function findById($adsId, $categoryType = 0) {
        return AdvertisementCategory::where('advertisement_id', $adsId)
                        ->where('category_type', $categoryType)
                        ->get();
    }
    
    /**
     * Filter the data and return 1st object.
     */
    public function filterSingleObect($filter) {
        $category = AdvertisementCategory::where($filter)->first();
        return $category;
    }

    /**
     * Remove all categories for selected ads.
     */
    public function removeCategoriesForAds($filter) {
        return AdvertisementCategory::where($filter)->delete();
    }

    /**
     * Remove those category which are not belongs to Advertisment.
     */
    public function removeCategoriesByIds($advertisementId, $categoryIds, $categoryType) {
        if(count($categoryIds) > 0) {
            return AdvertisementCategory::where('advertisement_id', '=', $advertisementId)
                                ->where('category_type', '=', $categoryType)
                                ->whereNotIn('category_id', array_values($categoryIds))
                                ->delete();
        } else {
            return AdvertisementCategory::where('advertisement_id', '=', $advertisementId)
                                ->where('category_type', '=', $categoryType)
                                ->delete();
        }
    }
}
