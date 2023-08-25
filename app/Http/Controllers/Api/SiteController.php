<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Role;
use App\Site;
use App\SiteRequest;
use App\SiteUser;
use App\Traits\SiteTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class SiteController extends Controller
{
    use SiteTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        try {
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);

            $offset = Helpers::getOffset($pageNo,$limit);
            $request->request->add(['take' => $limit, 'skip' => $offset ]);
            
            $sites = Site::getAll($request);
            $siteCount = Site::getAll($request,true);
            $responseData['total'] = $siteCount;
            $perPageCnt = $pageNo * $request->take;
            if($siteCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }
            $responseData['status'] = 1;
            if(!$sites->isEmpty()) {
                $responseData['message'] = trans('apimessages.site_fetch_successfully');
                $responseData['data'] = $sites;
            } else {
                info('API sites no records found');
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = array();
            }
            return response()->json($responseData,200);
        } catch (\Throwable $th) {
            Log::error("Getting error while fetching sites: ".$th);
            $responseData['message'] = $th->getMessage();
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
		$validator = $this->validateSite($request);
        try {
			$user = $request->user();
			if ($validator->fails()) {
				Log::error("Site validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				
            } else {
                $id = $request->id;
                if(empty($id)) {
                    $siteCount = Site::where('created_by',$user->id)->count();
                    if($siteCount >= 10) {
                        $responseData['status'] = 0;
                        $responseData['message'] = trans('apimessages.max_limit_site_create');
                        return response()->json($responseData,200);
                    }
                }
				$data = $request->only(['name','description','logo','link','asset_type_id','visibility','is_enable_request','is_agree']);
				$data['created_by'] = $user->id;
                if($request->hasFile('logo')) {
                    $data['logo'] = $this->uploadImage($request->file('logo'));
                }           
				$site = Site::updateOrCreate(['id' => $id],$data);
                $this->uploadImages($site->id,$request);

                // for delete uploaded images
                if(!empty($request->deleted_images)) {
                    $this->deleteImages($request->deleted_images);
                }
                // for save social link / contact detail
                if($id > 0) {
                    $this->saveSocialLink($id,$request);
                    $this->saveContactDetail($id,$request);
                }

				$responseData['status'] = 1;
                $responseData['message'] = ($id > 0) ? trans('apimessages.site_updated') : trans('apimessages.site_created');
                $responseData['data'] = ['id' => $site->id];
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while creating site: ".$e);
            $responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $userId = auth()->id();
			$site = Site::with('createdBy:id,name,profile_pic','createdBy.singlebusiness:id,name,user_id,business_slug','images','assetType:id,name')
            ->withCount('members')
            ->addSelect(\DB::raw("(select DATE_FORMAT(created_at, '%M, %Y') from site_users where user_id = $userId and site_users.site_id = sites.id limit 1) as member_since"),\DB::raw("DATE_FORMAT(created_at, '%M %d, %Y') as created_date"),\DB::raw("(SELECT EXISTS(SELECT id FROM site_users WHERE site_users.user_id = ".$userId." and site_users.site_id = sites.id limit 1)) as is_joined_user"),\DB::raw("(SELECT EXISTS(SELECT id FROM site_requests WHERE site_requests.user_id = ".$userId." and site_requests.site_id = sites.id limit 1)) as is_joined_request"))
            ->find($request->id);
            if($site) {
                $socials = $site->socials->pluck('url','name')->toArray();
                $business = $site->createdBy->singlebusiness;
                $assetType = $site->assetType;
                $contacts = $this->getContact($request->id);
                unset($site['socials'],$site['createdBy']['singlebusiness']);
                $site['socials'] = !empty($socials) ? $socials : new stdClass;
                $site['contact'] = !empty($contacts) ? $contacts : new stdClass;
                $site['asset_type'] = !empty($assetType) ? $assetType : new stdClass;
                $site['createdBy']['singlebusiness'] = !empty($business) ? $business : new stdClass;
                array_walk_recursive($site, function (&$item, $key) {
                    $item = null === $item ? '' : $item;
                });
            }
			$responseData = ['status' => 1, 'message' => trans('apimessages.site_fetch_successfully'), 'data' => $site];
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while fetching site".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        //
    }
    
    /**
     * join, leave or send request for join site
     *
     * @param  mixed $request
     * @return void
     */
    public function joinOrLeaveSite(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $validator = Validator::make($request->all(),[                    
                'site_id' => 'required|exists:sites,id',
                'user_id' => 'required_if:type,=,invite|exists:users,id'
            ]);
            if ($validator->fails()) {
				Log::error("joinOrLeaveSite validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $type = $request->input('type','join');
			$siteId = $request->site_id;
            $data['site_id'] = $siteId;
            $data['user_id'] = auth()->id();
            if($type == 'join') {
                $site = Site::find($siteId);
                // private
                if($site->getRawOriginal('visibility')) {
                    $data['sender_by'] = $data['user_id'];                
                    SiteRequest::updateOrCreate($data,$data);
                    $responseData = ['status' => 1, 'message' => trans('apimessages.site_joined_request')];
                } else {
                    $this->saveMember($data);
                    $responseData = ['status' => 1, 'message' => trans('apimessages.site_joined')];
                }          
            } else if($type == 'invite') {
                $data['sender_by'] = $data['user_id']; 
                $data['user_id'] = $request->user_id; 
                SiteRequest::updateOrCreate($data,$data);
                $this->sendInvitedNotification($siteId, $data['user_id']);
                $responseData = ['status' => 1, 'message' => trans('apimessages.site_joined_request')];
            } else if($type == 'cancel') {
                if($request->by == 'admin') {
                    $data['user_id'] = $request->user_id; 
                }
                SiteRequest::where($data)->delete();
                $responseData = ['status' => 1, 'message' => trans('apimessages.cancelled_joined_request')];
            } else {
                SiteUser::where($data)->delete();
                $responseData = ['status' => 1, 'message' => trans('apimessages.site_exit')];
            }
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while joinOrLeaveSite site".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * accept site invitation for join
     *
     * @param  mixed $request
     * @return void
     */
    public function acceptInvitation(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            if($request->accept_by == 'admin') {
                $userId = $request->user_id;
                $isAdmin = true;
            } else {
                $userId = auth()->id();
                $isAdmin = false;
            }
            $id = base64_decode($request->id);
            $validator = Validator::make(['id' => $id],[                    
                'id' => 'required|exists:site_requests,id,status,0,user_id,'.$userId,
            ]);
            if ($validator->fails()) {
				Log::error("acceptInvitation validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $invitation = SiteRequest::find($id);
            $data = [
                'site_id' => $invitation->site_id,
                'user_id' => $invitation->user_id,
            ];
            $invitation->status = 1;
            $invitation->joined_at = now();
            $invitation->save();
            $this->saveMember($data);
            $this->sendInvtAcceptedNotification($invitation->site_id,$userId,$isAdmin);
            $responseData = ['status' => 1, 'message' => trans('apimessages.site_joined')];
            
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while acceptInvitation site".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * getMembers
     *
     * @param  mixed $request
     * @return void
     */
    public function getMembers(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $validator = Validator::make($request->all(),[                    
                'site_id' => 'required|exists:sites,id',
            ]);
            if ($validator->fails()) {
				Log::error("get member validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);
            $withMember = $request->input('with_member',true);

            $offset = Helpers::getOffset($pageNo,$limit);
            $request->request->add(['take' => $limit, 'skip' => $offset ]);
            
            $memberRole = $this->memberRole();
            $roleId = ($memberRole) ? $memberRole->id : null;
            $creator = $notCreator = $managements = $members = [];
            $managementCount = $memberCount = 0;
            if($pageNo == 1) {
                $managements = $this->getSiteMembers($request,$roleId);

                if($managements->isNotEmpty()) {
                    $managementCount = $managements->count();
                    list($creator, $notCreator) = $managements->partition(function ($item) {
                        return $item->role_name == 'Creator';
                    });
                }

            }
            if($withMember) {
                $members = $this->getSiteMembers($request,$roleId,true);
                $memberCount = $this->getSiteMembers($request,$roleId,true,true);
            }
            $responseData['total'] = $memberCount;
            $data['totalMembers'] = $responseData['total'] + $managementCount;
            $data['totalPendingRequest'] = $this->getPendingJoinMembers($request,true);
            $perPageCnt = $pageNo * $request->take;
            if($memberCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }
            $data['creator'] = $creator;
            $data['managements'] = $notCreator;
            $data['members'] = $members;
            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.site_member_fetch_successfully');
            $responseData['data'] = $data;
            return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while get site members".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    /**
     * pendingJoinMembers
     *
     * @param  mixed $request
     * @return array
     */
    public function pendingJoinMembers(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $validator = Validator::make($request->all(),[                    
                'site_id' => 'required',
            ]);
            if ($validator->fails()) {
				Log::error("get pendingJoinMembers validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);

            $offset = Helpers::getOffset($pageNo,$limit);
            $request->request->add(['take' => $limit, 'skip' => $offset ]);
            
            $members = $this->getPendingJoinMembers($request);
            $memberCount = $this->getPendingJoinMembers($request,true);
            $responseData['total'] = $memberCount;
            $perPageCnt = $pageNo * $request->take;
            if($memberCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }
            
            $responseData['status'] = 1;
            if($memberCount > 0) {
                $responseData['message'] = trans('apimessages.site_member_fetch_successfully');
                $responseData['data'] = $members;
            } else {
                info('API site members no records found');
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = $members;
            }
            return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while get pendingJoinMembers ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * suggestSiteMembers
     *
     * @param  mixed $request
     * @return array
     */
    public function suggestSiteMembers(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $validator = Validator::make($request->all(),[                    
                'site_id' => 'required',
            ]);
            if ($validator->fails()) {
				Log::error("get suggestSiteMembers validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $members = $this->getSuggestSiteMembers($request);
            $responseData['status'] = 1;
            if(count($members) > 0) {
                $responseData['message'] = trans('apimessages.site_member_fetch_successfully');
                $responseData['data'] = $members;
            } else {
                info('API site members no records found');
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = $members;
            }
            return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while get suggestSiteMembers members".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * get site roles
     *
     * @param  mixed $request
     * @return void
     */
    public function siteRoles(Request $request)
    {
		try {
            $members = getRoles(true,'site');
            $responseData['status'] = 1;
            if(count($members) > 0) {
                $responseData['message'] = trans('apimessages.site_role_fetch_successfully');
                $responseData['data'] = $members;
            } else {
                info('API site members no records found');
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] = $members;
            }
            return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while get siteRoles members".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * assignRoleSiteMember
     *
     * @param  mixed $request
     * @return void
     */
    public function assignRoleSiteMember(Request $request)
    {
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            $validator = Validator::make($request->all(),[                    
                'site_id' => 'required',
                'user_id' => 'required',
                'role_id' => 'required',
            ]);
            if ($validator->fails()) {
				Log::error("get assignRoleSiteMember validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all()[0];
				return response()->json($responseData,200);
            }
            $query = $request->only(['site_id','user_id']);
            $data = $query;
            $data['role_id'] = $request->role_id;
            SiteUser::where($query)->update($data);
            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.site_member_assign_role');
            return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while get assignRoleSiteMember members".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }
    
    /**
     * for get auto suggestion
     *
     * @param  mixed $request
     * @return void
     */
    public function siteAutoSuggestion(Request $request)
    {
        $sites = Site::select('id','name')->where('status',1)->where('is_approved',1)->where('name','like',$request->searchText."%")->limit(5)->get();
        if($sites->isEmpty()) {
            $responseData['status'] = 0;
            $responseData['message'] = trans('apimessages.norecordsfound');
            $responseData['data'] = $sites;
        } else {
            $responseData['status'] = 1;
            $responseData['message'] =  trans('apimessages.site_fetch_successfully');
            $responseData['data'] = collect($sites)->unique(function($item){
                return strtoupper($item->name); 
            })->values()->toArray();
        }        
        return response()->json($responseData, 200);
    }
}
