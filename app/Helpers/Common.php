<?php

use Illuminate\Support\Facades\Crypt;
use App\AssetType;
use App\Business;
use App\Category;
use App\Owners;
use App\Role;
use App\Settings;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

//use Image;

function supportNumber()
{
	$settings = Settings::where('key','support_number')->first();
	if($settings && !empty($settings->value)) {
		return '+91'.$settings->value;
	}
	return '+918758961006';
}

function supportEmail()
{
	$settings = Settings::where('key','support_email')->first();
	if($settings && !empty($settings->value)) {
		return $settings->value;
	}
	return 'rana@ryuva.club';
}

function thousandsCurrencyFormat($num)
{

    if ($num > 1000) {

        $x = round($num);
        $x_number_format = number_format($x);
        $x_array = explode(',', $x_number_format);
        $x_parts = array('k', 'm', 'b', 't');
        $x_count_parts = count($x_array) - 1;
        $x_display = $x;
        $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
        $x_display .= $x_parts[$x_count_parts - 1];

        return $x_display;
    }

    return (string) $num;
}

/**
 * getBtn
 *
 * @param  mixed $btnName
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getBtn($btnName = 'Save', $class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $deleteBtn = "<button
                        class='btn btn-xs btn-primary {$class}' 
                        {$attribute}>".$btnName.
                "</button>";
    return $deleteBtn;                
}

/**
 * getViewBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getViewBtn($url = '#', $class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $btn = "<a href='{$url}' class='mr5 {$class}' {$attribute}>
                <span data-toggle='tooltip' data-original-title='View' class='glyphicon glyphicon-eye-open'></span>
             </a>";
    return $btn;                
}

/**
 * getDeleteBtn
 *
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getDeleteBtn($url = '#', $class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $deleteBtn = "<a href='{$url}' class='mr5 {$class}' {$attribute}>
                <span data-toggle='tooltip' data-original-title='Delete' class='glyphicon glyphicon-remove'></span>
             </a>";
    return $deleteBtn;                
}

/**
 * getEditBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getEditBtn($url = '#',$class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $editBtn = "<a href='{$url}' class='mr5 {$class}' {$attribute}>
                    <span data-toggle='tooltip' data-original-title='Edit' class='glyphicon glyphicon-edit'></span>
                </a>";
    return $editBtn;                
}

/**
 * getApproveBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getApproveBtn($url = '#',$class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $btn = "<a href='{$url}' class='mr5 btn bg-green {$class}' {$attribute} data-toggle='tooltip' data-original-title='Approve'>
                <i class='fa fa-check'></i>
                </a>";
    return $btn;                
}

/**
 * getPendingBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getPendingBtn($url = '#',$class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $btn = "<a href='{$url}' class='mr5 btn btn-danger {$class}' {$attribute} data-toggle='tooltip' data-original-title='Pending'>
                <i class='fa fa-pause'></i>
                </a>";
    return $btn;                
}

/**
 * getRejectBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getRejectBtn($url = '#',$class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $btn = "<a href='{$url}' class='mr5 btn btn-danger {$class}' {$attribute} data-toggle='tooltip' data-original-title='Reject'>
                <i class='fa fa-close'></i>
                </a>";
    return $btn;                 
}

/**
 * getChildBtn
 *
 * @param  mixed $url
 * @param  mixed $class
 * @param  mixed $attributes
 * @return void
 */
function getChildBtn($url = '#',$class ='', $attributes= [])
{
    $attribute = '';
    if (is_array($attributes)) {
        foreach ($attributes as $key => $value) {
            $attribute .= $key.'="'.$value. '" ';
        }
    }
    $editBtn = "<a href='{$url}' class='mr5 {$class}' {$attribute}>
                    <span class='glyphicon glyphicon-log-out' data-toggle='tooltip' data-original-title='Sub Asset Type'></span>
                </a>";
    return $editBtn;                
}

function getFiltersDataInArray($jsonData)
{
    $filters = json_decode($jsonData);
    $data = [];
    foreach ($filters as $key => $value) {
        if(array_key_exists($value->name,$data)) {
            $dataValue = $data[$value->name]; 
            array_push($dataValue,$value->value);
            $data[$value->name] = $dataValue;
        } else {
            if(in_array($value->name,['gender','membership_type','meta_tags','age_groups','sender_type','notification_to'])) {             
                $data[$value->name] = $value->value;
            } else {
                $data[$value->name] = (!empty($value->value)) ? [$value->value] : [];
            }
        }
    }
    return $data;          
}

function getDistricts()
{
    $districts = Business::select('district')->distinct()->whereNotNull('district')->where('district','<>','')->orderBy('district')->get()->makeHidden(['categories']);
    return $districts;        
}

function getEducations()
{
    $educations = User::select('education')->distinct()->whereNotNull('education')->where('education','<>','')->orderBy('education')->get()->makeHidden(['profile_url']);
    return $educations;
}

function getCaste()
{
    $castes = Owners::select('kul_gotra')->distinct()->whereNotNull('kul_gotra')->where('kul_gotra','<>','')->orderBy('kul_gotra')->get()->makeHidden(['owner_image_name_url']);
    return $castes;
}

function getCategories()
{
    $category = Category::where('parent','0')->orderBy('name')->get();
    return $category;
}

function getEntities()
{
    $entity = AssetType::where('parent','0')->where('name','Entity')->with('childs')->first();
    return ($entity) ? $entity->childs : [];
}

/**
 * get roles
 *
 * @return object
 */
function getRoles($withSite = false,$type = null)
{
    $roles = Role::orderBy('name');
    if(!$withSite) {
        $roles->where('type','<>','site');
    }
    if($type) {
        $roles->where('type',$type);
    }
    return $roles->get();
}
/**
 * for get front-end url of entity detail page
 *
 * @param  mixed $entity
 * @return void
 */
function getUrlOfEntityDetail($entity)
{
    $url = config('constant.FRONT_END_URL');
    if($entity && $entity->is_normal_view) {
        $url .= 'home/business-detail/'.$entity->business_slug;
    } else if(isset($entity->entityType)) {
        $url .= strtolower($entity->entityType->name).'/'.$entity->business_slug;
    }
    return $url;
}

/**
 * delete file on cloude storage
 *
 * @param  mixed $path
 * @return void
 */
function deleteFile($path)
{
    try {
        $isDeleted = false;
        if(Storage::disk(config('constant.DISK'))->exists($path)) {
            if(Storage::disk(config('constant.DISK'))->delete($path)) {
                $isDeleted = true;
                info('file deleted:- '.$path);
            }
        }
        return $isDeleted;
    } catch (\Throwable $th) {
        Log::error('getting error while deleting file, path:- '.$path);
        Log::error($th);
        return false;
    }
}

/**
 * getImageLogo
 *
 * @param  mixed $url
 * @param  mixed $id
 * @return void
 */
function getImageLogo($url, $isBanner = false)
{
    $id = md5($url);
    $circleClass = (!$isBanner) ? 'img-circle' : '';
    $width = (!$isBanner) ? '50' : '100';
    $logo = "<img style='cursor: pointer;' data-toggle='modal' data-target='#{$id}' src='{$url}' width='{$width}' height='50' class='{$circleClass}'/>
    <div class='modal modal-centered fade image_modal' id='{$id}' role='dialog' style='vertical-align: center;'>
        <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content' style='background-color:transparent;'>
                <div class='modal-body'>
                <center>
                    <button type='button' class='close' data-dismiss='modal'>&times;</button>
                    <img src='{$url}' style='width:100%; border-radius:5px;'/>
                <center>
                </div>
            </div>
        </div>
    </div>";
    return $logo;
}

/**
 * for get setting value in array
 *
 * @param  mixed $key
 * @return array
 */
function getSettingsInArray($key)
{
    $data = [];
    $settings = Settings::where('key',$key)->first();
    if(!empty($settings->value)) {
        $data = explode(',', $settings->value);
    }
    return $data;
}

/**
 * for get standard permission array
 *
 * @return void
 */
function getStdPermission()
{
    $data = ['List','View','Add','Edit','Delete'];
    return $data;
}

/**
 * for get type of reason
 *
 * @return void
 */
function reasonTypes()
{
    $data = ['Entity','Post'];
    return $data;
}

/**
 * assetTypes
 *
 * @return void
 */
function assetTypes()
{
    $assetTypes = AssetType::where('parent',0)->get();
    return $assetTypes;
}

/**
 * assetTypes
 *
 * @return void
 */
function isEntity($parentId)
{
    $isEntity = AssetType::where('id',$parentId)->where('name','like','entity')->exists();
    return $isEntity;
}

/**
 * for get edit url of user
 *
 * @param  mixed $user
 * @return void
 */
function viewUserUrl($user)
{
    $name = '';
    if($user) {
        if(auth()->user()->can(config('perm.editUser'))) {
            $encryptId = Crypt::encrypt($user->id);
            $url = url('/admin/edituser/'.$encryptId);
            $name = "<a href='$url' target='_blank'> ".$user->name." </a>";
        } else {
            $name = $user->name;
        }
    }
    return $name;                                        
}

/**
 * for get register message
 *
 * @param  mixed $otp
 * @return void
 */
function getRegMsg($otp)
{
    $msg = "Dear User, {$otp} is the OTP for your account registration on MyRajasthan Club INCOPE";
    return $msg;
}

/**
 * for get reset password message
 *
 * @param  mixed $otp
 * @return void
 */
function getResetPwdMsg($otp)
{
    $msg = "Dear User, {$otp} is the OTP to reset your password in MyRajasthan Club INCOPE";
    return $msg;
}

// function imageResize($path, $with = 100, $height = 100)
// {
// 	$resizeImage = Image::make($path)->resize($with, $height)->encode();
// 	return (string) $resizeImage;
// }