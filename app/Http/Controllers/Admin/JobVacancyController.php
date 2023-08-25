<?php

namespace App\Http\Controllers\Admin;

use App\Application;
use App\Http\Controllers\Controller;
use App\JobApplied;
use App\JobSkill;
use App\JobVacancy;
use App\Location;
use Storage;
use Illuminate\Http\Request; 
use Yajra\DataTables\Facades\DataTables;
use Config;
use Log;
use Image;
use Helpers; 

class JobVacancyController extends Controller
{
    public function __construct()
    { 
        $this->objApp = new Application();
        $this->JOB_IMAGE_PATH = Config::get('constant.JOB_IMAGE_PATH'); 
        $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_HEIGHT'); 
        $this->BUSINESS_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_WIDTH'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $jobs = JobVacancy::where('status', 1)->orderby("id", "ASC"); 
            $user = auth()->user();
            return DataTables::of($jobs)
                ->addColumn('action', function ($job) use ($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-jobs';
                    // $encriptId = Crypt::encrypt($location->id);
                    $encriptId = $job->id;
                    $attributes = [
                        "data-url" => route('jobs.destroy', $job->id)
                    ];

                    if ($user->can(config('perm.editJobs'))) {
                        $editUrl = route('jobs.edit', $encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if ($user->can(config('perm.deleteJobs'))) {
                        $deleteBtn = getDeleteBtn("#", $class, $attributes);
                    }
                    return $editBtn . $deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.JobVacancy.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $jobVacancy = [];
        $autoLocation=[];
        $applications = $this->objApp->getAll();
        return view('Admin.JobVacancy.edit', compact("jobVacancy",'applications',"autoLocation"));
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
            'application_id'  =>  'required',
            'location_id'  =>  'required', 
            'title'  =>  'required',
            'description'  =>  'required', 
            'company_name'  =>  'required',  
            'qualification'  =>  'required',  
            'experience'  =>  'required',  
            'workplace_type'  =>  'required',  
            'employment_type'  =>  'required',  
            'skills'=>'required',
            'image_url.*' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400'
        ]);
		try { 
            $user = auth()->user();  
            $message="Job save successfully";
            if ($request->id) {
                $message="Job update successfully"; 
                $jobVacancy = JobVacancy::firstOrNew(['id' =>  $request->id]);
            } else {
                $jobVacancy = new JobVacancy();
            }
           
            $jobVacancy->application_id = $request->application_id;
            $jobVacancy->location_id = $request->location_id;
            $jobVacancy->user_id = $user->id;
            $jobVacancy->title = $request->title;
            $jobVacancy->description = $request->description;
            $jobVacancy->type = 'Job Hiring'; 

            if(isset($request->external_link)){
                $jobVacancy->external_link = $request->external_link;
            }
            if(isset($request->qualification)){
                $jobVacancy->qualification = $request->qualification;
            }
            if(isset($request->experience)){
                $jobVacancy->experience = $request->experience;
            }
            if(isset($request->workplace_type)){
                $jobVacancy->workplace_type = $request->workplace_type;
            }
            if(isset($request->employment_type)){
                $jobVacancy->employment_type = $request->employment_type;
            }
            if(isset($request->company_name)){
                $jobVacancy->company_name = $request->company_name;
            }  

            if ($request->image_url) {
                $image_url = $request->image_url; 
                if (!empty($image_url)) {
                    $fileName = 'job_' . uniqid() . '.' . $image_url->getClientOriginalExtension();

                    $pathThumb = (string) Image::make($image_url->getRealPath())->resize($this->BUSINESS_THUMBNAIL_IMAGE_WIDTH, $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT)->encode();

                    $jobVacancy->image_url = $fileName;

                    // if (isset($postData['old_flag']) && $postData['old_flag'] != '') {
                    //     $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_flag'], $this->JOB_IMAGE_PATH, "s3");
                    // }
                    //Uploading on AWS
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->JOB_IMAGE_PATH, $pathThumb, "s3");
                }
            } 
            // dd($jobVacancy); exit;  
            $jobVacancy->save(); 
            if($request->id){
                $jobSkill = JobSkill::where('job_vacancy_id', '=',  $request->id)->delete();
            }
            if(isset($request->skills)){
                $skills= $request->skills;
                $skillArray = explode(',',$skills); 
                foreach($skillArray as $skill){
                    $jobSkill = new JobSkill(); 
                    $jobSkill->skill_name = $skill;
                    $jobSkill->job_vacancy_id = $jobVacancy->id;
                    $jobSkill->save();
                }
            } 
			return redirect()->route('jobs.index')->with('success', $message);
		} catch (\Exception $e) {
			Log::error('getting error while saving job:- ' . $e);
            return redirect()->route('jobs.index')->with('error', $e->getMessage());
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
        $jobVacancy = JobVacancy::where('id',$id)->with("jobskill")->first();
        $skills = [];
        foreach($jobVacancy->jobskill as $singleSkill){
            $skills[]=$singleSkill->skill_name;
        }
        $jobVacancy->skills = implode(',',$skills);
        $autoLocation = Location::find($jobVacancy->location_id); 
        $applications = $this->objApp->getAll();
        return view('Admin.JobVacancy.edit', compact("jobVacancy",'applications','autoLocation'));
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
    public function destroy($job_vacancy_id)
    {
        try {

            $jobCount = JobVacancy::where('id', '=',  $job_vacancy_id)->count();
            if($jobCount>0){ 
                $jobDetail= JobVacancy::where('id', '=',  $job_vacancy_id)->select('image_url')->first();
                // Delete Photo
                    $Jpath = config('constant.JOB_IMAGE_PATH');
                    if(Storage::disk(config('constant.DISK'))->exists($Jpath.$jobDetail->image_url)) {
                        if(Storage::disk(config('constant.DISK'))->delete($Jpath.$jobDetail->image_url)) {
                            $isDeleted = true;
                            info('Job file deleted:- '.$Jpath.$jobDetail->image_url);
                        }
                    }
                // delete photo
                JobVacancy::where('id', '=',  $job_vacancy_id)->delete();
                JobSkill::where('job_vacancy_id', '=',  $job_vacancy_id)->delete(); 
                $jobAppliedCount = JobApplied::where('job_vacancy_id', '=',  $job_vacancy_id)->count();
                if($jobAppliedCount>0){
                    $jobApplied = JobApplied::where('job_vacancy_id', '=',  $job_vacancy_id)->select('document_file')->get();
                    foreach($jobApplied as $apply){
                        $path = config('constant.JOB_APPLY_IMAGE_PATH');
                        if(Storage::disk(config('constant.DISK'))->exists($path.$apply->document_file)) {
                            if(Storage::disk(config('constant.DISK'))->delete($path.$apply->document_file)) {
                                $isDeleted = true;
                                info('Job applied file deleted:- '.$path.$apply->document_file);
                            }
                        }
                    }
                    JobApplied::where('job_vacancy_id', '=',  $job_vacancy_id)->delete(); 
                }
                return redirect()->route('jobs.index')->with("success", "Job deleted successfully");
            }
            else{ 
                return redirect()->route('jobs.index')->with("error", "Job not found");
            } 
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting Job: " . $th);
            return redirect()->route('jobs.index')->with("error", $th->getMessage());
        }  
    }
 

    public function autocomplete(Request $request)
    {  
        $data = Location::select("*")
                ->where("pincode","LIKE","%".$request->pincode."%")
                ->get();
   
        return response()->json($data);
    }
}
