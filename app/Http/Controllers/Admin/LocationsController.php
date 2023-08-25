<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Crypt;
use Image;
use Helpers;
use Config;

class LocationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->COUNTRY_FLAG_IMAGE_PATH = Config::get('constant.COUNTRY_FLAG_IMAGE_PATH');
        $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH');
        $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT');
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $locations = Location::select('*')->where('status', 1)->orderby("id", "ASC");
            $user = auth()->user();
            return DataTables::of($locations)
                ->addColumn('action', function ($location) use ($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-location';
                    // $encriptId = Crypt::encrypt($location->id);
                    $encriptId = $location->id;
                    $attributes = [
                        "data-url" => route('location.destroy', $location->id)
                    ];

                    if ($user->can(config('perm.editLocation'))) {
                        $editUrl = route('location.edit', $encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if ($user->can(config('perm.deleteLocation'))) {
                        $deleteBtn = getDeleteBtn("#", $class, $attributes);
                    }
                    return $editBtn . $deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.Location.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $location = [];
        return view('Admin.Location.edit', compact("location"));
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
            'country' => 'required',
            'country_code' => 'required',
            'state' => 'required',
            'district' => 'required',
            'city' => 'required',
            'pincode' => 'required',
            'position' => 'integer',
        ]);
        //'pincode' => 'required|unique:Locations,pincode,' . $request->pincode . ',pincode,city,' . $request->city



        try {
            $message = "Location saved successfully";
            if ($request->id) {
                $message = "Location update successfully";
                $location = Location::firstOrNew(['id' =>  $request->id]);
            } else {
                $location = Location::firstOrNew(['pincode' =>  $request->pincode, ["city", $request->city]]);
            }
            $location->country = $request->country;
            $location->country_code = $request->country_code;
            $location->state = $request->state;
            $location->district = $request->district;
            $location->tehsil = $request->tehsil;
            $location->city = $request->city;
            $location->locality = $request->locality;
            $location->pincode = $request->pincode;
            $location->latitude = $request->latitude;
            $location->longitude = $request->longitude;
            $location->type = $request->type;
            $location->position = $request->position;
            // upload country flag
            if ($request->flag) {
                $flag = $request->flag;

                if (!empty($flag)) {
                    $fileName = 'country_flag_' . uniqid() . '.' . $flag->getClientOriginalExtension();

                    $pathThumb = (string) Image::make($flag->getRealPath())->resize($this->COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH, $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT)->encode();

                    $location->flag = $fileName;

                    if (isset($postData['old_flag']) && $postData['old_flag'] != '') {
                        $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_flag'], $this->COUNTRY_FLAG_IMAGE_PATH, "s3");
                    }
                    //Uploading on AWS
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->COUNTRY_FLAG_IMAGE_PATH, $pathThumb, "s3");
                }
            }
            $location->save();
            return redirect()->route('location.index')->with('success', $message);
        } catch (\Throwable $th) {
            Log::error('getting error while saving resons:- ' . $th);
            return redirect()->route('location.index')->with('error', $th->getMessage());
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
    public function edit(Location $location)
    {
        return view('Admin.Location.edit', compact("location"));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect()->route('location.index')->with("success", "Location deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting reason: " . $th);
            return redirect()->route('location.index')->with("error", $th->getMessage());
        }
    }
}
