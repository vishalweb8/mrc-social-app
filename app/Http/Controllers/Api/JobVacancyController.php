<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\JobApplied;
use App\JobSkill;
use App\JobVacancy;
use Illuminate\Http\Request;
use Validator;
use Log;
use Image;
use Helpers;
use Config;
use JWTAuth;
use Storage;
class JobVacancyController extends Controller
{
    public function __construct()
    {  
        $this->jobObj =  new JobVacancy(); 
        $this->jobApplyObj =  new JobApplied(); 
        $this->JOB_IMAGE_PATH = Config::get('constant.JOB_IMAGE_PATH'); 
        $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_HEIGHT'); 
        $this->BUSINESS_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_WIDTH'); 
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myJobs(Request $request)
    {
        $type =  $request->type; 
        $user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            // $business->with(['owners:business_id,full_name', 'user:id,name', 'businessImages:business_id,image_name']); 
        
			$jobVacancy = JobVacancy::where('type',$type)
                ->where('user_id', $user->id)
                ->with(["jobskill:job_vacancy_id,skill_name","location"])
                ->get();
            
			$responseData = ['status' => 1, 'message' => trans('apimessages.job_getting'), 'data' => $jobVacancy];
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while getting job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

     
    public function store(Request $request)
    {   
        $user = JWTAuth::parseToken()->authenticate();
       
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            if($request->type = 'Job Hiring'){
                $validator = Validator::make($request->all(), 
                    [
                        'application_id'  =>  'required',
                        'location_id'  =>  'required', 
                        'title'  =>  'required',
                        'description'  =>  'required', 
                        'company_name'  =>  'required', 
                        'type'  =>  'required', 
                        'skills'=>'required',
                        'image_url.*' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400'
                    ]
                );
            }
            else{
                $validator = Validator::make($request->all(), 
                    [
                        'application_id'  =>  'required',
                        'location_id'  =>  'required', 
                        'title'  =>  'required',
                        'description'  =>  'required',  
                        'type'  =>  'required',  
                        'image_url.*' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400'
                    ]
                );
            }
			
            
            if ($validator->fails()) {
				Log::error("Job create validation failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all(); 
            } else {
				if ($request->id) { 
                    $jobVacancy = JobVacancy::firstOrNew(['id' =>  $request->id]);
                } else {
                    $jobVacancy = new JobVacancy();
                }
                $jobVacancy->application_id = $request->application_id;
                $jobVacancy->location_id = $request->location_id;
                $jobVacancy->user_id = $user->id;
                $jobVacancy->title = $request->title;
                $jobVacancy->description = $request->description;
                $jobVacancy->type = $request->type; 

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
                				  
				$responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.job_created');
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		} 
    }

    public function deleteJob(Request $request)
    {   
        $job_vacancy_id = $request->job_vacancy_id;
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
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
                $responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.job_deleted');
            }
            else{

                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.job_not_found');
            }
            
			 
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		} 
    }

    public function jobList(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try { 
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);
            $offset = Helpers::getOffset($pageNo,$limit); 
            
            $filters=[];
            $filters['take'] = $limit; 
            $filters['skip'] = $offset;
            // Filters
            if (isset($request->company_name) && $request->company_name != '') {
                $filters['company_name'] = $request->company_name; 
            } 
            if (isset($request->title) && $request->title != '') {
                $filters['title'] = $request->title; 
            }
            if (isset($request->location_id) && $request->location_id != '') {
                $filters['location_id'] = $request->location_id; 
            }
            if (isset($request->employment_type) && $request->employment_type != '') {
                $filters['employment_type'] = $request->employment_type; 
            }
            if (isset($request->workplace_type) && $request->workplace_type != '') {
                $filters['workplace_type'] = $request->workplace_type; 
            }
            if (isset($request->skills) && $request->skills != '') {
                $filters['skills'] = $request->skills; 
            } 

            $jobVacancy = $this->jobObj->getAll($filters);  
            $jobCount =  $this->jobObj->getAll($filters,true);
            $responseData['total'] = $jobCount;
            $perPageCnt = $pageNo * $limit;
            if($jobCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }

            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.job_getting');
            $responseData['data'] = $jobVacancy;
 
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while getting job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    public function applyJob(Request $request)
    {   
        $user = JWTAuth::parseToken()->authenticate();
       
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
             
            $validator = Validator::make($request->all(), 
                [
                    'job_vacancy_id'  =>  'required',
                    'email'  =>  'required', 
                    'mobile_no'  =>  'required', 
                    'experience' => 'required',
                    'document_file' => 'required|file|mimes:doc,docx,pdf|max:2048',
                ],['document_file.max' => 'Maximum document size to upload is 2MB']
            ); 
            
            if ($validator->fails()) {
				Log::error("Job apply failed.");
                $responseData['status'] = 0;
                $responseData['message'] = $validator->messages()->all(); 
            } else {
				 
                $jobCount = JobVacancy::where('id', '=',  $request->job_vacancy_id)->count();
                if($jobCount>0){    
                    $jobapplyCount = JobApplied::where(['job_vacancy_id' =>  $request->job_vacancy_id, "user_id" =>$user->id])->count();   
                    if($jobapplyCount == 0 ){ 
                        $jobApplied = new JobApplied(); 
                        $jobApplied->job_vacancy_id = $request->job_vacancy_id;
                        $jobApplied->email = $request->email;
                        $jobApplied->mobile_no = $request->mobile_no;
                        $jobApplied->experience = $request->experience;                
                        $jobApplied->user_id = $user->id;
                        if($request->has('document_file')) { 
                            $document = $request->file('document_file');
                            $fileName = 'job_apply_' . uniqid() . '.' .$document->getClientOriginalExtension();
                            $jobApplied->document_file = $fileName;
                            $path = config('constant.JOB_APPLY_IMAGE_PATH');
                            //Uploading on AWS
                            $originalImage = Helpers::addFileToStorage($fileName, $path, $document, "s3");
                        }  
                        $jobApplied->save(); 
                        $responseData['status'] = 1;
                        $responseData['message'] = trans('apimessages.job_apply');
                    }
                    else{
                        $responseData['status'] = 0;
                        $responseData['message'] = trans('apimessages.job_already_apply'); 
                    }
                }
                else{
                    $responseData['status'] = 0;
                    $responseData['message'] = trans('apimessages.job_not_found'); 
                }
			}
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		} 
    }

    public function jobApplyList(Request $request)
    { 
        $user = JWTAuth::parseToken()->authenticate();
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try {
            // Filters
            $pageNo = $request->input('page',1);
            $limit = $request->input('limit',10);
            $offset = Helpers::getOffset($pageNo,$limit); 
            
            $filters=[];
            $filters['take'] = $limit; 
            $filters['skip'] = $offset;
            if (isset($request->experience) && $request->experience != '') {
                $filters['experience'] = $request->experience; 
            } 
            if (isset($request->job_vacancy_id) && $request->job_vacancy_id != '') {
                $filters['job_vacancy_id'] = $request->job_vacancy_id; 
            }
            if (isset($request->mobile_no) && $request->mobile_no != '') {
                $filters['mobile_no'] = $request->mobile_no; 
            }
            if (isset($request->email) && $request->email != '') {
                $filters['email'] = $request->email; 
            } 
            if (isset($request->page) && $request->page != '') {
                $filters['page'] = $request->page; 
            } 
            if (isset($request->limit) && $request->limit != '') {
                $filters['limit'] = $request->limit; 
            } 

            $jobApplied = $this->jobApplyObj->getAll($filters); 
            $jobCount =  $this->jobApplyObj->getAll($filters,true);
            $responseData['total'] = $jobCount;
            $perPageCnt = $pageNo * $limit;
            if($jobCount > $perPageCnt)
            {
                $responseData['loadMore'] = 1;
            } else {
                $responseData['loadMore'] = 0;
            }

            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.job_application_getting');
            $responseData['data'] = $jobApplied; 
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while getting job apply: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		}
    }

    public function JobDetails(Request $request)
    {   
        $job_vacancy_id = $request->job_vacancy_id;
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
		try { 
            $jobCount = JobVacancy::where('id', '=',  $job_vacancy_id)->count();
            if($jobCount>0){ 
                $jobDetails= JobVacancy::where('id', '=',  $job_vacancy_id)
                ->with(['user','jobskill','location'])    
                ->first();
                
                $responseData['status'] = 1;
                $responseData['message'] = trans('apimessages.job_application_getting');
                $responseData['data'] = $jobDetails;
            }
            else{ 
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.job_not_found'); 
            }
            
			 
			return response()->json($responseData,200);
		} catch (\Exception $e) {
			Log::error("Getting error while storing job: ".$e);
			$responseData['message'] = $e->getMessage();
			return response()->json($responseData,400);
		} 
    }


}
