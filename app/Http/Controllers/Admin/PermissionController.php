<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listPermission'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addPermission')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editPermission'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deletePermission'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::query();
            $user = auth()->user();
            return DataTables::of($permissions)
                ->addColumn('action', function($permission) use($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-permission';
                    $id = $permission->id;
                    $attributes = [
                        "data-url" => route('permission.destroy',$id)
                    ];
                    if($user->can(config('perm.editPermission'))) {
                        $editUrl = route('permission.edit',$id);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deletePermission'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                   
                    return $editBtn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.Permission.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.Permission.create');
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
            //'name' => 'required|string|unique:permissions,name,' . $request->id,
            'module' => 'required|string',
        ]);
        try {

            $permissions = $request->input('name',[]);

            foreach($permissions as $name) {
                if(!empty($name)) {
                    $name .= ' '.$request->module;
                    $permission = Permission::withTrashed()->firstOrNew(['name' =>  $name]);
                    $permission->name = $name;
                    $permission->module = $request->module;
                    $permission->deleted_at = NULL;
                    $permission->save();
                }
            }

            return redirect()->route('permission.index')->with('success',"Permission saved successfully");
        } catch (\Throwable $th) {
            Log::error('getting error while saving permission:- '.$th);
            return redirect()->route('permission.index')->with('error',$th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        return view('Admin.Permission.edit',compact('permission'));
    }
    
    /**
     * update
     *
     * @param  mixed $permission
     * @param  mixed $request
     * @return void
     */
    public function update(Permission $permission,Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);
        try {
            $permission->name = $request->name;
            $permission->save();
            return redirect()->route('permission.index')->with('success',"permission saved successfully");
        } catch (\Throwable $th) {
            Log::error('getting error while updating permission:- '.$th);
            return redirect()->route('permission.index')->with('error',$th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        try {
            $id = $permission->id;
            $permission->delete();
            Log::info("Permission ($id) deleted by ".auth()->id());
            return redirect()->route('permission.index')->with("success","Permission deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting permission: ".$e);
            return redirect()->route('permission.index')->with("error",$e->getMessage());
        }
    }
}
