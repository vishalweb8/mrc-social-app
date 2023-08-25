<?php

namespace App\Http\Controllers\Admin;

use App\BusinessBranding;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class BusinessBrandingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $brandings = BusinessBranding::with('business')->get();
        return view('Admin.BusinessBranding.index',compact('brandings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.BusinessBranding.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$request->validate([
			'business_id' => 'required',
			'start_date' => 'required',
			'end_date' => 'required',
		],[
			'business_id' => 'The business field is required.',
		]);

        try {
			$data = $request->except(['_token','image']);
			$data['image'] = $this->uploadImage($request);
			BusinessBranding::create($data);
			return redirect()->route('businessBranding.index')->with("success","Business branding created successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while creating business branding: ".$e);
			return redirect()->route('businessBranding.index')->with("error",$e->getMessage());
		}
    }    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BusinessBranding  $businessBranding
     * @return \Illuminate\Http\Response
     */
    public function edit(BusinessBranding $businessBranding)
    {
        return view('Admin.BusinessBranding.edit',compact('businessBranding'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BusinessBranding  $businessBranding
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BusinessBranding $businessBranding)
    {
        $request->validate([
			'business_id' => 'required',
			'start_date' => 'required',
			'end_date' => 'required',
		],[
			'business_id' => 'The business field is required.',
		]);

        try {
			$data = $request->except(['_token','image']);
			if($request->file('image')) {
				$data['image'] = $this->uploadImage($request);
			}
			$businessBranding->update($data);
			return redirect()->route('businessBranding.index')->with("success","Business branding updated successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while updating business branding: ".$e);
			return redirect()->route('businessBranding.index')->with("error",$e->getMessage());
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BusinessBranding  $businessBranding
     * @return \Illuminate\Http\Response
     */
    public function destroy(BusinessBranding $businessBranding)
    {
		try {
			$businessBranding->delete();
			return redirect()->route('businessBranding.index')->with("success","Business branding deleted successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while deleting business branding: ".$e);
			return redirect()->route('businessBranding.index')->with("error",$e->getMessage());
		}
        
    }
	
	/**
	 * upload business branding image on aws storage
	 *
	 * @param  mixed $request
	 * @return void
	 */
	public function uploadImage($request)
	{
		try {
			if ($request->file('image')) 
			{  
				$image = $request->file('image'); 
				$fileName = 'business_branding_' . uniqid() . '.' . $image->getClientOriginalExtension();
				$path = config('constant.BUSINESS_BRANDING_IMAGE_PATH');
				//Uploading on AWS
				$originalImage = Helpers::addFileToStorage($fileName, $path, $image, "s3");

				return $path.$originalImage;
			} else {
				info("Business branding image is empty");
				return null;
			}
		} catch (\Throwable $th) {
			Log::error("Getting error while uploading business branding image: ".$th);
			return null;
		}
	}
}
