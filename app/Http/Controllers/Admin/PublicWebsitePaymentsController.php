<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PublicWebsite;
use App\publicWebsitePayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublicWebsitePaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $publicWebsitePayments = publicWebsitePayments::has('publicWebsiteName');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $publicWebsitePayments->whereHas('publicWebsiteName.businessName', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        $publicWebsitePayments = $publicWebsitePayments->get();
        return view('Admin.ListPublicWebsiPayments',compact('publicWebsitePayments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $publicWebsite = PublicWebsite::get();
        return view('Admin.AddPublicWebsitePayments',compact('publicWebsite'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
                'website_name' => 'required',
                'amount' => 'required',
                'date' => 'required',
                'pay_trans_id' => 'required|unique:public_website_payments',
                'payment_message' => 'required',
            ]);
            try {
                DB::beginTransaction();
                $publicWebsitePayments = new publicWebsitePayments;
                $publicWebsitePayments->pw_id = $request->website_name;
                $publicWebsitePayments->payment_amount = $request->amount;
                $publicWebsitePayments->payment_date = $request->date;
                $publicWebsitePayments->pay_trans_id = $request->pay_trans_id;
                $publicWebsitePayments->payment_message = $request->payment_message;
                $publicWebsitePayments->save();
                DB::commit();

            return redirect()->route('PublicWebsitepayments.list')->with('success', trans('Public Website Payment Successfully'));
            } catch (\Exception $e) {
               return redirect()->route('PublicWebsitepayments.list')->with('error', trans('Public Website Payment Fail'));
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
        $publicWebsitePayments = publicWebsitePayments::find($id);
        $publicWebsite = PublicWebsite::get();
        return view('Admin.EditPublicWebsitePayments',compact('publicWebsite','publicWebsitePayments'));
        // dd($id,$publicWebsitePayments);
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
                'website_name' => 'required',
                'amount' => 'required',
                'date' => 'required',
                 'pay_trans_id' => [
                        'required',
                        Rule::unique('public_website_payments')->ignore($id),
                    ],
                // 'pay_trans_id' => 'required|unique:public_website_payments',
                'payment_message' => 'required',
            ]);
            try {
                DB::beginTransaction();
                $EditpublicWebsitePayments =  publicWebsitePayments::find($id);
                $EditpublicWebsitePayments->pw_id = $request->website_name;
                $EditpublicWebsitePayments->payment_amount = $request->amount;
                $EditpublicWebsitePayments->payment_date = $request->date;
                $EditpublicWebsitePayments->pay_trans_id = $request->pay_trans_id;
                $EditpublicWebsitePayments->payment_message = $request->payment_message;
                $EditpublicWebsitePayments->update();
                DB::commit();

            return redirect()->route('PublicWebsitepayments.list')->with('success', trans('Public Website Payment Update Successfully'));
            } catch (\Exception $e) {
               return redirect()->route('PublicWebsitepayments.list')->with('error', trans('Public Website Payment Fail'));
            }
    
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
        $destroypublicWebsitePayments = publicWebsitePayments::find($id);
        $destroypublicWebsitePayments->delete();

        return redirect()->route('PublicWebsitepayments.list')->with('success', trans('Public Website Payment Delete Successfully'));
    }
}
