<?php

namespace App\Http\Controllers\Admin;

use App\AssetType;
use App\AssetTypeField;
use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AssetTypeController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listAssetType'), ['only' => ['index']]);
        $this->middleware('permission:'.config('perm.listSubAsset'), ['only' => ['getSubAsset']]);
        $this->middleware(['permission:'.config('perm.addAssetType').'|'.config('perm.addSubAsset').'|'.config('perm.editSubAsset')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.editSubAsset'), ['only' => ['edit']]);
        $this->middleware('permission:'.config('perm.deleteSubAsset'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $assetTypes = AssetType::where('parent',0);
            $user = auth()->user();
            return DataTables::of($assetTypes)
                ->addColumn('action', function($assetType) use($user) {
                    // $class = 'delete-asset-type';
                    // $attributes = [
                    //     "data-url" => route('assetType.destroy',$assetType->id)
                    // ];
                    // $editUrl = route('assetType.edit',$assetType->id);
                    // $btn = getEditBtn($editUrl);

                    // $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    $childBtn = '';
                    if($user->can(config('perm.listSubAsset'))) {
                        $url = route('getSubAssetTypeByAsset',$assetType->id);
                        $childBtn = getChildBtn($url);
                    }

                    return $childBtn;
                })
                ->make(true);
        }
        return view('Admin.AssetType.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($parent = 0)
    {
        $assetType = [];
        return view('Admin.AssetType.edit',compact('assetType','parent'));
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
            'name' => 'required|string|unique:asset_types,name,' . $request->id,
        ]);
        $assetType = AssetType::firstOrNew(['id' =>  $request->id]);
        $assetType->name = $request->name;
        $assetType->parent = $request->parent;
        $assetType->save();
        if(!empty($request->components)) {
            $components = json_encode($request->components);
            $categoryId = $request->input('category_id',null);
            $data = [
                'category_id' => $categoryId,
                'selected_fields' => $components
            ];   
            $assetType->fields()->updateOrCreate(['category_id' => $categoryId], $data);
        }
        if($request->parent > 0) {
            return redirect()->route('getSubAssetTypeByAsset',$request->parent)->with('success',"Sub Assest type saved successfully");
        }
        return redirect()->route('assetType.index')->with('success',"Assest type saved successfully");
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AssetType  $assetType
     * @return \Illuminate\Http\Response
     */
    public function edit(AssetType $assetType)
    {
        return view('Admin.AssetType.edit',compact('assetType'));
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AssetType  $assetType
     * @return \Illuminate\Http\Response
     */
    public function destroy(AssetType $assetType)
    {
        try {
            $parentId = $assetType->parent;
            $assetType->delete();
            if($parentId > 0) {
                return redirect()->route('getSubAssetTypeByAsset',$parentId)->with('success',"Sub Assest type deleted successfully");
            }
            return redirect()->route('assetType.index')->with("success","Asset type deleted successfully");
        } catch (\Exception $e) {
            \Log::error("Getting error while deleting Asset type: ".$e);
            return redirect()->route('assetType.index')->with("error",$e->getMessage());
        }
    }
    
    /**
     * for get sub asset type
     *
     * @param  mixed $request
     * @return void
     */
    public function getSubAsset(Request $request)
    {
        if ($request->ajax()) {
            $assetTypes = AssetType::where('parent',$request->id)->with('parent');
            $user = auth()->user();
            return DataTables::of($assetTypes)
                ->addColumn('action', function($assetType) use($user) {
                    $class = 'delete-asset-type';
                    $attributes = [
                        "data-url" => route('assetType.destroy',$assetType->id)
                    ];
                    $btn = $deleteBtn = '';
                    if($user->can(config('perm.listSubAsset'))) {
                        $editUrl = route('assetType.edit',$assetType->id);
                        $btn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteSubAsset'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }

                    return $btn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.AssetType.SubAssetType.index');
    }

    public function getSubAssetFields(Request $request)
    {
        if(!empty($request->sub_category_id)) {
            $category = Category::find($request->sub_category_id);
            $categoryId = ($category) ? $category->parent : 0;
        } else {
            $categoryId = $request->input('category_id',0);
        }
        $assetType = AssetTypeField::where('asset_type_id',$request->asset_type_id)->where('category_id',$categoryId)->first();
        $data = [];
        if($assetType && !empty($assetType->selected_fields)) {
            $data = json_decode($assetType->selected_fields,true);
        }
        if($request->from == 'admin') {
            $view = view('Admin.AssetType.SubAssetType.entity_fields',['components' => $data])->render();
            $data = $view;
        }
        $responseData['status'] = 1;
        $responseData['message'] = 'Sub asset type fields fetched successfully';
        $responseData['data'] = $data;
        return response()->json($responseData, 200);
    }
    
    /**
     * for get sub asset type by asset type 
     *
     * @param  mixed $request
     * @return void
     */
    public function getSubAssetByAsset(Request $request)
    {
        if(!empty($request->assetName)) {
            $subAssetType = AssetType::whereHas('parent', function ($query) use ($request) {
                $query->where('name','LIKE',$request->assetName);
            })->get();
        } else {
            $parent = $request->assetId;
            $subAssetType = AssetType::where('parent',$parent)->get();
        }
        if($subAssetType->isEmpty()) {
            $responseData['status'] = 0;
            $responseData['message'] = trans('apimessages.norecordsfound');
        } else {
            $responseData['status'] = 1;
            $responseData['message'] = trans('apimessages.sub_asset_fetch_successfully');
        }
        $responseData['data'] =  $subAssetType;
        return response()->json($responseData, 200);
    }
}
