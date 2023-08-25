<?php

namespace App\Traits;

use App\City;
use App\Helpers\Helpers;
use App\NotificationList;
use App\PublicPost;
use App\PublicPostImage;
use App\PublicPostVideo;
use Illuminate\Support\Facades\Storage;
use Validator;

trait PublicPostTrait {
	
	/**
	 * for validate input post
	 *
	 * @param  mixed $request
	 * @return object
	 */
	public function validatePost($request)
	{
		$validator = Validator::make($request->all(), 
			[                    
				//'title' => 'required',
				'category' => 'required',
				'site_id' => 'required_if:post_type,=,2',
				'source' => 'in:myself,external',
				'type' => 'in:admin,business_user',
				'status' => 'in:draft,active,inactive',
				'external_link' =>  'url',
                'images' => 'max:4'
            ],[
                'images.max' => "You can't upload greater than 4 images"
            ]
		);

		return $validator;
	}
	
	/**
	 * for get post category
	 *
	 * @return string
	 */
	public function getPostCategory()
	{
		return trim(Helpers::isOnSettings('post_category'));
	}
	
	/**
	 * for get moderator keywords
	 *
	 * @return string
	 */
	public function getModeKeywords()
	{
		return trim(Helpers::isOnSettings('moderator_keywords'));
	}
        
    /**
     * uploadImage
     *
     * @param  mixed $request
     * @return void
     */
    public function uploadImage($postId,$request)
	{
		try {
			if ($request->file('images')) 
			{  
				$images = $request->file('images');                
                foreach($images as $image) {
                    $fileName = 'post_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = config('constant.POST_IMAGE_PATH');
                    //Uploading on AWS
                    $originalImage = Helpers::addFileToStorage($fileName, $path, $image, "s3");
                    $url = $path.$originalImage;
                    PublicPostImage::create(['post_id'=>$postId, 'url'=>$url]);
                }
			} else {
				info("post image is empty");
			}
		} catch (\Throwable $th) {
			\Log::error("Getting error while uploading post image: ".$th);
		}
	}

    /**
     * uploadVideo
     *
     * @param  mixed $request
     * @return void
     */
    public function uploadVideo($postId,$request)
	{
       
		try {
            $data=$request->all();
           
                $video = $request->file('video'); 
                $fileName = 'post_' . uniqid() . '.' . $video->getClientOriginalExtension();
                $path = config('constant.POST_VIDEO_PATH'); 
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $path, $video, "s3");
                //print_r($originalImage); exit;
                $url = $path.$originalImage;
            
                PublicPostVideo::create(['post_id'=>$postId, 'url'=>$url]);
            
            //  }
				   
            
		
		} catch (\Throwable $th) {
			\Log::error("Getting error while uploading post image: ".$th);
		}
	}
    
    /**
     * delete all images of post
     *
     * @param  mixed $postId
     * @return void
     */
    public function deleteImage($postId)
	{
		try {
            $post = PublicPost::find($postId);
            if($post) {
                foreach($post->images as $image) {
                    if(Storage::disk(config('constant.DISK'))->exists($image->url)) {
                        Storage::disk(config('constant.DISK'))->delete($image->url);
                    }
                    PublicPostImage::whereId($image->id)->delete();
                }
            }
		} catch (\Throwable $th) {
			\Log::error("Getting error while deleting post image: ".$th);
		}
	}

        
    /**
     * delete  video of post
     *
     * @param  mixed $postId
     * @return void
     */
    public function deleteVideo($postId)
	{
		try {
            if($postId) { 
              $videoData = PublicPostVideo::select('url')
              ->where('post_id','=', $postId)
              ->first();
              
            if(Storage::disk(config('constant.DISK'))->exists($videoData->url)) {
                Storage::disk(config('constant.DISK'))->delete($videoData->url);
            }
               PublicPostVideo::where('post_id', $postId)->delete();
                   
            }
		} catch (\Throwable $th) {
			\Log::error("Getting error while deleting post image: ".$th);
		}
	}

    /**
     * delete  images of post by image ids
     *
     * @param  mixed $postId
     * @return void
     */
    public function deleteImageById($ids)
	{
		try {
            $images = PublicPostImage::whereIn("id",$ids)->get();
            foreach($images as $image) {
                if(Storage::disk(config('constant.DISK'))->exists($image->url)) {
                    Storage::disk(config('constant.DISK'))->delete($image->url);
                }
                $image->delete();
            }
		} catch (\Throwable $th) {
			\Log::error("Getting error while deleting post image by ids: ".$th);
		}
	}
    
    /**
     * validateLike
     *
     * @param  mixed $request
     * @return object
     */
    public function validateLike($request)
	{
		$validator = Validator::make($request->all(),[                    
				'post_id' => 'required'
			]
		);
		return $validator;
	}
    
    /**
     * get latitude  and longitude by city name
     *
     * @param  mixed $cityName
     * @return void
     */
    public function getLatLngByCity($cityName)
    {
        $city = City::where('name',$cityName)->first();
        if($city) {
            $data['latitude'] = $city->latitude;
            $data['longitude'] = $city->longitude;
        } else {
            $data['latitude'] = Null;
            $data['longitude'] = Null;
        }

        return $data;
    }
    
    /**
     * for send liked / shared post notification
     *
     * @param  mixed $request
     * @param  mixed $isLiked
     * @return void
     */
    public function sendLikeShareNotification($request,$isLiked = true)
	{
		try {
            $post = PublicPost::find($request->post_id);
            if($post) {
                $userName = auth()->user()->name;
                $userId = $post->user_id;
                if($isLiked) {
                    $likeLabel = 'liked';
                } else {
                    $likeLabel = 'shared';
                }
                //Send push notification to  User
                $notificationData['title'] = "Post $likeLabel";
                $notificationData['message'] = "$userName $likeLabel your post";
                $notificationData['type'] = '17';
                Helpers::sendPushNotification($userId, $notificationData);
    
                $notificationData['user_id'] = $userId;
                $notificationData['data'] = json_encode(['site_id'=> $request->site_id, 'post_id' => $request->post_id]);
                $notificationData['user_name'] = ($post->user) ? $post->user->name : '';
                $notificationData['activity_user_id'] = auth()->id();
    
                NotificationList::create($notificationData); 
            }
		} catch (\Throwable $th) {
			\Log::error("Getting error while sending notification when invite to user: ".$th);
		}
	}
}
