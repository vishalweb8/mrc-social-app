<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\PublicPost;
use App\PublicPostComment;
use App\PublicPostLike;
use App\PublicPostView;
use App\Traits\PublicPostTrait;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use DB;
class PublicPostController extends Controller
{
	use PublicPostTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$validator = \Validator::make($request->all(), 
				[                    
					'type' => 'required|in:admin,business_user',
				]);
			if ($validator->fails()) {
				Log::error("get all posts validation failed.");
				$responseData['status'] = 0;
				$responseData['message'] = $validator->messages()->all()[0];
				
			} else {
                $time1 = intval(microtime(true)*1000);
                \Log::info("Start Post Query Execution :-");
				$filters = $request->all();
				$headerData = $request->header('Platform');
				$pageNo = $request->input('page',1);
				$filters['post_type'] = $request->input('post_type',1);

				if (!empty($headerData) && $headerData == config('constant.WEBSITE_PLATFORM'))
				{	
                    $limit = $request->input('limit',config('constant.WEBSITE_RECORD_PER_PAGE'));			    
                    $offset = Helpers::getWebOffset($pageNo,$limit);
                    info('page:- '.$pageNo);
                    info('limit:- '.$limit);
                    info('offset:- '.$offset);
                    $filters['take'] = $limit;
                    $filters['skip'] = $offset;
				}
				else
				{
                    $limit = $request->input('limit',config('constant.API_RECORD_PER_PAGE'));
				    $offset = Helpers::getOffset($pageNo,$limit);
			        $filters['take'] = $limit;
			        $filters['skip'] = $offset;
				}
                $time2 = intval(microtime(true)*1000);
                \Log::info("Post Query Execution time1:=== " . ($time2 - $time1).'ms');

				$posts = PublicPost::getAll($filters);
                $time3 = intval(microtime(true)*1000);
                \Log::info("Post Query Execution after get all post time:=== " . ($time3 - $time2).'ms');
				$postCount = PublicPost::getAll($filters,true);
                $time4 = intval(microtime(true)*1000);
                \Log::info("Post Query Execution after get all post count time:=== " . ($time4 - $time3).'ms');
                $responseData['total'] = $postCount;
				$perPageCnt = $pageNo * $filters['take'];
                if($postCount > $perPageCnt)
                {
                    $responseData['loadMore'] = 1;
                } else {
					$responseData['loadMore'] = 0;
				}
				$responseData['status'] = 1;
				if(!$posts->isEmpty()) {
                    $userId = 0;
                    if(\Auth::guard('api')->check()) {
                        $userId = \Auth::guard('api')->id();
                    }
                    $viewsData=[];
                    foreach ($posts as $key => $post) {
                        //$post->views()->sync([$userId],false);
                        $post->created_at_diff = str_replace(' ago','',$post->created_at->diffForHumans(null,null,true));
                        $viewsData[$key] = $post->id; 
                        // $viewsData[$key]['post_id'] = $post->id; 
                        // $viewsData[$key]['user_id'] = $userId; 
                    }
                    if(!empty($viewsData)) {
                        PublicPost::whereIn('id', $viewsData)->update(['views_count' =>\DB::raw('views_count+1')]);
                    }
                    //PublicPostView::insert($viewsData);
                    $time5 = intval(microtime(true)*1000);
                    \Log::info("Post Query Execution after update all view detail time:=== " . ($time5 - $time4).'ms');
					$responseData['message'] = trans('apimessages.posts_getting');
					$responseData['data'] = $posts;
				} else {
					info('API getAllPosts no records found');
	                $responseData['message'] = trans('apimessages.norecordsfound');
	                $responseData['data'] = array();
				}
			}
			return response()->json($responseData, 200);
		} catch (\Exception $e) {
			Log::error("Getting error while creating post: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		$validator = $this->validatePost($request);
        $video =  $request->file('video'); 

        try {
			$user = JWTAuth::parseToken()->authenticate();
			if ($validator->fails()) {
				Log::error("Public post validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				
            } else {
				$data = $request->except(['images','delete_images']);
				$data['user_id'] = $user->id;
				$data['type'] = "business_user";
                $data = array_merge($data,$this->getLatLngByCity($request->city));
				$post = PublicPost::create($data);

                $this->uploadImage($post->id,$request);
                if($request->hasFile('video')){  
                  $this->uploadVideo($post->id,$request);
                }
                // for create post for group
                if($request->post_type == 2) {
                    $siteData['site_id'] = $request->site_id;
                    $siteData['content_id'] = $post->id;
                    $this->createSiteContent($siteData);
                }
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.posts_created');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while creating post: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * show the posts detail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
			$post = PublicPost::with('images:id,post_id,url','videos:id,post_id,url')->find($request->post_id);
			$responseData = ['status' => 1, 'message' => trans('apimessages.posts_getting'), 'data' => $post];
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while fetching public post: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        $validator = $this->validatePost($request);
		$validator->sometimes('post_id', 'required|numeric', function () {
			return true;
		});
        
        try {
			if ($validator->fails()) {
				Log::error("Public post update validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				
            } else {
				$data = $request->except(['post_id','images','delete_images','delete_image_ids','post_type','site_id','video']);
                $data = array_merge($data,$this->getLatLngByCity($request->city));
				PublicPost::whereId($request->post_id)->update($data);
                if($request->delete_images == 'true') {
                    $this->deleteImage($request->post_id);
                }
                if($request->header('Platform') == 'web') {
                    if(!empty($request->delete_image_ids)) {
                        $imageIds = json_decode($request->delete_image_ids);
                        $this->deleteImageById($imageIds);
                    }
                    if($request->file('images')) {
                        $this->uploadImage($request->post_id,$request);
                    }
                } else {
                    if($request->file('images')) {
                        $this->deleteImage($request->post_id);
                        $this->uploadImage($request->post_id,$request);
                    }
                    if($request->hasFile('video')){ 
                        $this->deleteVideo($request->post_id);
                        $this->uploadVideo($request->post_id,$request);
                      }
                }
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.posts_updated');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while updating post: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     *  @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
			$validator = \Validator::make($request->all(), 
				[                    
					'post_id' => 'required',
				]);
			if ($validator->fails()) {
				Log::error("Public post delete validation failed.");
				$responseData['status'] = 0;
				$responseData['message'] = $validator->messages()->all()[0];
				
			} else {
				PublicPost::whereId($request->post_id)->delete();
				$responseData = ['status' => 1, 'message' => trans('apimessages.posts_deleted')];
			}
			return response()->json($responseData,200);
        } catch (\Exception $e) {
            Log::error("Getting error while deleting public post: ".$e);
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
			return response()->json($responseData,400);
        }

    }

	public function getCategoryAndModKeywords()
	{		
		try {
			$categories = $keywords = [];
			$category = $this->getPostCategory();
			$keyword = $this->getModeKeywords();
			
			if(!empty($category)) {
				$categories = explode(',',$category);
			}

			if(!empty($keyword)) {
				$keywords = explode(',',$keyword);
			}

			$data['categories'] = $categories;
			$data['keywords'] = $keywords;

			$data = [
				'categories' => $categories,
				'keywords' => $keywords,
			];

			$responseData = ['status' => 1, 'message' => trans('apimessages.posts_category_getting'), 'data' => $data];
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while fetch category and moderator keywords: ".$e);
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
			return response()->json($responseData,400);
		}
	}
    
    /**
     * like
     *
     * @param  mixed $request
     * @return void
     */
    public function like(Request $request)
    {
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		$validator = $this->validateLike($request);
        try {
			$user = JWTAuth::parseToken()->authenticate();
			if ($validator->fails()) {
				Log::error("Public post like validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				
            } else {
                $isShare = $request->input('share',false);
                if($isShare) {
                    PublicPost::whereId($request->post_id)->increment('share_count');
                    $responseData['message'] = trans('apimessages.posts_shared');
                    $this->sendLikeShareNotification($request,false);
                } else {
                    $data['user_id'] = $user->id;
                    $data['post_id'] = $request->post_id;
                    $like = PublicPostLike::where($data)->first();
                    if(!empty($like)) {
                        $like->delete();
                        $responseData['message'] = trans('apimessages.posts_unliked');
                    } else {
                        $data['latitude'] = $request->latitude;
                        $data['longitude'] = $request->longitude;
                        $data['ip_address'] = $request->ip();
                        PublicPostLike::updateOrcreate($data);
                        $responseData['message'] = trans('apimessages.posts_liked');
                        $this->sendLikeShareNotification($request);
                    }
                }
				$responseData['status'] = 1;
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while like post: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    public function comment(Request $request)
    {
		$responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		$validator = $this->validateLike($request);
        try {
			$user = JWTAuth::parseToken()->authenticate();
			if ($validator->fails()) {
				Log::error("Public post comment validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				
            } else { 
                $postComment = new PublicPostComment();
                $postComment->user_id = $user->id;
                $postComment->post_id = $request->post_id;
                $postComment->comment = $request->comment;
                $postComment->save();
                $responseData['message'] = trans('apimessages.posts_commented');
                $responseData['status'] = 1;
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while comment post: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

     /**
     * Display liked Users of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLikedUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try { 
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);
            $offset = Helpers::getOffset($pageNo,$limit); 
            
            $filters=[];
            $filters['take'] = $limit; 
            $filters['skip'] = $offset;
            // Filters
            if (isset($request->post_id) && $request->post_id != '') {
                $filters['post_id'] = $request->post_id; 
            } 

            $users =  PublicPostLike::where('post_id',$request->post_id)
                ->with(["user:id,name,profile_pic", "user.singlebusiness:id,user_id"])
            ->get();

            
            foreach ($users as $key => $usersdata)
                {
                    
                    $ListArray['name'] = $usersdata->user->name;
                    $ListArray['image'] = $usersdata->user->profile_url;
                    $ListArray['business_id'] = $usersdata->singlebusiness->id;
                    $ListArray['user_profile_id'] = $usersdata->user->id;
                    $ListArray['Timestamp'] = $usersdata->updated_at;
                    $responseData[] = $ListArray;

                }    
                
                print_r($ListArray);die;


             return($users); die;
            

            // $jobVacancy = $this->jobObj->getAll($filters);  
            
            $responseData['total'] = $jobCount;
            $perPageCnt = $pageNo * $limit;
            if($jobCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }

            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.job_getting');
            $responseData['data'] = $responseData;
 
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while getting job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
     
    }
}
