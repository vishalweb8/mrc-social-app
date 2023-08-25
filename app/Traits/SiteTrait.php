<?php

namespace App\Traits;

use App\Helpers\Helpers;
use App\NotificationList;
use App\Role;
use App\Site;
use App\SiteContactDetail;
use App\SiteImage;
use App\SiteUser;
use App\SocialMedia;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

trait SiteTrait {
    
    /**
     * validateSite
     *
     * @param  mixed $request
     * @return object
     */
    public function validateSite($request)
	{
		$validator = Validator::make($request->all(), 
			[ 
				'name' => 'required',
				'asset_type_id' => 'required',
				'visibility' => 'in:true,false',
				'link' =>  'url',
                'logo' => 'image|mimes:jpeg,png,jpg',
                'images' => 'max:4',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            ],[
                'images.max' => "You can't upload greater than 4 images",
                'images.*.max' => "File size must be less than 2 MB",
                'link.url' => 'Website is invalid url'
            ]
		);

		return $validator;
	}    
        
    /**
     * for upload image on cloud storage
     *
     * @param  mixed $image
     * @return void
     */
    public function uploadImage($image)
	{
        $url = null;
		try {
			if (!empty($image)) 
			{
                $fileName = 'site_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = config('constant.SITE_IMAGE_PATH');
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $path, $image, "s3");
                $url = $path.$originalImage;
			}
		} catch (\Throwable $th) {
			Log::error("Getting error while uploading site logo: ".$th);
		}
        return $url;
	}    
        
    /**
     * for upload site images/cover
     *
     * @param  mixed $siteId
     * @param  mixed $request
     * @return void
     */
    public function uploadImages($siteId,$request)
	{
		try {
			if ($request->file('images')) 
			{  
				$images = $request->file('images');
                SiteImage::where('site_id',$siteId)->delete();      
                foreach($images as $image) {
                    $url = $this->uploadImage($image);
                    SiteImage::create(['site_id'=>$siteId, 'url'=>$url]);
                }
			} else {
				info("site images is empty");
			}
		} catch (\Throwable $th) {
			Log::error("Getting error while uploading site image: ".$th);
		}
	}
    
    /**
     * delete uploaded images of site
     *
     * @param  mixed $ids
     * @return void
     */
    public function deleteImages($ids)
	{
		try {
            $images = SiteImage::whereIn("id",$ids)->get();
            foreach($images as $image) {
                if(Storage::disk(config('constant.DISK'))->exists($image->url)) {
                    Storage::disk(config('constant.DISK'))->delete($image->url);
                }
                $image->delete();
            }
		} catch (\Throwable $th) {
			Log::error("Getting error while deleting site image by ids: ".$th);
		}
	}
    
    /**
     * create/update social link of site
     *
     * @param  mixed $id
     * @param  mixed $request
     * @return void
     */
    public function saveSocialLink($id,$request)
	{
		try {
            $socials = $request->input('socials',[]);
            if(is_array($socials)) {
                $socials = $socials;
            }else if(!empty($socials)) {
                $socials = json_decode($socials,true);
            }

            foreach($socials as $key => $social) {
                if(!empty($key)) {
                    $query = [
                        'site_id' => $id,
                        'name' => $key
                    ];
                    $data = $query;
                    $data['url'] = $social;
                    SocialMedia::updateOrCreate($query,$data);              
                }
            }
		} catch (\Throwable $th) {
			Log::error("Getting error while saving social links of site: ".$th);
		}
	}
    
    /**
     * for save contact detail of site
     *
     * @param  mixed $id
     * @param  mixed $request
     * @return void
     */
    public function saveContactDetail($id,$request)
	{
		try {
            cache()->forget('site_contact_'.$id);
            $data = $request->only(['mobile_no','address','location_id']);
            $data['site_id'] = $id;
            $data['name'] = $request->contact_name;
            SiteContactDetail::updateOrCreate(['site_id' => $id],$data);            
		} catch (\Throwable $th) {
			Log::error("Getting error while saving contact detail of site: ".$th);
		}
	}
    
    /**
     * for send invited notification to user
     *
     * @param  mixed $siteId
     * @param  mixed $userId
     * @return void
     */
    public function sendInvitedNotification($siteId, $userId)
	{
		try {
            $site = Site::find($siteId);
            if($site) {
                $userName = auth()->user()->name;
                $siteName = $site->name;
                //Send push notification to  User
                $notificationData['title'] = 'Site invited';
                $notificationData['message'] = "Invited by $userName for join $siteName site";
                $notificationData['type'] = '14';
                Helpers::sendPushNotification($userId, $notificationData);
    
                $notificationData['user_id'] = $userId;
                $notificationData['data'] = json_encode(['site_id'=> $siteId]);
                $notificationData['user_name'] = ($site->createdBy) ? $site->createdBy->name : '';
                $notificationData['activity_user_id'] = auth()->id();
    
                NotificationList::create($notificationData);          
            }
		} catch (\Throwable $th) {
			Log::error("Getting error while sending notification when invite to user: ".$th);
		}
	}
    
    /**
     * for send accepted invitation notification to user/ site admin
     *
     * @param  mixed $siteId
     * @param  mixed $userId
     * @param  mixed $isAdmin
     * @return void
     */
    public function sendInvtAcceptedNotification($siteId, $userId, $isAdmin = false)
	{
		try {
            $site = Site::find($siteId);
            if($site) {
                if($isAdmin) {
                    $user = User::find($userId);
                    $userName = ($user) ? $user->name : '';
                    $receiverName = $userName;
                } else {
                    $userName = auth()->user()->name;
                    $userId = $site->created_by;
                    $receiverName = ($site->createdBy) ? $site->createdBy->name : '';
                }
                $siteName = $site->name;
                //Send push notification to  User
                $notificationData['title'] = 'Site invitation Accepted';
                $notificationData['message'] = "$userName to joined $siteName site";
                $notificationData['type'] = '16';
                Helpers::sendPushNotification($userId, $notificationData);
    
                $notificationData['user_id'] = $userId;
                $notificationData['data'] = json_encode(['site_id'=> $siteId]);
                $notificationData['user_name'] = $receiverName;
                $notificationData['activity_user_id'] = auth()->id();
    
                NotificationList::create($notificationData);          
            }
		} catch (\Throwable $th) {
			Log::error("Getting error while sending notification when invite to user: ".$th);
		}
	}
    
    /**
     * getSiteMembers
     *
     * @param  mixed $request
     * @param  mixed $isCount
     * @return object
     */
    public function getSiteMembers($request, $roleId, $isMember = false, $isCount = false)
	{
		try {
            $members = User::select(['users.id','users.name','profile_pic','site_users.role_id',\DB::raw("DATE_FORMAT(site_users.created_at, '%M %d, %Y') as created_date"),\DB::raw("(select name from roles where id = site_users.role_id) as role_name")])
            ->join('site_users', function($join) use($request) { 
                $join->on('site_users.user_id', '=', 'users.id'); 
                $join->where('site_users.site_id', $request->site_id);
            });
            if(!empty($request->searchText)) {
                $search = $request->searchText;
                $members->where('users.name', 'like',$search . '%');
            }

            if(!empty($roleId) && $isMember) {
                $members->where('site_users.role_id', $roleId);
            } else if(!empty($roleId) && !$isMember) {
                $members->where('site_users.role_id','<>', $roleId);
            }
    
            if (!$isCount && $isMember) {
                $members->skip($request->skip)->take($request->take);
            }
            if($isCount) {
                $data = $members->count();
            } else {
                $data = $members->with('singlebusiness:id,name,user_id,business_slug,business_logo')->orderBy('site_users.id')->get();
            }
            return $data;
		} catch (\Throwable $th) {
			Log::error("Getting error while get site members: ".$th);
            return [];
		}
	}
    
    /**
     * getPendingJoinMembers
     *
     * @param  mixed $request
     * @param  mixed $isCount
     * @return void
     */
    public function getPendingJoinMembers($request, $isCount = false)
	{
		try {
            $members = User::select(['users.id','users.name','profile_pic',\DB::raw('TO_BASE64(site_requests.id) as request_id'),\DB::raw("DATE_FORMAT(site_requests.created_at, '%M %d, %Y') as created_date")])
            ->join('site_requests', function($join) use($request) { 
                $join->on('site_requests.user_id', '=', 'users.id'); 
                $join->where('site_requests.site_id', $request->site_id);
            })
            ->where('site_requests.status',0);
            if(!empty($request->searchText)) {
                $search = $request->searchText;
                $members->where('users.name', 'like',$search . '%');
            }
    
            if (!$isCount) {
                $members->skip($request->skip)->take($request->take);
            }
            if($isCount) {
                $data = $members->count();
            } else {
                $data = $members->with('singlebusiness:id,name,user_id,business_slug,business_logo')->orderBy('site_requests.id')->get();
            }
            return $data;
		} catch (\Throwable $th) {
			Log::error("Getting error while get site members: ".$th);
            return [];
		}
	}

    /**
     * getSuggestSiteMembers
     *
     * @param  mixed $request
     * @param  mixed $isCount
     * @return object
     */
    public function getSuggestSiteMembers($request)
	{
		try {
            $userId = auth()->id();
            $members = User::select(['users.id','name','profile_pic'])
            ->join('site_users', function($join) use($request) { 
                $join->on('site_users.user_id', '=', 'users.id'); 
                $join->where('site_users.site_id', $request->site_id);
            });
            if(!empty($request->searchText)) {
                $search = $request->searchText;
                $members->where('users.name', 'like',$search . '%');
            }
            $members->where('users.id', '<>',$userId);
            $data = $members->orderBy('name')->limit(10)->get();
            return $data;
		} catch (\Throwable $th) {
			Log::error("Getting error while getSuggestSiteMembers: ".$th);
            return [];
		}
	}
    
    /**
     * for get contact detail of site
     *
     * @param  mixed $siteId
     * @return void
     */
    public function getContact($siteId)
	{
		try {
            $cacheName = 'site_contact_'.$siteId;
            $seconds = 7200;
            
            $data = Cache::remember($cacheName, $seconds, function () use ($siteId) {
                $contatct = SiteContactDetail::select('site_contact_details.*','country','state','city','pincode')
                    ->leftJoin('locations', 'locations.id', '=', 'site_contact_details.location_id')
                    ->where('site_id',$siteId)->first();
                return $contatct;
            });
            if($data) {
                array_walk_recursive($data, function (&$item, $key) {
                    $item = null === $item ? '' : $item;
                });
            }
            return $data;
		} catch (\Throwable $th) {
			Log::error("Getting error while get contact detail: ".$th);
            return null;
		}
	}
    
    /**
     * for get member role
     *
     * @return object
     */
    public function memberRole()
	{
		try {
            $cacheName = 'site_member_role';
            $seconds = 7200;
            
            $data = Cache::remember($cacheName, $seconds, function () {
                $role = Role::where('name','like','member')->where('type','site')->first();
                return $role;
            });
            return $data;
		} catch (\Throwable $th) {
			Log::error("Getting error while get member role: ".$th);
            return null;
		}
	}
    
    /**
     * use this function to save member
     *
     * @param  mixed $query
     * @return void
     */
    public function saveMember($query)
    {
        $data = $query; 
        $memberRole = $this->memberRole();
        $data['role_id'] = ($memberRole) ? $memberRole->id : null;
        SiteUser::updateOrCreate($query,$data);
    }
    

}