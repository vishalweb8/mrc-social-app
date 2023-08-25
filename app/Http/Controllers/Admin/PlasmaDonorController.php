<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PlasmaDonor;
use Illuminate\Http\Request;
use Log;

class PlasmaDonorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $donors = PlasmaDonor::latest()->get();
		return view('Admin.PlasmaDonor.index', compact('donors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.PlasmaDonor.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateDonor($request);
		$validator->validate();
        try {
			$data = $request->all();
            PlasmaDonor::create($data);
            return redirect()->route('plasmaDonor.index')->with('success',"Plasma donor created successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while creating plasma donor: ".$e);
            return redirect()->route('plasmaDonor.index')->with('error',$e->getMessage());
		}
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PlasmaDonor  $plasmaDonor
     * @return \Illuminate\Http\Response
     */
    public function edit(PlasmaDonor $plasmaDonor)
    {
        return view('Admin.PlasmaDonor.edit',compact('plasmaDonor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PlasmaDonor  $plasmaDonor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PlasmaDonor $plasmaDonor)
    {
        $validator = $this->validateDonor($request);
        $validator->validate();
        try {
            $plasmaDonor->update($request->all());
            return redirect()->route('plasmaDonor.index')->with('success',"Plasma donor updated successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while updating plasma donor: ".$e);
            return redirect()->route('plasmaDonor.index')->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PlasmaDonor  $plasmaDonor
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlasmaDonor $plasmaDonor)
    {
        try {
            $plasmaDonor->delete();
            return redirect()->route('plasmaDonor.index')->with("success","Plasma donor deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting plasma donor: ".$e);
            return redirect()->route('plasmaDonor.index')->with("error",$e->getMessage());
        }
    }
}
