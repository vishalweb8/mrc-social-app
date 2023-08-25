<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\JobApplied; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Crypt;
use Image;
use Helpers;
use Config;

class JobAppliedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $jobApplieds = JobApplied::select('*')
                ->with(["jobvacancy","user"])
                ->orderby("id", "ASC");
            $user = auth()->user();
            return DataTables::of($jobApplieds)
                ->addColumn('action', function ($jobs) use ($user) {
                     $deleteBtn = ''; 
                    $document_file = $jobs->document_fullurl;   
                    return  "<a href='{$document_file}' class='mr5 '>
                    <span data-toggle='tooltip' data-original-title='Delete' class='glyphicon glyphicon-download'></span>
                </a>";
                })
                ->make(true);
        }
        return view('Admin.JobApplied.index');
    } 
}
