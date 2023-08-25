<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PublicWebsiteTemplets;
use CKSource\CKFinder\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicWebsiTetempletsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct()
    {

        $this->BUSINESS_ORIGINAL_IMAGE_PATH = Config::get('constant.BUSINESS_ORIGINAL_IMAGE_PATH');
        $this->BUSINESS_THUMBNAIL_IMAGE_PATH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_PATH');
        $this->BUSINESS_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_WIDTH');
        $this->BUSINESS_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_THUMBNAIL_IMAGE_HEIGHT');

        $this->PUBLIC_WEBSITE_ORIGINAL_IMAGE = Config::get('constant.PUBLIC_WEBSITE_ORIGINAL_IMAGE');
        $this->PUBLIC_WEBSITE_THUMBNAIL_IMAGE = Config::get('constant.PUBLIC_WEBSITE_THUMBNAIL_IMAGE');
        $this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_WIDTH');

        $this->BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.BUSINESS_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.BUSINESS_LOGO_THUMBNAIL_IMAGE_WIDTH');

        
        $this->USER_ORIGINAL_IMAGE_PATH = Config::get('constant.USER_ORIGINAL_IMAGE_PATH');
        $this->USER_THUMBNAIL_IMAGE_PATH = Config::get('constant.USER_THUMBNAIL_IMAGE_PATH');
        $this->USER_PROFILE_PIC_WIDTH = Config::get('constant.USER_PROFILE_PIC_WIDTH');
        $this->USER_PROFILE_PIC_HEIGHT = Config::get('constant.USER_PROFILE_PIC_HEIGHT');
        
        $this->OWNER_ORIGINAL_IMAGE_PATH = Config::get('constant.OWNER_ORIGINAL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_PATH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_PATH');
        $this->OWNER_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.OWNER_THUMBNAIL_IMAGE_WIDTH');
        $this->OWNER_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.OWNER_THUMBNAIL_IMAGE_HEIGHT');
        
           
    }

    public function index()
    {
        $PublicWebsiteTemplets = PublicWebsiteTemplets::get();
        return view('Admin.ListPublicWebsiTetemplets',compact('PublicWebsiteTemplets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.AddPublicWebsiTetemplets');
    }

    public function status(Request $request){
        // return $request->all();
        if ($request->status == 0) {
          $status = 0;
          $message = "Public Website Templet Deactive";
        } else {
          $status = 1;
          $message = "Public Website Templet Active";
        }

        $PublicWebsiteTemplets = PublicWebsiteTemplets::findOrFail($request->id);
        $PublicWebsiteTemplets->status = $status;
        $PublicWebsiteTemplets->save();

        
            return response()->json([
            'status'   => 'Successfully',
            'message'  => $message
          ], 200);
       
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
         $request->validate([
                'template_name' => 'required|unique:public_website_templets',
                'template_html' => 'required',
                'template_theme' => 'required',
                'preview_image' => 'required',
            ]);
            try {
                DB::beginTransaction();
                $PublicWebsiteTemplets = new PublicWebsiteTemplets;
                $PublicWebsiteTemplets->template_name = $request->template_name;
                $PublicWebsiteTemplets->template_html = $request->template_html;
                $PublicWebsiteTemplets->template_theme = $request->template_theme;
                if ($request->preview_image) 
                {  
                    $logo = $request->preview_image; 

                    if (!empty($logo)) 
                    {
                        $fileName = 'preview_image' . uniqid() . '.' . $logo->getClientOriginalExtension();
                        $pathThumb = (string) \Image::make($logo->getRealPath())->resize($this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_WIDTH, $this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_HEIGHT)->encode();
                        
                        //Uploading on AWS
                        $originalImage = \Helpers::addFileToStorage($fileName, $this->PUBLIC_WEBSITE_ORIGINAL_IMAGE, $logo, "s3");
                        $thumbImage = \Helpers::addFileToStorage($fileName, $this->PUBLIC_WEBSITE_THUMBNAIL_IMAGE, $pathThumb, "s3");

                        $PublicWebsiteTemplets['preview_image'] = $fileName;
                    }
                  }
                $PublicWebsiteTemplets->save();
                DB::commit();

            return redirect()->route('PublicWebsiteTetemplets.list')->with('success', trans('Public Website Templets Save Successfully'));
            } catch (\Exception $e) {
                dd($e);
                return response()->json([
                'message' => 'Error'
                ],500);
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
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $PublicWebsiteTemplets = PublicWebsiteTemplets::find($id);
        return view('Admin.EditPublicWebsiteTemplets',compact('PublicWebsiteTemplets'));
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
        $request->validate([
            'template_name' => [
                        'required',
                        Rule::unique('public_website_templets')->ignore($id),
                    ],
                // 'template_name' => 'required|unique:public_website_templets',
                'template_html' => 'required',
                'template_theme' => 'required',
            ]);
        $PublicWebsiteTemplets = PublicWebsiteTemplets::find($id);
                $PublicWebsiteTemplets->template_name = $request->template_name;
                $PublicWebsiteTemplets->template_html = $request->template_html;
                $PublicWebsiteTemplets->template_theme = $request->template_theme;
                if ($request->preview_image) 
                {  
                    $logo = $request->preview_image; 

                    if (!empty($logo)) 
                    {
                        $fileName = 'preview_image' . uniqid() . '.' . $logo->getClientOriginalExtension();
                        $pathThumb = (string) \Image::make($logo->getRealPath())->resize($this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_WIDTH, $this->PUBLIC_WEBSITE_LOGO_THUMBNAIL_IMAGE_HEIGHT)->encode();
                        
                        //Uploading on AWS
                         $originalImage = \Helpers::addFileToStorage($fileName, $this->PUBLIC_WEBSITE_ORIGINAL_IMAGE, $logo, "s3");
                        $thumbImage = \Helpers::addFileToStorage($fileName, $this->PUBLIC_WEBSITE_THUMBNAIL_IMAGE, $pathThumb, "s3");

                        $PublicWebsiteTemplets['preview_image'] = $fileName;
                    }
                  }
                $PublicWebsiteTemplets->update();
    return redirect()->route('PublicWebsiteTetemplets.list')->with('success', trans('Public Website Templets Update Successfully'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
         $destroyPublicWebsiteTetemplets = PublicWebsiteTemplets::find($id);
         $destroyPublicWebsiteTetemplets->delete();

          return redirect()->route('PublicWebsiteTetemplets.list')->with('success', trans('Public Website Templets Delete Successfully'));
    }

	public function getTheme(Request $request)
	{
		$template = PublicWebsiteTemplets::find($request->template_id);
		if($template) {
			$themes = explode(',',$template->template_theme);
			$options = '';
			foreach($themes as $theme) {
				$theme = trim($theme);
				$options .= '<option value="'.$theme.'">'.$theme. '</option>';
			}
			return response()->json(['status'=> true, 'data' => $options]);
		} else {
			return response()->json(['status'=> false, 'message' => 'Template not found']);
		}
	}
}
