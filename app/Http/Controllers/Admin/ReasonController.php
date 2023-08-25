<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class ReasonController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listReason'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addReason').'|'.config('perm.editReason')],   ['only' => ['store']]);
        $this->middleware('permission:'.config('perm.addReason'), ['only' => ['create']]);
        $this->middleware('permission:'.config('perm.editReason'), ['only' => ['edit']]);
        $this->middleware('permission:'.config('perm.deleteReason'),   ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $reasons = Reason::select('reasons.*')->with('assetType');
            $user = auth()->user();
            return DataTables::of($reasons)
                ->addColumn('entity_type', function($reason) {
                    $entityType = '';
                    if(!empty($reason->assetType)) {
                        $entityType = $reason->assetType->name;
                    }                                     
                    return $entityType;
                })
                ->editColumn('reason', function($reason) {                                      
                    return Str::limit($reason->reason,250);
                })
                ->addColumn('action', function($reason) use($user) {
                    $class = 'delete-reason';
                    $editBtn = $deleteBtn = '';
                    $attributes = [
                        "data-url" => route('reason.destroy',$reason->id)
                    ];
                    if($user->can(config('perm.editReason'))) {
                        $editUrl = route('reason.edit',$reason->id);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteReason'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                
                    return $editBtn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.Reason.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $reason = [];
        return view('Admin.Reason.edit',compact('reason'));
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
            'asset_type' => 'required',
           // 'sub_asset_type' => 'required',
            'reason' => 'required|string',
        ],['entity_type.required_if' => 'The entity type field is required.']);
        try {            
            $reason = Reason::firstOrNew(['id' =>  $request->id]);
            if(!empty($request->sub_asset_type)) {
                $reason->asset_type_id = !empty($request->sub_asset_type) ? $request->sub_asset_type : NULL;
            } else {
                $reason->asset_type_id = !empty($request->asset_type) ? $request->asset_type : NULL;
            }
            $reason->reason = $request->reason;
            $reason->save();
            return redirect()->route('reason.index')->with('success',"Reason saved successfully");
        } catch (\Throwable $th) {
            Log::error('getting error while saving resons:- '.$th);
            return redirect()->route('reason.index')->with('error',$th->getMessage());
        }        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reason  $reason
     * @return \Illuminate\Http\Response
     */
    public function edit(Reason $reason)
    {
        return view('Admin.Reason.edit',compact('reason'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reason  $reason
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reason $reason)
    {
        try {
            $reason->delete();
            return redirect()->route('reason.index')->with("success","Reason deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting reason: ".$th);
            return redirect()->route('reason.index')->with("error",$th->getMessage());
        }
    }
    
    /**
     * get reasons for reports to entity
     *
     * @param  mixed $request
     * @return void
     */
    public function getReasonsByEntityType(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                //'asset_type_id' => 'required'
            ]);
            if ($validator->fails())
            {
                Log::error('API validation failed while get reasons');
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } else {
                $reasons = Reason::select('id','asset_type_id','reason');
                if(!empty($request->asset_type_id)) {
                    $reasons->where('asset_type_id',$request->asset_type_id); 
                } else {
                    $type = $request->input('type','Post');
                    $reasons->whereRaw("(asset_type_id = (select id from asset_types where name like '{$type}' limit 1))");
                }
                $reasons =  $reasons->get();
                $response =['status'=> 1, 'message' =>trans('apimessages.getting_report_reasons'),'data' => $reasons ];
                if($reasons->isEmpty()) {
                    $response['message'] =trans('apimessages.norecordsfound');
                }
		        return response()->json($response,200);
            }
        } catch (\Throwable $th) {
            Log::error("Getting error while fetching reasons:- ".$th);
            $outputArray['status'] = 0;
            $outputArray['message'] = $th->getMessage();
            $statusCode = 400;
            return response()->json($outputArray, $statusCode);
        }
    }
}
