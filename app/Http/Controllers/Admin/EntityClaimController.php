<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\EntityClaim;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Log;
use Yajra\DataTables\Facades\DataTables;

class EntityClaimController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()) {
            $claimRequests = EntityClaim::select('entity_claims.*')->where('entity_claims.status','pending')->has('claimBy')->whereHas('entity', function ($query) {
                $query->where('user_id', 0);
                $query->orWhere('user_id', null);
            })->with('claimBy:id,name,phone','entity:id,name');
            $user = auth()->user();

            if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
                $claimRequests->whereHas('entity', function ($query) use ($user) {
                    $query->whereRaw($user->sql_query);
                });
            }
            return DataTables::of($claimRequests)
                ->addColumn('user_name', function($claimRequest) { 
                    $name = '';                   
                    if(!empty($claimRequest->claimBy)) {
                        $name = "<a href='".url('/admin/edituser',\Crypt::encrypt($claimRequest->claimBy->id))."' target='_blank'>".$claimRequest->claimBy->name."</a>";
                    }
                    return $name;
                })
                ->addColumn('entity_name', function($claimRequest) { 
                    $name = '';                   
                    if(!empty($claimRequest->entity)) {
                        $name = "<a href='".route('entity.show',\Crypt::encrypt($claimRequest->entity_id))."' target='_blank'>".$claimRequest->entity->name."</a>";
                    }
                    return $name;
                })
                ->editColumn('document', function($claimRequest) use($user) { 
                    $url = 'N/A';                   
                    if($user->can(config('perm.viewDocClaim')) && !empty($claimRequest->document)) {
                        $url = "<a href='".$claimRequest->document."' target='_blank'>View</a>";
                    }
                    return $url;
                })
                ->addColumn('action', function($claimRequest) use($user) {
                    $approveBtn = $rejectBtn = '';
                    $class = 'update-claim-status';
                    $attributes = [
                        "data-url" => route('entity.claim.update',$claimRequest->id),
                        "data-status" => 'approved'
                    ];

                    if($user->can(config('perm.approveClaim'))) {
                        $approveBtn = getApproveBtn("#",$class, $attributes);
                    }
                    if($user->can(config('perm.rejectClaim'))) {
                        $attributes['data-status'] = 'rejected';
                        $rejectBtn = getRejectBtn("#",$class, $attributes);
                    }

                    return $approveBtn.$rejectBtn;
                })
                ->rawColumns(['user_name','entity_name','document','action'])
                ->make(true);
        }
        return view('Admin.Entity.claim-request');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'entity_id' => 'required',
                'document' => 'required|file|mimes:doc,docx,pdf,csv|max:2048',
            ],['document.max' => 'Maximum document size to upload is 2MB']);
            if ($validator->fails())
            {
                Log::error('API validation failed while claim entity');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $entityId = $request->entity_id;                
                $entity = Business::find($entityId);
                if(!empty($entity)) {
                    $userId = auth()->id();
                    $isExistsBusiness = Business::where('user_id',$userId)->exists();
                    if($isExistsBusiness) {
                        $response =['status'=> 0, 'message' =>trans('apimessages.already_have_business')];
                        return response()->json($response,200);
                    }

                    $entityType = isset($entity->entityType) ? $entity->entityType->name : 'business';
                    if($entity->user_id > 0) {
                        $response =['status'=> 0, 'message' =>trans('apimessages.already_claim_entity',['entity_type'=>$entityType])];
                        return response()->json($response,200);
                    }

                    $isExistsClaim = EntityClaim::where('entity_id',$entityId)->where('claim_by',$userId)->where('status','pending')->exists();
                    if($isExistsClaim) {
                        $response =['status'=> 0, 'message' =>trans('apimessages.already_sent_request_for_claim_entity',['entity_type'=>$entityType])];
                        return response()->json($response,200);
                    }
                    $url = null;
                    if($request->has('document')) {
                        $document = $request->file('document');
                        $fileName = 'entity_claim_' . uniqid() . '.' . $document->getClientOriginalExtension();
                        $path = config('constant.ENTITY_CLAIM_DOCUMENT_PATH');
                        //Uploading on AWS
                        $originalImage = Helpers::addFileToStorage($fileName, $path, $document, "s3");
                        $url = $path.$originalImage;
                    }
                    EntityClaim::updateOrCreate(['entity_id'=>$entityId,'claim_by'=>$userId],['entity_id'=>$entityId,'claim_by'=>$userId,'document'=>$url,'status'=>'pending']);
                    $response =['status'=> 1, 'message' =>trans('apimessages.successfully_sent_request_for_claim_entity')];
		            return response()->json($response,200);
                }
                
                $response =['status'=> 0, 'message' =>trans('apimessages.invalid_business_id')];
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            Log::error("getting error while sent request for business claim, userId(".auth()->id()."):- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $claim = EntityClaim::find($request->id);
            if($claim && $request->status == 'approved') {
                $entity = Business::find($claim->entity_id);
                if( $entity && $entity->user_id > 0) {
                    $entityType = isset($entity->entityType) ? $entity->entityType->name : 'business';
                    $response =['status'=> 0, 'message' =>trans('apimessages.already_claim_entity',['entity_type'=>$entityType])];
                    return response()->json($response,200);
                } else {
                    $entity->user_id = $claim->claim_by;
                    $entity->save();
                    $response =['status'=> 1, 'message' =>trans('apimessages.business_claim_approved_successfully')];
                }
            } else {
                $response =['status'=> 1, 'message' =>trans('apimessages.business_claim_rejected_successfully')];
            }
            $claim->status = $request->status;
            $claim->save();
            
		    return response()->json($response,200);
        } catch (\Throwable $th) {
            Log::error("getting error while update business claim, userId(".auth()->id()."):- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EntityClaim  $entityClaim
     * @return \Illuminate\Http\Response
     */
    public function destroy(EntityClaim $entityClaim)
    {
        //
    }
}
