<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PublicPost;
use App\Traits\PublicPostTrait;
use Illuminate\Http\Request;
use Log;
use Yajra\DataTables\Facades\DataTables;

class PublicPostController extends Controller
{
	use PublicPostTrait;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listPost'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addPost')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editPost'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deletePost'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()) {

            $posts = PublicPost::select('public_posts.*')->has('user')->with('user:id,name');
            $user = auth()->user();
            if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
                $posts->whereHas('user.singlebusiness', function ($query) use ($user) {
                    $query->whereRaw($user->sql_query);
                });
            }
            if(!empty($request->site_id))
            {
                $posts->join('site_contents','site_contents.content_id','=','public_posts.id')->where('site_contents.site_id',$request->site_id);
            }
            return DataTables::of($posts)
                ->addColumn('user_name', function($post) {
                    $name = viewUserUrl($post->user);                               
                    return $name;
                })
                ->editColumn('type', function($post) {
                    $type = '';
                    if($post->type == 'business_user') {
                        $type = 'Business User';
                    } else {
                        $type = $post->type;
                    }
                    return $type;
                })
                ->editColumn('created_at', function($post) {                    
                    return $post->created_at->format("Y-m-d H:i");
                })
                ->addColumn('action', function($post) use($user) {
                    $class = 'delete-post';
                    $editBtn = $deleteBtn = '';
                    if($user->can(config('perm.editPost'))) {
                        $editUrl = route('publicPost.edit',$post->id);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deletePost'))) {
                        $attributes = [
                            "data-url" => route('publicPost.destroy',$post->id)
                        ];
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                
                    return $editBtn.$deleteBtn;
                })
                ->rawColumns(['user_name','action'])
                ->make(true);
        }
		return view('Admin.PublicPost.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		$categories = $this->getPostCategory();
		$keywords = $this->getModeKeywords();
        return view('Admin.PublicPost.create',compact('categories','keywords'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$validator = $this->validatePost($request);
		$validator->validate();
        try {
			$data = $request->all();
			$data['type'] = 'admin';
			$data['user_id'] = auth()->id();
            PublicPost::create($data);
            return redirect()->route('publicPost.index')->with('success',"Public Post created successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while creating post: ".$e);
            return redirect()->route('publicPost.index')->with('error',$e->getMessage());
		}
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PublicPost  $publicPost
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicPost $publicPost)
    {
        $categories = $this->getPostCategory();
		$keywords = $this->getModeKeywords();
        return view('Admin.PublicPost.edit',compact('publicPost','categories','keywords'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PublicPost  $publicPost
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicPost $publicPost)
    {
        $validator = $this->validatePost($request);
        $validator->validate();
        try {
            if(!empty($request->delete_image_ids)) {
                $imageIds = $request->delete_image_ids;
                $this->deleteImageById($imageIds);
            }
            if($request->file('images')) {
                $this->uploadImage($publicPost->id,$request);
            }
            $publicPost->update($request->all());
            return redirect()->route('publicPost.index')->with('success',"Public Post updated successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while updating post: ".$e);
            return redirect()->route('publicPost.index')->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PublicPost  $publicPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicPost $publicPost)
    {
        try {
            $publicPost->delete();
            return redirect()->route('publicPost.index')->with("success","Public post deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting public post: ".$e);
            return redirect()->route('publicPost.index')->with("error",$e->getMessage());
        }

    }
}
