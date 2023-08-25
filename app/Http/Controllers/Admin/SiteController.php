<?php

namespace App\Http\Controllers\Admin;

use App\AssetType;
use App\Http\Controllers\Controller;
use App\Site;
use App\SiteUser;
use App\Traits\SiteTrait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SiteController extends Controller
{
    use SiteTrait;
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listSite'), ['only' => ['index']]);
        $this->middleware('permission:'.config('perm.viewSite'), ['only' => ['show']]);
        $this->middleware('permission:'.config('perm.addSite'), ['only' => ['create']]);
        $this->middleware('permission:'.config('perm.editSite'), ['only' => ['edit']]);
        $this->middleware('permission:'.config('perm.deleteSite'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sites = Site::getAll($request,false,true);
            $user = auth()->user();           
            return DataTables::of($sites)
                ->editColumn('created_by', function($site) {
                    $name = viewUserUrl($site->createdBy);                               
                    return $name;
                })
                ->editColumn('asset_type', function($site) {
                    $name  = '';
                    if(!empty($site->assetType)) {
                        $name = $site->assetType->name;
                    }                                     
                    return $name;
                })
                ->addColumn('action', function($site) use($user) {
                    $class = 'approve-reject-site';
                    $viewBtn = $editBtn = $approveBtn = $rejectBtn = $deleteBtn = '';
                    $attributes = [
                        "data-url" => route('site.approveReject',$site->id)
                    ];
                    if($user->can(config('perm.approveSite')) && ($site->is_approved == 'Pending')) {
                        $attributes['data-status'] = 1;
                        $approveBtn = getApproveBtn("javascript:;",$class, $attributes);
                    }
                    if($user->can(config('perm.rejectSite')) && ($site->is_approved == 'Pending')) {
                        $attributes['data-status'] = 2;
                        $rejectBtn = getRejectBtn("javascript:;",$class, $attributes);
                    }
                    if($user->can(config('perm.viewSite'))) {
                        $viewUrl = route('site.show',$site->id);
                        $viewBtn = getViewBtn($viewUrl);
                    }
                    if($user->can(config('perm.editSite'))) {
                        $editUrl = route('site.edit',$site->id);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteSite'))) {
                        $class = 'delete-site';
                        $attributes = [
                            "data-url" => route('site.destroy',$site->id)
                        ];
                        $deleteBtn = getDeleteBtn("javascript:;",$class, $attributes);
                    }                
                    return $approveBtn.$rejectBtn.$viewBtn.$editBtn.$deleteBtn;
                })
                ->rawColumns(['created_by','action'])
                ->make(true);
        }
        $assetTypes = AssetType::whereHas('parent', function ($query) {
            $query->where('name','LIKE','Site');
        })->get();
        return view('Admin.Site.index',compact('assetTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $site = [];
        $contacts = null;
        return view('Admin.Site.edit',compact('site','contacts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateSite($request);
		$validator->validate();
        try {
			$id = $request->id;
            if(empty($id)) {
                $siteCount = Site::where('created_by',$request->created_by)->count();
                if($siteCount >= 10) {
                    $msg = trans('apimessages.max_limit_site_create');
                    return redirect()->back()->with('error',$msg);
                }
            }
            $data = $request->only(['name','description','logo','link','asset_type_id','visibility','created_by','status']);
            $data["is_enable_request"] = $request->input('is_enable_request',false);
            $data["is_approved"] = 1;
            $data["approved_at"] = now();
            $data["approved_by"] = auth()->id();
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
            $this->saveSocialLink($site->id,$request);
            $this->saveContactDetail($site->id,$request);

            $msg = ($id > 0) ? trans('apimessages.site_updated') : trans('apimessages.site_created');
            $responseData['data'] = ['id' => $site->id];
            return redirect()->route('site.index')->with('success',$msg);
		} catch (\Exception $e) {
			Log::error("Getting error while creating post: ".$e);
            return redirect()->route('site.index')->with('error',$e->getMessage());
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        return view('Admin.Site.show',compact('site'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function edit(Site $site)
    {
        $contacts = $this->getContact($site->id);
        return view('Admin.Site.edit',compact('site','contacts'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        try {
            $site->delete();
            return redirect()->route('site.index')->with("success","Site deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting site: ".$th);
            return redirect()->route('site.index')->with("error",$th->getMessage());
        }
    }
    
    /**
     * for use approved or reject site
     *
     * @param  mixed $request
     * @param  mixed $site
     * @return void
     */
    public function approveReject(Request $request, Site $site)
    {
        try {
            $site->is_approved = $request->is_approved;
            $site->approved_at = now();
            $site->approved_by = auth()->id();
            $site->save();
            $message = ($request->is_approved == 1) ? trans('labels.site_approved') : trans('labels.site_rejected') ;
            $response =['status'=> 1, 'message' => $message];
            return response()->json($response,200);
        } catch (\Throwable $th) {
            Log::error("Getting error while approve/reject site: ".$th);
            $response =['status'=> 0, 'message' =>$th->getMessage()];
            return response()->json($response,200);
        }
    }
    
    /**
     * getMembers
     *
     * @param  mixed $var
     * @return void
     */
    public function getMembers(Request $request)
    {
        if ($request->ajax()) {
            $members = User::select(['users.id','users.name','profile_pic','site_users.role_id',\DB::raw("DATE_FORMAT(site_users.created_at, '%M %d, %Y') as created_date"),\DB::raw("(select name from roles where id = site_users.role_id) as role_name")])
            ->join('site_users', function($join) use($request) { 
                $join->on('site_users.user_id', '=', 'users.id'); 
                $join->where('site_users.site_id', $request->site_id);
            });
            $user = auth()->user();           
            return DataTables::of($members)
                ->editColumn('name', function($member) {
                    $name = viewUserUrl($member);                               
                    return $name;
                })
                ->addColumn('action', function($member) use($user,$request) {
                    $class = 'delete-site-member';
                    $deleteBtn = '';
                    if($user->can(config('perm.deleteMemberSite')) && strtolower($member->role_name) != 'creator') {
                        $attributes = [
                            "data-url" => route('site.deleteMembers',[$request->site_id,$member->id])
                        ];
                        $deleteBtn = getDeleteBtn("javascript:;",$class, $attributes);
                    }                
                    return $deleteBtn;
                })
                ->rawColumns(['name','action'])
                ->make(true);
        }
    }
    
    /**
     * for delete site members
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMembers(Request $request)
    {
        try {
            SiteUser::where('site_id',$request->site_id)->where('user_id',$request->user_id)->delete();
            $response =['status'=> 1, 'message' => "Member successfully deleted"];
            return response()->json($response,200);
        } catch (\Throwable $th) {
            Log::error("Getting error while approve/reject site: ".$th);
            $response =['status'=> 0, 'message' =>$th->getMessage()];
            return response()->json($response,200);
        }
    }
    
    /**
     * autoComplete
     *
     * @param  mixed $request
     * @return void
     */
    public function autoComplete(Request $request)
    {
        $query = Site::orderBy('name')->limit(10);
        if(!empty($request->q)) {
            $query->where('name', 'LIKE', $request->q."%");
        }
        $sites = $query->orderBy('name')
                            ->get(['id', \DB::raw('name AS text')]);
        return response()->json($sites, 200);
    }
}
