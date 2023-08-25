<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PublicWebsitePlans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class PublicWebsitePlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $PublicWebsitePlans = PublicWebsitePlans::get();
        return view('Admin.ListPublicWebsitePlans',compact('PublicWebsitePlans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.AddPublicWebsitePlans');
    }

    public function status(Request $request){
        // return $request->all();
        if ($request->status == 0) {
          $status = 0;
          $message = "Public Website Plan Deactive";
        } else {
          $status = 1;
          $message = "Public Website Plan Active";
        }

        $PublicWebsitePlans = PublicWebsitePlans::findOrFail($request->id);
        $PublicWebsitePlans->status = $status;
        $PublicWebsitePlans->save();

        
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
    public function store(Request $request)
    {
        $request->validate([
                'pw_plan_name' => 'required|unique:public_website_plans',
                'plan_features' => 'required',
                'plan_amount' => 'required|numeric',
                'plan_duration' => 'required|numeric'
            ]);
         try {
                \DB::beginTransaction();
                $PublicWebsitePlans = new PublicWebsitePlans;
                $PublicWebsitePlans->pw_plan_name = $request->pw_plan_name;
                $PublicWebsitePlans->pw_plan_features = $request->plan_features;
                $PublicWebsitePlans->pw_plan_mrp = $request->plan_amount;
                $PublicWebsitePlans->pw_plan_amount = $request->plan_amount;
                $PublicWebsitePlans->pw_plan_duration = $request->plan_duration;
                $PublicWebsitePlans->save();
                \DB::commit();

            return redirect()->route('PublicWebsiteplans.list')->with('success', trans('Public Website Plan Save Successfully'));
            } catch (\Exception $e) {
                dd($e);
                return response()->json([
                'message' => 'Error'
                ],500);
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
        //
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
        $PublicWebsitePlans = PublicWebsitePlans::find($id);
        return view('Admin.EditPublicWebsitePlans', compact('PublicWebsitePlans'));
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
        // dd($request->all(),$id);

        $request->validate([
            'pw_plan_name' => [
                        'required',
                        Rule::unique('public_website_plans')->ignore($id),
                    ],
                // 'pw_plan_name' => 'required|unique:public_website_plans',
                'plan_features' => 'required',
                'plan_amount' => 'required|numeric',
                'plan_duration' => 'required'
            ]);
        $editPublicWebsitePlans =  PublicWebsitePlans::find($id);
        $editPublicWebsitePlans->pw_plan_name = $request->pw_plan_name;
        $editPublicWebsitePlans->pw_plan_features = $request->plan_features;
        $editPublicWebsitePlans->pw_plan_mrp = $request->plan_amount;
        $editPublicWebsitePlans->pw_plan_amount = $request->plan_amount;
        $editPublicWebsitePlans->pw_plan_duration = $request->plan_duration;
        $editPublicWebsitePlans->update();

        return redirect()->route('PublicWebsiteplans.list')->with('success', trans('Public Website Plan Update Successfully'));
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
         $destroyPublicWebsite = PublicWebsitePlans::find($id);
         $destroyPublicWebsite->delete();

          return redirect()->route('PublicWebsiteplans.list')->with('success', trans('Public Website Plan Delete Successfully'));
    }
}
