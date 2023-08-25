<?php

namespace App;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;
use Storage;
class AdvertisementImage extends Model
{
    //
    protected $table = 'advertisement_images';
    protected $fillable = ['advertisement_id', 'image_name'];
    protected $appends = ['image_name'];

    public function getImageNameAttribute()
    {
        if(!empty($this->thumbnail_url)) { 
            $url = config('constant.ADVERTISEMENT_THUMBNAIL_IMAGE_PATH').$this->thumbnail_url;
            if(Storage::disk(config('constant.DISK'))->exists($url)) { 
                $url = Storage::disk(config('constant.DISK'))->url($url);
            }
            else{
                $url = url(config('constant.DEFAULT_IMAGE'));
            }
        } else {
            $url = url(config('constant.DEFAULT_IMAGE'));
        }
        return $url;
    }
    /**
     * Save or update the image.
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) {
            $updateData = [];
            foreach ($this->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            return AdvertisementImage::where('id', $data['id'])->update($updateData);
        } else {        
            return AdvertisementImage::create($data);
        }
    }

    /**
     * Get list of images selected Ads.
     * 
     * @param: 
     *  $adsId : Selected Ads id
     */
    public function findById($adsId) {
        return AdvertisementImage::where('advertisement_id', $adsId)
                        ->get(['advertisement_id', 'image_name']);
    }
}
