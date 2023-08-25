<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SubCategoryRequest;
use Auth;
use Helpers;
use Config;
use Image;
use File;
use DB;
use Input;
use Redirect;
use App\Category;
use App\Business;
use Crypt;
use Response;
use Carbon\Carbon;
use Mail;
use Session;
use Cache;
use Log;
use Illuminate\Contracts\Encryption\DecryptException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objCategory = new Category();
        $this->objBusiness = new Business();

        $this->categoryLogoOriginalImagePath = Config::get('constant.CATEGORY_LOGO_ORIGINAL_IMAGE_PATH');
        $this->categoryLogoThumbImagePath = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_PATH');
        $this->categoryLogoThumbImageHeight = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_HEIGHT');
        $this->categoryLogoThumbImageWidth = Config::get('constant.CATEGORY_LOGO_THUMBNAIL_IMAGE_WIDTH');

        $this->categoryBannerImagePath = Config::get('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH');
        
        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('category-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));

        $this->controller = 'CategoryController';
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::select('categories.*',\DB::raw('(select count(business.id) from business where deleted_at is NULL and FIND_IN_SET(categories.id, business.category_id)) as business_count'))->where('parent',0);
            $user = auth()->user();
            return DataTables::of($categories)
                ->editColumn('category_logo', function($category) {
                    $logo = getImageLogo($category->category_logo);
                    return $logo;
                })
                ->editColumn('banner_img', function($category) {
                    $logo = '';
                    if(!empty($category->banner_img)  && \Storage::disk(config('constant.DISK'))->exists(config('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$category->banner_img)) {
                        $url = \Storage::disk(config('constant.DISK'))->url(config('constant.CATEGORY_BANNER_ORIGINAL_IMAGE_PATH').$category->banner_img);
                        $logo = getImageLogo($url,true);
                    }
                    return $logo;
                })
                ->editColumn('business_count', function($category) {
                    $label = $category->business_count;
                    if($category->business_count > 0) {
                        $label = '<a href="'.route('getAllBusinessesByCategory',Crypt::encrypt($category->id)).'" target="_blank">'.$category->business_count.'
                    </a>';
                    }
                    return $label;
                })
                ->addColumn('action', function($category) use($user) {
                    $class = $editBtn = $deleteBtn = $childBtn = '';
                    $encriptId = Crypt::encrypt($category->id);
                    if($user->can(config('perm.editCategory'))) {
                        
                        $editUrl = route('category.edit',$encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteCategory'))) {
                        $confirmMessage = trans("labels.confirmdeletemsg");
                        $attributes = [
                            "onclick" => "return confirm({$confirmMessage})"
                        ];
                        $url = route('category.destroy',$encriptId);
                        $deleteBtn = getDeleteBtn($url,$class, $attributes);
                    }
                    if($user->can(config('perm.listSubCategory'))) {
                        $url = route('get.child.category',$encriptId);
                        $childBtn = getChildBtn($url);
                    }

                    return $editBtn.$deleteBtn.$childBtn;
                })
                ->rawColumns(['category_logo','business_count','banner_img','action'])
                ->make(true);
        }
        return view('Admin.Category.index');
    }

    public function create()
    {
        $categories = [];
        $categories = $this->objCategory->get();
        $this->log->info('Admin Category add page', array('user_id' => Auth::id()));
        return view('Admin.EditCategory', compact('categories'));
    }

    public function save(CategoryRequest $CategoryRequest)
    {
        $requestData = [];
        $postData = $CategoryRequest->all();
        $requestData['id'] = e(input::get('id'));
        $id = $requestData['id'];
        $parentId = e(input::get('parent'));
        $requestData['parent'] = (isset($parentId) && !empty($parentId) && $parentId != 0) ? $parentId : 0;
        $requestData['name'] = input::get('name');
//      $categorySlug = e(input::get('category_slug'));
//      $requestData['category_slug'] = (isset($categorySlug) && !empty($categorySlug)) ? $categorySlug : NULL;
        if($id == 0 || !isset($id))
        {
            $categorySlug = SlugService::createSlug(Category::class, 'category_slug', $requestData['name']);
            $requestData['category_slug'] = (isset($categorySlug) && !empty($categorySlug)) ? $categorySlug : NULL;
        } 
        $requestData['metatags'] = e(input::get('metatags'));
        if(isset($postData['service_type'])) {
            $requestData['service_type'] = 1;
        }else{
            $requestData['service_type'] = 0;
        }
        $parentId = 0;
        if(isset($postData['action']) && $postData['action'] == 1)
        {
            $parentId = $postData['parent'];
            $requestData['parent'] = $parentId;
        }
        $hiddenCatLogo = e(Input::get('hidden_cat_logo'));
        $requestData['cat_logo'] = $hiddenCatLogo;
        if (Input::file())
        {
            $file = Input::file('cat_logo');
            if (isset($file) && !empty($file))
            {
                $fileName = 'catgory_logo_' . time() . '.' . $file->getClientOriginalExtension();
                
                $imgTumb =(string) Image::make($file->getRealPath())->resize($this->categoryLogoThumbImageWidth, $this->categoryLogoThumbImageHeight)->encode();

                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->categoryLogoOriginalImagePath, $file, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->categoryLogoThumbImagePath,$imgTumb, "s3");
                
                if ($hiddenCatLogo != '')
                {
                    $this->log->info('Admin category logo original and thumb image deleted successfully', array('user_id' => Auth::id(),'category_id' => $id, 'logoName' => $hiddenCatLogo));
                    Helpers::deleteFileToStorage($hiddenCatLogo, $this->categoryLogoOriginalImagePath, "s3");
                    Helpers::deleteFileToStorage($hiddenCatLogo, $this->categoryLogoThumbImagePath, "s3");
                }
                $requestData['cat_logo'] = $fileName;
            }
        }

        $hiddenBannerImg = e(Input::get('hidden_banner_img'));
        $requestData['banner_img'] = $hiddenBannerImg;
        if (Input::file())
        {
            $fileBanner = Input::file('banner_img');
            if (isset($fileBanner) && !empty($fileBanner))
            {
                $fileBannerName = 'catgory_banner_img_' . time() . '.' . $fileBanner->getClientOriginalExtension();

                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileBannerName, $this->categoryBannerImagePath, $fileBanner, "s3");

                if ($hiddenBannerImg != '')
                {
                    $this->log->info('Admin category Banner Image deleted successfully', array('user_id' => Auth::id(),'category_id' => $id, 'bannerName' => $hiddenBannerImg));
                    Helpers::deleteFileToStorage($hiddenBannerImg, $this->categoryBannerImagePath, "s3");
                }
                $requestData['banner_img'] = $fileBannerName;
            }
        }

        $response = $this->objCategory->insertUpdate($requestData);
        $category_id = (isset($response->id) && $response->id > 0) ? $response->id : $id;
        if ($response)
        {
            $this->log->info('Admin Category added/updated successfully', array('user_id' => Auth::id(), 'category_id' => $category_id));
            if($parentId == 0)
            {
                return Redirect::to("admin/categories")->with('success', trans('labels.categorysavesuccessmsg'));
            }
            else
            {
                return Redirect::to("admin/category/subcategories/".Crypt::encrypt($parentId))->with('success', 'Category saved successfully');
            }
            
        }
        else
        {
            $this->log->error('Admin something went wrong while adding/updating category', array('user_id' => Auth::id(), 'category_id' => $category_id));
            return Redirect::to("admin/categories")->with('error', trans('labels.common_error'));
        }
    }

    public function edit($id)
    {
        try 
        {
            $data = Category::find(Crypt::decrypt($id));
            $id = Crypt::decrypt($id);
            $categories = [];
            $categories = $this->objCategory->get();
            $childHirarchyCategory = Helpers::getCategorySubHierarchy($id); 
            $childHirarchyCategory[] = $id;
            $this->log->info('Admin Category edit page', array('user_id' =>  Auth::id(), 'category_id' => $id));
            if($data) 
            {
                return view('Admin.EditCategory', compact('data', 'categories','childHirarchyCategory'));
            } 
            else 
            {
                return Redirect::to("admin/categories")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            $this->log->error('Admin something went wrong while edit category', array('user_id' =>  Auth::id(), 'category_id' => $id, 'error' => $e->getMessage()));
            return view('errors.404');
        }
       
    }

    public function delete($id)
    {
        $id = Crypt::decrypt($id);
        $categoryData = Category::find($id);
        $categoryExistInBusiness = $this->objCategory->categoryHasBusinesses($id);
        
        if(count($categoryExistInBusiness) == 0)
        {
            $parentId = 0;
            $childAssignParent =  $this->objCategory->childAssignParent($parentId, $id);
            if($childAssignParent == 1)
            {
                $response = $categoryData->delete();
                Cache::forget('apiTrendingCategories');
                Cache::forget('getTrendingServices');
                Cache::forget('apiTrendingServices');
                if ($response)
                {
                    $this->log->info('Admin category deleted successfully', array('user_id' => Auth::id(),'category_id' => $id));
                    return Redirect::to("admin/categories")->with('success', trans('labels.ctdeletesuccessmsg'));
                }
                else
                {
                    $this->log->error('Admin something went wrong while deleting category', array('user_id' => Auth::id(),'category_id' => $id));
                    return Redirect::to("admin/categories")->with('error', trans('labels.common_error'));
                }
            }
            else
            {
                 $this->log->error('Admin something went wrong while deleting category', array('user_id' => Auth::id(),'category_id' => $id));
                return Redirect::to("admin/categories")->with('error', trans('labels.common_error'));
            }
        }
        else
        {
             $this->log->error('Admin something went wrong while deleting category', array('user_id' => Auth::id(),'category_id' => $id));
            return Redirect::to("admin/categories")->with('error', trans('labels.category_exist_in_business'));
        }
    }

    public function subCategoriesListing($parentId)
    {   
        try 
        {
            $parentId = Crypt::decrypt($parentId);
            $reverseCategoryHierarchy = Helpers::getCategoryReverseHierarchy($parentId);
            $reverseCategoryHierarchy = array_reverse($reverseCategoryHierarchy);

            $parentCategoryObj = Category::find($parentId);
            $sort = [];
            $sort['parent'] = $parentId;
            $subCategories = $this->objCategory->getAll($sort);
            
            $this->log->info('Admin sub categories listing page', array('user_id' => Auth::id(), 'parent_category_id' => $parentId));
            return view('Admin.ListSubCategory', compact('subCategories', 'parentId', 'parentCategoryObj','reverseCategoryHierarchy'));
            
        } catch (DecryptException $e) {
            $this->log->error('Admin something went wrong while sub categories listing page', array('user_id' =>  Auth::id(), 'parent_category_id' => $parentId, 'error' => $e->getMessage()));
            return view('errors.404');
        }
        
    }

    public function addSubcategory($parentId)
    {
        $reverseCategoryHierarchy = Helpers::getCategoryReverseHierarchy(Crypt::decrypt($parentId));
        $reverseCategoryHierarchy = array_reverse($reverseCategoryHierarchy);

        $categories = $this->objCategory->get();
    
        $this->log->info('Admin sub category add page', array('user_id' => Auth::id(), 'parent_category_id' => $parentId));
        return view('Admin.EditSubCategory', compact('parentId','reverseCategoryHierarchy','categories'));
    }

    public function saveSubcategory(SubCategoryRequest $SubCategoryRequest, $enrpId)
    {
        $parentId = Crypt::decrypt($enrpId);
        $postData = $SubCategoryRequest->all();
        if(isset($postData['action']) && $postData['action'] == 1)
        {
            $parentId = $postData['parent_id'];
            if($parentId == 0)
            {
                $enrpId = 0;
            }
            else
            {
                $enrpId = Crypt::encrypt($parentId);    
            }
            
        }
        $requestData = [];
        $requestData['id'] = e(input::get('id'));
        $id = $requestData['id'];
        $requestData['parent'] = $parentId;
        $requestData['name'] = input::get('name');
//      $requestData['category_slug'] = e(input::get('category_slug'));
        if($id == 0 || !isset($id))
        {
            $categorySlug = SlugService::createSlug(Category::class, 'category_slug', $requestData['name']);
            $requestData['category_slug'] = (isset($categorySlug) && !empty($categorySlug)) ? $categorySlug : NULL;
        } 
        $requestData['metatags'] = e(input::get('metatags'));
        if(isset($postData['service_type'])) {
            $requestData['service_type'] = 1;
        }else{
            $requestData['service_type'] = 0;
        }

        $hiddenCatLogo = e(Input::get('hidden_cat_logo'));
        $requestData['cat_logo'] = $hiddenCatLogo;
        if (Input::file())
        {
            $file = Input::file('cat_logo');
            if (isset($file) && !empty($file))
            {
                $fileName = 'catgory_logo_' . time() . '.' . $file->getClientOriginalExtension();
                
                $pathThumb = (string) Image::make($file->getRealPath())->resize($this->categoryLogoThumbImageWidth, $this->categoryLogoThumbImageHeight)->encode();

                //Uploading on AWS
                $originalImage = Helpers::addFileToStorage($fileName, $this->categoryLogoOriginalImagePath, $file, "s3");
                $thumbImage = Helpers::addFileToStorage($fileName, $this->categoryLogoThumbImagePath, $pathThumb, "s3");

                if ($hiddenCatLogo != '')
                {
                    $this->log->info('Admin sub category logo original and thumb image deleted successfully', array('user_id' => Auth::id(),'category_id' => $id, 'logoName' => $hiddenCatLogo));
                    Helpers::deleteFileToStorage($hiddenCatLogo, $this->categoryLogoOriginalImagePath, "s3");
                    Helpers::deleteFileToStorage($hiddenCatLogo, $this->categoryLogoThumbImagePath, "s3");
                }
                $requestData['cat_logo'] = $fileName;
            }
        }
        

        $response = $this->objCategory->insertUpdate($requestData);
        $category_id = (isset($response->id) && $response->id > 0) ? $response->id : $id;
        $getCategory = $this->objCategory->find($category_id);
        if ($getCategory)
        {  
            $this->log->info('Admin sub category added/updated successfully', array('user_id' => Auth::id(), 'category_id' => $category_id));
            $subId = Crypt::decrypt($enrpId);

            // start update category hieararchy and  parent category id
            if(isset($postData['action']) && $postData['action'] == 1)
            {    
                $businesslisting = $this->objBusiness->getBusinessListingByCategoryId($category_id);
                if(count($businesslisting) > 0)
                {
                    foreach($businesslisting as $business)
                    {
                        $businessData['category_hierarchy'] = Helpers::getCategoryHierarchy($business->category_id);
                        $businessData['parent_category'] = Helpers::getParentCategoryIds($business->category_id);
                        $businessData['id'] = $business->id;

                        $this->objBusiness->insertUpdate($businessData);
                    }
                    
                }
            }
            // stop update category hieararchy and  parent category id

            if($subId == 0)
            {
                return Redirect::to("admin/categories")->with('success', 'Category saved successfully');
            }
            else
            {
                return Redirect::to("admin/category/subcategories/".$enrpId)->with('success', 'Category saved successfully');
            }
        }
        else
        { 
            $this->log->error('Admin something went wrong while adding/updating sub-category', array('user_id' => Auth::id(), 'category_id' => $category_id));
            return Redirect::to("admin/category/subcategories/".$enrpId)->with('error', trans('labels.common_error'));
        }
    }

    public function editSubcategory($parentId, $enrId)
    {
        $id = Crypt::decrypt($enrId);
        $data = Category::find($id);
        $categories = $this->objCategory->get();

        $reverseCategoryHierarchy = Helpers::getCategoryReverseHierarchy(Crypt::decrypt($parentId));
        $reverseCategoryHierarchy = array_reverse($reverseCategoryHierarchy);

        $childHirarchyCategory = Helpers::getCategorySubHierarchy($id); 
        $childHirarchyCategory[] = $id;

        if($data) 
        {
            $this->log->info('Admin sub-category edit page', array('user_id' =>  Auth::id(), 'category_id' => $id));
            return view('Admin.EditSubCategory', compact('data', 'parentId','reverseCategoryHierarchy','categories','childHirarchyCategory'));
        } 
        else 
        {
            $this->log->error('Admin something went wrong while edit sub-category', array('user_id' =>  Auth::id(), 'category_id' => $id));
            return Redirect::to("admin/category/subcategories/".$parentId)->with('error', trans('labels.recordnotexist'));
        }
    }

    public function deleteSubcategory($enrpId, $enrId)
    {
        $id = Crypt::decrypt($enrId);
        $user = Category::find($id);
        $parentId = Crypt::decrypt($enrpId);

        $subChildAssignParent =  $this->objCategory->childAssignParent($parentId, $id);
        if($subChildAssignParent == 1)
        {
            $response = $user->delete();
            Cache::forget('getTrendingServices');
            if ($response)
            {
                $this->log->info('Admin sub-category deleted successfully', array('user_id' => Auth::id(),'category_id' => $id));
                return Redirect::to("admin/category/subcategories/".$enrpId)->with('success', trans('labels.ctdeletesuccessmsg'));
            }
            else
            {
                $this->log->error('Admin something went wrong while deleting sub-category', array('user_id' => Auth::id(),'category_id' => $id));
                return Redirect::to("admin/category/subcategories/".$enrpId)->with('error', trans('labels.common_error'));
            }
        }
        else
        {
            $this->log->error('Admin something went wrong while deleting sub-category', array('user_id' => Auth::id(),'category_id' => $id));
            return Redirect::to("admin/category/subcategories/".$enrpId)->with('error', trans('labels.common_error'));
        }
    }

//  Trending Services

    public function getAllTrendingServices(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::select('categories.*',\DB::raw('(select count(business.id) from business where deleted_at is NULL and FIND_IN_SET(categories.id, business.category_id)) as business_count'))->where('service_type',1);

            return DataTables::of($categories)
                ->editColumn('trending_service', function($category) {
                    $trending = '-';
                    if($category->trending_service) {
                        $trending = '<span class="label label-success">Trending</span>';
                    }
                    return $trending;
                })
                ->editColumn('business_count', function($category) {
                    $label = $category->business_count;
                    if($category->business_count > 0) {
                        $label = '<a href="'.route('getAllBusinessesByCategory',Crypt::encrypt($category->id)).'" target="_blank">'.$category->business_count.'
                    </a>';
                    }
                    return $label;
                })
                ->addColumn('action', function($category) {
                    if(!auth()->user()->can(config('perm.updateStatusTrendingService'))) {
                        return;
                    }
                    $isTrending = '';
                    if($category->trending_service) {
                        $isTrending = 'checked';
                    }
                    $btn = '<div class="checkbox"> <label>
                        <input type="checkbox" name="trendingCategories[]" id="trendingService_'.$category->id.'" onclick="updateTrendingService(this,this.value)" value="'.$category->id.'" '.$isTrending.'> </label></div>';
                    return $btn;
                })
                ->rawColumns(['trending_service','business_count','action'])
                ->make(true);
        }
        return view('Admin.Category.service');
    }

    public function getAllTrendingCategory(Request $request)
    {
        if ($request->ajax()) {
            $categories = Category::select('categories.*',\DB::raw('(select count(business.id) from business where deleted_at is NULL and FIND_IN_SET(categories.id, business.category_id)) as business_count'))->with('parentCatData');

            return DataTables::of($categories)
                ->editColumn('name', function($category) {
                    $name = $category->name;
                    if(!empty($category->parentCatData)) {
                        $name = $category->parentCatData->name.' - '.$category->name;
                    }
                    return $name;
                })
                ->editColumn('trending_category', function($category) {
                    $trending = '-';
                    if($category->trending_category) {
                        $trending = '<span class="label label-success">Trending</span>';
                    }
                    return $trending;
                })
                ->editColumn('business_count', function($category) {
                    $label = $category->business_count;
                    if($category->business_count > 0) {
                        $label = '<a href="'.route('getAllBusinessesByCategory',Crypt::encrypt($category->id)).'" target="_blank">'.$category->business_count.'
                    </a>';
                    }
                    return $label;
                })
                ->addColumn('action', function($category) {
                    if(!auth()->user()->can(config('perm.updateStatusTrending'))) {
                        return;
                    }
                    $isTrending = '';
                    if($category->trending_category) {
                        $isTrending = 'checked';
                    }
                    $btn = '<div class="checkbox"> <label>
                        <input type="checkbox" name="trendingCategories[]" id="trendingCategory_'.$category->id.'" onclick="updateTrendingCategory(this,this.value)" value="'.$category->id.'" '.$isTrending.'> </label></div>';
                    return $btn;
                })
                ->rawColumns(['trending_category','business_count','action'])
                ->make(true);
        }
        return view('Admin.Category.trending');
    }

    public function updateTrendingService(Request $request)
    {
        if(isset($request->categoryId) && isset($request->trendingService))
        {
            $requestData['id'] = $request->categoryId;
            $requestData['trending_service'] = $request->trendingService;
            $response = $this->objCategory->insertUpdate($requestData);
            echo ($response) ? 1 : 0;
        }
        else
        {
            $response = '';
            echo 0;
        }
//      return $response;
    }

    public function updateTrendingCategory(Request $request)
    {
        if(isset($request->categoryId) && isset($request->trendingCategory))
        {
            $requestData['id'] = $request->categoryId;
            $requestData['trending_category'] = $request->trendingCategory;
            $response = $this->objCategory->insertUpdate($requestData);
            echo ($response) ? 1 : 0;
        }
        else
        {
           $response = '';
           echo 0;
        }
//      return $response;
    }
}
