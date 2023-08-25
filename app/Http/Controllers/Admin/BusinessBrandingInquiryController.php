<?php

namespace App\Http\Controllers\Admin;

use App\BusinessBrandingInquiry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessBrandingInquiryController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listAdvertise'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addAdvertise')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editAdvertise'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deleteAdvertise'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inquiries = BusinessBrandingInquiry::with('business');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $inquiries->whereHas('business', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $inquiries = $inquiries->get();
        return view('Admin.BusinessBrandingInquiry.index',compact('inquiries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.BusinessBrandingInquiry.create');
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
			'business_name' => 'required',
			'name' => 'required',
			'mobile_number' => 'required|numeric|digits:10',
			'city' => 'required',
		]);

        try {
			$data = $request->except(['_token']);
			BusinessBrandingInquiry::create($data);
			return redirect()->route('businessBrandingInquiry.index')->with("success","Business branding inquiry created successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while creating business branding inquiry: ".$e);
			return redirect()->route('businessBrandingInquiry.index')->with("error",$e->getMessage());
		}
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BusinessBrandingInquiry  $businessBrandingInquiry
     * @return \Illuminate\Http\Response
     */
    public function edit(BusinessBrandingInquiry $businessBrandingInquiry)
    {
        return view('Admin.BusinessBrandingInquiry.edit',compact('businessBrandingInquiry'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BusinessBrandingInquiry  $businessBrandingInquiry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BusinessBrandingInquiry $businessBrandingInquiry)
    {
        $request->validate([
			'business_name' => 'required',
			'name' => 'required',
			'mobile_number' => 'required|numeric|digits:10',
			'city' => 'required',
		]);

        try {
			$data = $request->except(['_token']);
			
			$businessBrandingInquiry->update($data);
			return redirect()->route('businessBrandingInquiry.index')->with("success","Business branding inquiry updated successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while updating business branding inquiry: ".$e);
			return redirect()->route('businessBrandingInquiry.index')->with("error",$e->getMessage());
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BusinessBrandingInquiry  $businessBrandingInquiry
     * @return \Illuminate\Http\Response
     */
    public function destroy(BusinessBrandingInquiry $businessBrandingInquiry)
    {
        try {
			$businessBrandingInquiry->delete();
			return redirect()->route('businessBrandingInquiry.index')->with("success","Business branding inquiry deleted successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while deleting business branding inquiry: ".$e);
			return redirect()->route('businessBrandingInquiry.index')->with("error",$e->getMessage());
		}
    }
}
