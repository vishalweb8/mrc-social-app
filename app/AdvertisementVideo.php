<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdvertisementVideo extends Model
{
    //
    protected $table = 'advertisement_videos';
    protected $fillable = ['advertisement_id', 'video_link', 'video_id', 'thumbnail'];

    /**
     * Save the video data.
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
            return AdvertisementVideo::where('id', $data['id'])->update($updateData);
        } else {
            return AdvertisementVideo::create($data);
        }
    }

    /**
     * Get list of videos selected Ads.
     * 
     * @param: 
     *  $adsId : Selected Ads id
     */
    public function findById($adsId)
    {
        return AdvertisementVideo::where('advertisement_id', $adsId)
            ->get(['id', 'advertisement_id', 'video_link', 'video_link AS url', 'video_id', 'thumbnail']);
    }

    /**
     * Filter the data and return 1st object.
     */
    public function filterSingleObect($filter)
    {
        $video = AdvertisementVideo::where($filter)
            ->select(['id', 'advertisement_id', 'video_link', 'video_link AS url', 'video_id', 'thumbnail'])
            ->first();
        return $video;
    }

    /**
     * Remove all the video links which is not 
     */
    public function removeVideos($advertisementId, $videoLinks)
    {
        $videos = AdvertisementVideo::where('advertisement_id', '=', $advertisementId)
            ->whereNotIn('video_link', array_values($videoLinks))
            ->get();
        try {
            foreach ($videos as $video) {
                $video->delete();
            }
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Update the all videos links which are saved in database.
     * 
     * @return 
     *      @param: Total stored videos links.
     */
    public function updateVideos($advertisementId, $videoLinks)
    {
        $savedVideos = AdvertisementVideo::where('advertisement_id', '=', $advertisementId)
                ->select(['id', 'advertisement_id', 'video_link', 'video_link AS url', 'video_id', 'thumbnail'])
                ->get();
        
        foreach($savedVideos as $rIndex => $video) {
            if(isset($videoLinks[$rIndex]) && $videoLinks[$rIndex] != "") {
                $videoId = '';
                $videoLinkValue = $videoLinks[$rIndex];

                $this->AddVideo($advertisementId, $videoLinkValue, $video->id);
            }
        }

        return AdvertisementVideo::where('advertisement_id', '=', $advertisementId)->count();
    }


    public function AddVideo($advertisementId, $videoLinkValue, $Id = 0)
    {
        if(isset($videoLinkValue) && $videoLinkValue != "") {
            $videoId = '';

            
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoLinkValue, $match))
            {
                $videoId = $match[1];
                $thumbnailVideo = 'https://img.youtube.com/vi/'.$videoId.'/1.jpg';
            }
            $filter = [
                ['video_link', '=', $videoLinkValue],
                ['advertisement_id', '=', $advertisementId],
                ['video_id', '=', $videoId]
            ];

            $isSaved = $this->filterSingleObect($filter);
            if(!$isSaved) {
                $video['advertisement_id'] = $advertisementId;
                $video['video_link'] = $videoLinkValue;
                $video['video_id'] = $videoId;
                $video['thumbnail'] = $thumbnailVideo;
                $video['id'] = $Id;

                $response = $this->insertUpdate($video);
            }
        }
    }

    /**
     * Remove all the video links by Ids
     */
    public function removeVideosByIds($advertisementId, $videoIds)
    {
        return AdvertisementVideo::where('advertisement_id', '=', $advertisementId)
            ->whereIn('video_link', array_values($videoIds))
            ->delete();
    }

    /**
     * Delete all vidoes of the advertisement.
     */
    public function removeAllVideos($advertisementId)
    {
        AdvertisementVideo::where('advertisement_id', '=', $advertisementId)->delete();
    }
}
