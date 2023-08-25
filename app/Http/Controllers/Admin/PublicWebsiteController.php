<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Http\Controllers\Controller;
use App\PublicInquiry;
use App\PublicReview;
use App\PublicWebsite;
use App\PublicWebsitePlans;
use App\PublicWebsiteTemplets;
use Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicWebsiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $publicwebsite = PublicWebsite::with('businessName');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $publicwebsite->whereHas('businessName', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $publicwebsite = $publicwebsite->get();
        return view('Admin.ListPublicWebsite',compact('publicwebsite'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $id = Crypt::decrypt($id);
        $business = Business::find($id);
        $PublicWebsiTetemplets = PublicWebsiteTemplets::where('status',1)->get();
        $PublicWebsitePlans = PublicWebsitePlans::where('status',1)->get();
        return view('Admin.AddPublicWebsite',compact('business','id','PublicWebsiTetemplets','PublicWebsitePlans'));
    }

    public function status(Request $request){
        // return $request->all();
        if ($request->value == 0) {
          $value = 0;
          $message = "Public Website Pending";
        } elseif($request->value == 1) {
          $value = 1;
          $message = "Public Website Submitted";
        }elseif($request->value == 2) {
          $value = 2;
          $message = "Public Website Live";
        }elseif($request->value == 3) {
          $value = 3;
          $message = "Public Website Paused";
        }elseif($request->value == 4) {
          $value = 4;
          $message = "Public Website Removed";
        }

        $PublicWebsite = PublicWebsite::findOrFail($request->id);
        $PublicWebsite->status = $value;
        $PublicWebsite->save();

        
            return response()->json([
            'status'   => 'Successfully',
            'message'  => $message
          ], 200);
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {        
		$request->validate([
			'template_name' => 'required',
			'template_theme' => 'required',
			'website_name' => 'required|unique:public_websites',
			'plan_name' => 'required',
			'pw_domain' => 'unique:public_websites',
		]);
		try {
			$websiteSlug = Str::slug($request->website_name);
			$domainSlug = Str::slug($request->pw_domain);
			$startDate = date('Y-m-d');
			$monthGet =  PublicWebsitePlans::where('id',$request->plan_name)->first();
			$userID  =  Business::where('id',$id)->first('user_id');
			$start = Carbon\Carbon::now();
			$endDate = $start->addMonths($monthGet->pw_plan_duration); 

			DB::beginTransaction();
			$public_website = new PublicWebsite;
			$public_website->business_id = $id;
			$public_website->user_id = $userID->user_id;
			$public_website->template_id = $request->template_name;
			$public_website->template_theme = $request->template_theme;
			$public_website->website_name = $request->website_name;
			$public_website->website_slug_name = $websiteSlug;
			$public_website->pw_plan_id = $request->plan_name;
			$public_website->pw_plan_start_date = $startDate ;
			$public_website->pw_plan_end_date = $endDate;
			$public_website->pw_type = $request->pw_type;
			$public_website->pw_domain = $request->pw_domain;
			$public_website->pw_slug_domain = $domainSlug;
			$public_website->save();
			// dd($public_website);
			DB::commit();

		return redirect()->route('publicwebsite.list')->with('success', trans('Public Website Save Successfully'));
		} catch (\Exception $e) {
			return redirect()->route('publicwebsite.list')->with('error', trans('Public Website Save Fail'));
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $publicwebsite = PublicWebsite::find($id);
        $PublicWebsiTetemplets = PublicWebsiteTemplets::get();
        $PublicWebsitePlans = PublicWebsitePlans::where('status',1)->get();
        //$business = Business::get();
        return view('Admin.EditPublicWebsite', compact('publicwebsite','PublicWebsiTetemplets','PublicWebsitePlans'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$request->validate([
			'template_name' => 'required',
			'template_theme' => 'required',
		]);

         $websiteSlug = Str::slug($request->website_name);
        $domainSlug = Str::slug($request->pw_domain);
        $startDate = date('Y-m-d');
        $monthGet =  PublicWebsitePlans::where('id',$request->plan_name)->first();
        $start = Carbon\Carbon::now();
        $endDate = $start->addMonths($monthGet->pw_plan_duration); 
        // dd($request->all(),$id);
        $publicwebsite = PublicWebsite::where('id',$id)->first('business_id');
        // dd($publicwebsite->business_id);
            $editPublicWebsite =  PublicWebsite::find($id);
            //$editPublicWebsite->business_id = $publicwebsite->business_id;
            $editPublicWebsite->template_id = $request->template_name;
            $editPublicWebsite->template_theme = $request->template_theme;
            $editPublicWebsite->website_name = $request->website_name;
            $editPublicWebsite->website_slug_name = $websiteSlug;
            $editPublicWebsite->pw_plan_id = $request->plan_name;
            $editPublicWebsite->pw_plan_start_date = $startDate ;
            $editPublicWebsite->pw_plan_end_date = $endDate;
            $editPublicWebsite->pw_type = $request->pw_type;
            $editPublicWebsite->pw_domain = $request->pw_domain;
            $editPublicWebsite->status = $request->status;
            $editPublicWebsite->pw_slug_domain = $domainSlug;
            $editPublicWebsite->update();

        return redirect()->route('publicwebsite.list')->with('success', trans('Public Website Update Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $id = Crypt::decrypt($id);
         $destroyPublicWebsite = PublicWebsite::find($id);
         $destroyPublicWebsite->delete();

          return redirect()->route('publicwebsite.list')->with('success', trans('Public Website Delete Successfully'));
    }
	
	/**
	 * get all public website inquires
	 *
	 * @return void
	 */
	public function getInquiry()
    {

        $inquiries = PublicInquiry::with('business');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $inquiries->whereHas('business', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $inquiries = $inquiries->get();
        return view('Admin.PublicInquiry.index',compact('inquiries'));
    }

	/**
	 * get all public website reviews
	 *
	 * @return void
	 */
	public function getReviews()
    {

        $reviews = PublicReview::with('business');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $reviews->whereHas('business', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $reviews = $reviews->get();
        return view('Admin.PublicReview.index',compact('reviews'));
    }
}
