<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Permission;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listRole'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addRole').'|'.config('perm.editRole')],   ['only' => ['store']]);
        $this->middleware('permission:'.config('perm.addRole'), ['only' => ['create']]);
        $this->middleware('permission:'.config('perm.editRole'), ['only' => ['store']]);
        $this->middleware('permission:'.config('perm.deleteRole'),   ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::query();
            $user = auth()->user();
            return DataTables::of($roles)
                ->addColumn('action', function($role) use($user) {
                    if($role->name == 'Super Admin') {
                        return '-';
                    }
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-role';
                    $id = $role->id;
                    $attributes = [
                        "data-url" => route('role.destroy',$id)
                    ];
                    if($user->can(config('perm.editRole'))) {
                        $editUrl = route('role.edit',$id);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteRole'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                   
                    return $editBtn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.Role.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $assignedPermissions = [];
        $permissions = $this->getPermisssions();
        return view('Admin.Role.edit',compact('permissions','assignedPermissions'));
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
            'name' => 'required|string|unique:roles,name,' . $request->id,
        ]);
        try {
            config(['sluggable.includeTrashed' => true]);
            $role = Role::firstOrNew(['id' =>  $request->id]);
            $role->name = $request->name;
            $role->type = $request->type;
            $role->save();
            $permissions = $request->input('permissions',[]);
            $role->permissions()->sync($permissions);
            config(['sluggable.includeTrashed' => false]);
            return redirect()->route('role.index')->with('success',"Role saved successfully");
        } catch (\Throwable $th) {
            config(['sluggable.includeTrashed' => false]);
            Log::error('getting error while saving role:- '.$th);
            return redirect()->route('role.index')->with('error',$th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $assignedPermissions = $role->permissions->pluck('id')->toArray();
        $permissions = $this->getPermisssions();
        return view('Admin.Role.edit',compact('role','permissions','assignedPermissions'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  mixed $role
     * @return void
     */
    public function destroy(Role $role)
    {
        try {
            $id = $role->id;
            $role->delete();
            Log::info("Role ($id) deleted by ".auth()->id());
            return redirect()->route('role.index')->with("success","Role deleted successfully");
        } catch (\Exception $e) {
            Log::error("Getting error while deleting Role: ".$e);
            return redirect()->route('role.index')->with("error",$e->getMessage());
        }
    }
        
    /**
     * get all permission by module
     *
     * @return void
     */
    public function getPermisssions()
    {
        $permissions = Permission::orderBy('module')->get()->groupBy('module');
        return $permissions;
    }
}
