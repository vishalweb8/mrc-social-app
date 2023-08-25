<?php

namespace App\Http\Controllers\Admin;

use App\Application;
use App\CallToAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables; 
use Config;
use Image;
use Helpers;
class CallToActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    { 
        $this->objApp = new Application();
        $this->ICON_IMAGE_PATH = Config::get('constant.ICON_IMAGE_PATH'); 
        $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT'); 
        $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH'); 
    }
    
    public function index(Request $request)
    { 
        if ($request->ajax()) {
            $callToActions = CallToAction::select('*')->has('application')->with('application');
            $user = auth()->user();
            return DataTables::of($callToActions)
                ->addColumn('action', function ($callToAction) use ($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-callToAction';
                    // $encriptId = Crypt::encrypt($callToAction->id);
                    $encriptId = $callToAction->id;
                    $attributes = [
                        "data-url" => route('calltoaction.destroy', $callToAction->id)
                    ];

                    if ($user->can(config('perm.editCallToAction'))) {
                        $editUrl = route('calltoaction.edit', $encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if ($user->can(config('perm.deleteCallToAction'))) {
                        $deleteBtn = getDeleteBtn("#", $class, $attributes);
                    }
                    return $editBtn . $deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.CalltoAction.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $callToAction = [];
        $applications = $this->objApp->getAll();
        return view('Admin.CallToAction.edit', compact("callToAction",'applications'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    public function store(Request $request)
    {
        if($request->id){
            $request->validate([
                'name' => 'required', 
                'target' => 'required',
                'placement' => 'required', 
                'application_id' => 'required'
            ]);
        }
        else{
            $request->validate([
                'name' => 'required',
                'icon' => 'required',
                'target' => 'required',
                'placement' => 'required', 
                'application_id' => 'required'
            ]);
        } 

        try {
            $postData = $request->all();
            $message = "Call to action saved successfully";
            if ($request->id) {
                $message = "Call to action  update successfully";
                $callToAction = CallToAction::firstOrNew(['id' =>  $request->id]);
            } else {
                $callToAction = new CallToAction();
            }
            $callToAction->name = $request->name;
            $callToAction->target = $request->target;
            $callToAction->placement = $request->placement;
            $callToAction->application_id = $request->application_id; 
            if ($request->icon) {
                $icon = $request->icon; 
                if (!empty($icon)) {
                    $fileName = 'icon_' . uniqid() . '.' . $icon->getClientOriginalExtension();

                    $pathThumb = (string) Image::make($icon->getRealPath())->resize($this->COUNTRY_FLAG_THUMBNAIL_IMAGE_WIDTH, $this->COUNTRY_FLAG_THUMBNAIL_IMAGE_HEIGHT)->encode();

                    $callToAction->icon = $fileName;

                    if (isset($postData['old_flag']) && $postData['old_flag'] != '') {
                        $thumbImageDelete = Helpers::deleteFileToStorage($postData['old_flag'], $this->ICON_IMAGE_PATH, "s3");
                    }
                    //Uploading on AWS
                    $thumbImage = Helpers::addFileToStorage($fileName, $this->ICON_IMAGE_PATH, $pathThumb, "s3");
                }
            }
            $callToAction->save();
            return redirect()->route('calltoaction.index')->with('success', $message);
        } catch (\Throwable $th) {
            Log::error('getting error while saving call To Action:- ' . $th);
            return redirect()->route('calltoaction.index')->with('error', $th->getMessage());
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
    public function edit(CallToAction $calltoaction)
    {  
        $applications = $this->objApp->getAll();
        return view('Admin.CallToAction.edit', compact("calltoaction",'applications'));
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
    public function destroy(CallToAction $calltoaction)
    {
        try {
            $calltoaction->delete();
            return redirect()->route('calltoaction.index')->with("success", "callToAction deleted successfully");
        } catch (\Throwable $th) {
            Log::error("Getting error while deleting reason: " . $th);
            return redirect()->route('calltoaction.index')->with("error", $th->getMessage());
        }
    }
}
