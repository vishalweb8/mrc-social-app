<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Settings::pluck('value','key');
		return view('Admin.Settings.index', compact('settings'));
    }
  

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        try {
			// for checkbox field
			$excepts = ['_token','is_otp_enable','is_upi_enable'];
			$data = $request->except($excepts);
			$data['is_otp_enable'] = $request->input('is_otp_enable',false);
			$data['is_upi_enable'] = $request->input('is_upi_enable',false);
			foreach($data as $key => $value) {
				Settings::updateOrCreate(['key'=> $key],['key'=> $key,'value'=> $value]);
			}
			return redirect()->back()->with('success','Settings updated successfully.');
		} catch (\Exception $e) {
			\Log::error("Getting error while saving settings: ".$e);
			return redirect()->back()->with('error',$e->getMessage());
		}
    }
    
}
