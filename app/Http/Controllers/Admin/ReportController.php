<?php

namespace App\Http\Controllers\Admin;

use App\EntityReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class ReportController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listReport'), ['only' => ['index']]);
        $this->middleware('permission:'.config('perm.viewReport'), ['only' => ['show']]);
        $this->middleware('permission:'.config('perm.deleteReport'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $reports = EntityReport::select('entity_reports.*')->hasRelation()->with('assetType','reportBy:id,name');
            if($request->asset_type_id) {
                $reports->where('asset_type_id',$request->asset_type_id);
            }
            if($request->created_at) {
                $reports->whereDate('created_at',$request->created_at);
            }
            $user = auth()->user();
            return DataTables::of($reports)
                ->editColumn('report_by', function($report) {
                    $name = viewUserUrl($report->reportBy);                               
                    return $name;
                })
                ->editColumn('asset_type', function($report) use($user) {
                    $name = $url = '';
                    if(!empty($report->entity_id) && $user->can(config('perm.viewEntity'))) {
                        $url = route('entity.show',Crypt::encrypt($report->entity_id));
                    } else if(!empty($report->post_id) &&  $user->can(config('perm.editPost'))) {
                        $url = route('publicPost.edit',$report->post_id);
                    } else if(!empty($report->site_id) &&  $user->can(config('perm.viewSite'))) {
                        $url = route('site.show',$report->site_id);
                    }
                    if(!empty($report->assetType)) {
                        $name = $report->assetType->name;
                        if(!empty($url)) {
                            $name = "<a href='$url' target='_blank'> $name </a>";
                        }
                    }                                     
                    return $name;
                })
                ->editColumn('comment', function($report) {                                    
                    return Str::limit($report->comment,250);
                })
                ->addColumn('action', function($report) use($user) {
                    $class = 'delete-report';
                    $viewBtn = $deleteBtn = '';
                    $attributes = [
                        "data-url" => route('report.destroy',$report->id)
                    ];
                    if($user->can(config('perm.viewReport'))) {
                        $viewUrl = route('report.show',$report->id);
                        $viewBtn = getViewBtn($viewUrl);
                    }
                    if($user->can(config('perm.deleteReport'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                
                    return $viewBtn.$deleteBtn;
                })
                ->rawColumns(['report_by','asset_type','action'])
                ->make(true);
        }
        $assetTypes = EntityReport::leftJoin("asset_types", "asset_types.id", "=", "entity_reports.asset_type_id")->select('asset_type_id','asset_types.name')->distinct('asset_type_id')->get();
        return view('Admin.Report.index',compact('assetTypes'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $report = EntityReport::with('reportBy','reasons')->findOrFail($id);
        return view('Admin.Report.show', compact('report'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $report = EntityReport::find($id);
            $report->delete();
            return redirect()->route('report.index')->with("success","Report deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting report: ".$th);
            return redirect()->route('report.index')->with("error",$th->getMessage());
        }
    }
}
