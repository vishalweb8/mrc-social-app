<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Cms;
use App\TempSearchTerm;
use App\Branding;
use App\Business;
use App\BrandingList;
use App\Http\Requests\CmsRequest;
use Auth;
use Input;
use Config;
use Redirect;
use Crypt;
use Image;
use File;
use DB;
use Illuminate\Contracts\Encryption\DecryptException;

class CmsController extends Controller
{
    public function __construct()
    { 
        $this->middleware('auth');
        $this->objCms = new Cms();
        $this->objBranding = new Branding();
        $this->objBrandingList = new BrandingList();
    }

    public function index()
    {
        $cmsList = $this->objCms->getAll();
        return view('Admin.ListCms', compact('cmsList'));
    }

    public function add()
    {
        $data = [];
        return view('Admin.EditCms', compact('data'));
    }

    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $data = $this->objCms->find($id);
            if($data) {
                return view('Admin.EditCms', compact('data'));
            } else {
                return Redirect::to("admin/cms")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            return view('errors.404');
        }
        
    }

    public function save(CmsRequest $request)
    {
        $postData = Input::all();
        unset($postData['_token']);
        $response = $this->objCms->insertUpdate($postData);
        if ($response) {
            return Redirect::to("admin/cms")->with('success', trans('labels.cmssuccessmsg'));
        } else {
            return Redirect::to("admin/cms")->with('error', trans('labels.cmserrormsg'));
        }
    }

    public function delete($id)
    {
        $data = $this->objCms->find($id);
        $response = $data->delete();
        if ($response) {
            return Redirect::to("admin/cms")->with('success', trans('labels.cmsdeletesuccessmsg'));
        }
    }

    public function brandingImage()
    {
        $postData =[];
        $postData = Input::all();
        $business =array();
        if(isset($postData['_token']))
        {
            $category =  $postData['category'];

            $business =  DB::table('business')
                ->where('name','like','%'.$postData['name'].'%')
                ->Where('country',$postData['country'])
                ->Where('state',$postData['state'])
                ->Where('city',$postData['city'])
                ->where('approved',1)
                ->when($category, function ($query) use ($category) {
                            return $query->where('parent_category','like','%'. $category.'%');
                        })
                ->where('deleted_at',null);
            if(!Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
                $business->whereRaw(Auth::user()->sql_query);
            }
            $business = $business->get();
        }
        $country =  DB::table('country')->get();
        $state =  DB::table('state')->get();
        $city =  DB::table('city')->get();
        $categories =  DB::table('categories')->where('parent','0')->get();

        $brandingDetail = $this->objBranding->first();
        return view('Admin.Branding',compact('brandingDetail','country','categories','state','city','business','postData'));
    }
    public function brandingsave()
    {
        $postData = Input::get();

        $brandingArr = [];
        $brandingArr['group_title'] = $postData['group_title'];
        $collection = '';
        $i = 0;
        foreach ($postData['business_id'] as $key => $value) {
            if($i == 0){
                 $collection .= $value;
            }else{
                 $collection .= ','.$value;

            }
           

            $i++;
        }
         $brandingArr['business_id'] =   $collection;

       $this->objBrandingList->create($brandingArr);
        return Redirect::to('admin/branding')->with('success', trans('labels.brandingsavesuccessmsg'));
    }
    public function savebrandingImage()
    {
        $postData = Input::all();

        $brandingArray = [];

        if($postData['type'] == 1)
        {
            if (Input::file())
            {
                $file = Input::file('image');
                if (isset($file) && !empty($file))
                {
                    $fileName = 'branding_image.png';
                    $pathOriginal = public_path('images/'. $fileName);
                    Image::make($file->getRealPath())->save($pathOriginal);
                    $brandingArray['name'] = $fileName;
                    $brandingArray['type'] = 1;
                }
            }
        }

        if($postData['type'] == 2)
        {
            $brandingArray['name'] = $postData['video'];
            $brandingArray['type'] = 2; 
        }

        if($postData['type'] == 3)
        {
            $brandingArray['name'] = $postData['text'];
            $brandingArray['type'] = 3; 
        }

        $brandingArray['business_id'] = null;
        if(isset($postData['business_id']) && $postData['business_id'] != '')
        {
            $brandingArray['business_id'] = $postData['business_id'];
        }
        $brandingArray['page_name'] = $postData['page_name'];
        $brandingDetail = $this->objBranding->first();
        if($brandingDetail)
        {
            $this->objBranding->where('id',$brandingDetail->id)->update($brandingArray);
        }
        else
        {
            $this->objBranding->create($brandingArray);
        }

        return Redirect::to('admin/branding')->with('success', trans('labels.brandingsavesuccessmsg'));
    }

    public function deletebrandingImage()
    {
        DB::table('branding')->truncate();

        if (file_exists('images/branding_image.png')) 
        {
           \File::delete('images/branding_image.png');
        }
        return Redirect::to('admin/branding')->with('success', trans('labels.brandingdeletesuccessmsg'));
    }

    public function getSearchTerm()
    {
        $searchTermList = TempSearchTerm::with('user')->latest()->limit(2000)->get();
        return view('Admin.ListSearchTerms',compact('searchTermList'));
    }

    public function autoCompleteBusiness()
    {
        $postData = request()->all();

        $query = Business::where('approved', '=', 1)->where('name','<>','');

        if(Auth::check() && !Auth::user()->isSuperAdmin() && !empty(Auth::user()->sql_query)) {
            $query->whereRaw(Auth::user()->sql_query);
        }

        if(isset($postData['ids']) && !empty($postData['ids'])) {
            $query->whereIn('id',$postData['ids']);
        }

        if(isset($postData['q']) && $postData['q'] != '') {
            $postData['q'] = strtolower($postData['q']);
            $query->where(DB::raw('LOWER(name)'), 'LIKE', $postData['q']."%");
        }

        $businessList = $query->orderBy('name')
                            ->limit(10)
                            ->get(['id', DB::raw('name AS text')]);

        return response()->json($businessList, 200);
    }
}
