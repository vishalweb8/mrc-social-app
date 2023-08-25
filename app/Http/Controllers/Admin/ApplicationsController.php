<?php

namespace App\Http\Controllers\Admin;
use App\Application;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Helpers;
use Config;

class ApplicationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $applications = Application::select('*')->orderby("id", "ASC");
            $user = auth()->user();
            return DataTables::of($applications)
                ->addColumn('action', function ($application) use ($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-application';
                    // $encriptId = Crypt::encrypt($application->id);
                    $encriptId = $application->id;
                    $attributes = [
                        "data-url" => route('application.destroy', $application->id)
                    ];

                    if ($user->can(config('perm.editApplication'))) {
                        $editUrl = route('application.edit', $encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if ($user->can(config('perm.deleteApplication'))) {
                        $deleteBtn = getDeleteBtn("#", $class, $attributes);
                    }
                    return $editBtn . $deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.Application.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $application = [];
        return view('Admin.Application.edit', compact("application"));
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
            'name' => 'required',
            'slug' => 'required'
        ]);

        try {
            $message = "Application saved successfully";
            if ($request->id) {
                $message = "Application update successfully";
                $application = Application::firstOrNew(['id' =>  $request->id]);
            } else {
                $application = new Application();
            }
            $application->name = $request->name;
            $application->slug = $request->slug;  
            $application->save();
            return redirect()->route('application.index')->with('success', $message);
        } catch (\Throwable $th) {
            Log::error('getting error while saving application:- ' . $th);
            return redirect()->route('application.index')->with('error', $th->getMessage());
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
    public function edit(Application $application)
    {
        return view('Admin.Application.edit', compact("application"));
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
    public function destroy(Application $application)
    {
        try {
            $application->delete();
            return redirect()->route('application.index')->with("success", "application deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting reason: " . $th);
            return redirect()->route('application.index')->with("error", $th->getMessage());
        }
    }
}
