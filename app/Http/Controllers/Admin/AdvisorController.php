<?php

namespace App\Http\Controllers\Admin;

use App\Advisor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class AdvisorController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listAdvisors'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addAdvisors')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editAdvisors'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deleteAdvisors'),   ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $advisors = Advisor::latest()->get();
		return view('Admin.Advisor.index', compact('advisors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.Advisor.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateInput($request);
		$validator->validate();
        try {
			$data = $request->except(['_token','image']);
			$data['image'] = $this->uploadImage($request);
            Advisor::create($data);
            return redirect()->route('advisor.index')->with('success',"Advisor created successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while creating Advisor: ".$e);
            return redirect()->route('advisor.index')->with('error',$e->getMessage());
		}
    }    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Advisor  $advisor
     * @return \Illuminate\Http\Response
     */
    public function edit(Advisor $advisor)
    {
        return view('Admin.Advisor.edit',compact('advisor'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Advisor  $advisor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Advisor $advisor)
    {
        $validator = $this->validateInput($request);
        $validator->validate();
        try {
			$data = $request->except(['_token','image']);
			if ($request->file('image')) {
				$data['image'] = $this->uploadImage($request);
			}
            $advisor->update($data);
            return redirect()->route('advisor.index')->with('success',"Advisor updated successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while updating post: ".$e);
            return redirect()->route('advisor.index')->with('error',$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Advisor  $advisor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advisor $advisor)
    {
		try {
            $advisor->delete();
            return redirect()->route('advisor.index')->with("success","Advisor deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting Advisor: ".$e);
            return redirect()->route('advisor.index')->with("error",$e->getMessage());
        }

    }
	
	/**
	 * for validate input field
	 *
	 * @param  mixed $request
	 * @return object
	 */
	public function validateInput($request)
	{
		$validator = \Validator::make($request->all(), 
			[                    
				'name' => 'required',
				'email' => 'required|email',
				'mobile_number' => 'required',
				'position' => 'required',
			]
		);

		return $validator;
	}
	
	/**
	 * for upload image on AWS server
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
				$fileName = 'advisor_' . uniqid() . '.' . $image->getClientOriginalExtension();
				$path = config('constant.ADVISOR_IMAGE_PATH');
				//Uploading on AWS
				$originalImage = \Helpers::addFileToStorage($fileName, $path, $image, "s3");

				return $path.$originalImage;
			} else {
				info("Advisor image is empty");
				return null;
			}
		} catch (\Throwable $th) {
			Log::error("Getting error while uploading Advisor image: ".$th);
			return null;
		}
	}

}
