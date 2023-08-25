<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\OnlineStore;
use Illuminate\Http\Request;

class OnlineStoreController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listOnlineStore'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addOnlineStore')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editOnlineStore'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deleteOnlineStore'),   ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stores = OnlineStore::latest()->get();
		return view('Admin.OnlineStore.index', compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.OnlineStore.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateOnlineStore($request);
		$validator->validate();
        try {
			$data = $request->only('name');
            $data['logo'] = $this->uploadImage($request);
            OnlineStore::create($data);
            return redirect()->route('onlineStore.index')->with('success',"Online store created successfully");
		} catch (\Exception $e) {
			\Log::error("Getting error while online store: ".$e);
            return redirect()->route('onlineStore.index')->with('error',$e->getMessage());
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OnlineStore  $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function show(OnlineStore $onlineStore)
    {
        return view('Admin.OnlineStore.edit', compact('onlineStore'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OnlineStore  $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function edit(OnlineStore $onlineStore)
    {
        return view('Admin.OnlineStore.edit', compact('onlineStore'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OnlineStore  $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OnlineStore $onlineStore)
    {
        $request->validate([                    
            'name' => 'required'
        ]);
        try {
            $onlineStore->name = $request->name;
            $onlineStore->status = $request->status;
            if($request->file('logo')) {
                $onlineStore->logo = $this->uploadImage($request);
            }
            $onlineStore->save();
            return redirect()->route('onlineStore.index')->with('success',"Online store updated successfully");
		} catch (\Exception $e) {
			\Log::error("Getting error while online update: ".$e);
            return redirect()->route('onlineStore.index')->with('error',$e->getMessage());
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OnlineStore  $onlineStore
     * @return \Illuminate\Http\Response
     */
    public function destroy(OnlineStore $onlineStore)
    {
        try {
            $onlineStore->delete();
            return redirect()->route('onlineStore.index')->with("success","Online store deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting online store: ".$e);
            return redirect()->route('onlineStore.index')->with("error",$e->getMessage());
        }
    }

    public function validateOnlineStore($request)
    {
        $validator = \Validator::make($request->all(), 
			[                    
				'name' => 'required',
                'logo' => 'required'
            ]
		);

		return $validator;
    }

    public function uploadImage($request)
	{
        $url = null;
		try {
			if ($request->file('logo')) 
			{  
				$logo = $request->file('logo');                
                $fileName = 'online_store_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $path = config('constant.ONLINE_STORE_IMAGE_PATH');
                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $path, $logo, "s3");
                $url = $path.$originalImage;
			}
		} catch (\Throwable $th) {
			\Log::error("Getting error while uploading post image: ".$th);
		}
        return $url;
	}
}
