<?php

namespace App\Http\Controllers\Admin;

use App\AssetType;
use App\Business;
use App\EntityCustomField;
use App\EntityDescriptionLanguage;
use App\EntityKnowMore;
use Illuminate\Http\Request;
use App\Http\Requests\SubscriptionRequest;
use Auth;
use Input;
use Config;
use Redirect;
use App\SubscriptionPlan;
use App\MembershipRequest;
use App\Http\Controllers\Controller;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Image;
use File;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Helpers;
use App\User;
use App\UserRole;
use Illuminate\Support\Facades\DB;

class CsvImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objSubscription = new SubscriptionPlan();
        $this->SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH = Config::get('constant.SUBSCRIPTION_PLAN_ORIGINAL_IMAGE_PATH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_PATH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_WIDTH');
        $this->SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT = Config::get('constant.SUBSCRIPTION_PLAN_THUMBNAIL_IMAGE_HEIGHT');
        $this->log = new Logger('agent-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));
    }
    public function index()
    {
        $pendingRecord = 0;
        $notInsert = 0;
        $insertRecord = 0;
        return view('Admin.CsvImport', compact('pendingRecord','notInsert','insertRecord'));
    }
    
    /**
     * currently this function is not in use
     *
     * @param  mixed $request
     * @return void
     */
    public function save(Request $request)
    {

       if ($request->file('file') != null ){

          $file = $request->file('file');

          // File Details 
          $filename = $file->getClientOriginalName();
          $extension = $file->getClientOriginalExtension();
          $tempPath = $file->getRealPath();
          $fileSize = $file->getSize();
          $mimeType = $file->getMimeType();

          // Valid File Extensions
          $valid_extension = array("csv");

          // 2MB in Bytes
          $maxFileSize = 20971520; 

          // Check file extension
                $insertRecord = 0;
                $notInsert = 0;
                $pendingRecord = array();
          if($extension === "csv"){
                  if(in_array(strtolower($extension),$valid_extension))
                  {

                    // Check file size
                    if($fileSize <= $maxFileSize){

                      // File upload location
                      $location = public_path('/uploads');

                      // Upload file
                      $file->move($location,$filename);

                      // Import CSV to Database
                      $filepath = public_path("uploads/".$filename);

                      // Reading file
                      $file = fopen($filepath,"r");

                      $importData_arr = array();
                      $i = 0;

                      while (($filedata = fgetcsv($file, 100000, ",")) !== FALSE) {
                         $num = count($filedata );

                         if($i > 0){

                            for ($c=0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata [$c];
                            }
                         }
                         
                         $i++;
                      }
                      fclose($file);

                      $braj = 1 ; 
                     
                      $i1 = 1;
                      foreach($importData_arr as $importData) 
                        {

                                    $gender = '';
                                    if($importData[3]=='Male'){
                                        $gender = 1;

                                    }else{

                                         $gender = 2;
                                    }
                                    $email = $importData[7];
                                     $country_code = '+'.preg_replace('/\D/', '', $importData[1]);
                                     $phone = ($importData[2])? preg_replace('/\D/', '', $importData[2]):preg_replace('/\D/', '', $importData[6]);
                                     $name = $importData[0];
                                     
                                     if( $phone ){

                                         $insertData = array(
                           
                                               "name"=>$name,
                                               "gender"=>$gender,
                                               "email"=> $email,
                                               "password"=>bcrypt('123456789'),
                                               "country_code"=> $country_code,
                                               "phone"=>$phone,
                                               "status"=>1
                                            );

                                         $users = DB::table('users')->where('country_code',$insertData['country_code'])->where('phone',$insertData['phone'])->get();
            
                                if($users->count() == 0){
                                    $insertRecord++;
                                    $data  = array();
                                    $userId = DB::table('users')->insertGetId($insertData);

                                    if($userId){

                                          $arrayName = array('user_id' => $userId,'role_id'=>2 );
                                          DB::table('user_role')->insertGetId($arrayName);
                                         
                                          if($importData[16]){
                                            $cate= DB::table('categories')->where('name',$importData[16])->first();
                                            $cateid = $cate->id;
                                          }else{
                                                $cateid ='';

                                          }
                                            $insertBusinessData  = array(
                                                'user_id' =>$userId, 
                                                'name' =>$importData[4],   
                                                'phone' =>$importData[6], 
                                                'country_code' =>$country_code, 
                                                'mobile' =>$importData[2], 
                                                'membership_type' =>0, 
                                                'address' =>$importData[8], 
                                                'street_address' =>$importData[8], 
                                                'country' =>$importData[11], 
                                                'state' =>$importData[10], 
                                                'city' =>$importData[9], 
                                                'taluka' =>$importData[9], 
                                                'district' =>$importData[9], 
                                                'pincode' =>$importData[12], 
                                                'email_id' =>$importData[7], 
                                                'website_url' =>$importData[13],  
                                                'latitude'=>$importData[14] ,
                                                'longitude'=>  $importData[15],
                                                'parent_category'=>  $cateid,
                                                 
                                                 );
                                                //  $businessId =  DB::table('business')->insertGetId($insertBusinessData);
                                            $business = Business::create($insertBusinessData);
                                            $businessId = $business->id;
                                            $insertOwnersData  = array(
                                                                'business_id' =>  $businessId,
                                                                'full_name' => $importData[0],
                                                                'email_id' => $email
                                                             );
                                            $businessId =  DB::table('owners')->insertGetId($insertOwnersData);
                                    }

                                }else{

                                        $pand = array(
                                                    "row"=> $i1,
                                                    "name"=>$importData[0],                                   
                                                    "email"=>$email,                                    
                                                    "country_code"=>$country_code,
                                                    "phone"=>$phone, 
                                                    "msg"=>'Duplicate entry.'
                                                 );
                                     $notInsert++;
                                    $pendingRecord[] = $pand;
                                }

                             }else{
                                      $pand = array(
                                                    "row"=> $i1,
                                                    "name"=>$importData[0],                                   
                                                    "email"=>$email,                                    
                                                    "country_code"=>$country_code,
                                                    "phone"=>$phone, 
                                                    "msg"=>'Phone number mandatory field is require.'
                                                 );
                                     $notInsert++;
                                    $pendingRecord[] = $pand;

                             }
                                $i1++;

                        }
                        return view('Admin.CsvImport', compact('pendingRecord','notInsert','insertRecord'));
                    }else{
                     return view('Admin.CsvImport',compact('pendingRecord','notInsert','insertRecord'));
                    }

                  }else{
                   return Redirect::to("admin/csvimport/")->with('error', 'Import file formate is wrong! Please chack');
                  }
            }else{
                 
            return Redirect::to("admin/csvimport/")->with('error', 'Import file formate is wrong! Please chack');

            }
        }

        
    }
    
    /**
     * entry for place entity
     * 
     * Table:- business, entity_know_mores, entity_custom_fields
     *
     * @param  mixed $request
     * @return void
     */
    public function import(Request $request)
    {
        ini_set('memory_limit', '2400M');
        ini_set('max_execution_time', '3600');  

        if ($request->file('file') != null) {

            $file = $request->file('file');

            // File Details 
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Valid File Extensions
            $valid_extension = array("csv");

            // 2MB in Bytes
            $maxFileSize = 20971520;

            // Check file extension
            $insertRecord = 0;
            $notInsert = 0;
            $pendingRecord = array();
            if ($extension === "csv") {
                if (in_array(strtolower($extension), $valid_extension)) {

                    // Check file size
                    if ($fileSize <= $maxFileSize) {

                        // File upload location
                        $location = public_path('/uploads');

                        // Upload file
                        $file->move($location, $filename);

                        // Import CSV to Database
                        $filepath = public_path("uploads/" . $filename);

                        // Reading file
                        $file = fopen($filepath, "r");

                        $importData_arr = array();
                        $i = 0;

                        while (($filedata = fgetcsv($file, 100000, ",")) !== FALSE) {
                            $num = count($filedata);

                            if ($i > 0) {

                                for ($c = 0; $c < $num; $c++) {
                                    $importData_arr[$i][] = $filedata[$c];
                                }
                            }

                            $i++;
                        }
                        fclose($file);

                        $braj = 1;

                        $i1 = 1;
                        foreach ($importData_arr as $importData) {

                            $gender = '';
                            if ($importData[3] == 'Male') {
                                $gender = 1;
                            } else {

                                $gender = 2;
                            }
                            $email = $importData[7];
                            if(!empty($importData[1])) {
                                $country_code = '+' . preg_replace('/\D/', '', $importData[1]);
                            } else {
                                $country_code = null;
                            }
                            $phone = ($importData[2]) ? preg_replace('/\D/', '', $importData[2]) : preg_replace('/\D/', '', $importData[6]);
                            $name = $importData[0];

                            if (!empty($name) && $phone) {
                                info('importing csv file with user');

                                $insertData = array(

                                    "name" => $name,
                                    "gender" => $gender,
                                    "email" => $email,
                                    "password" => bcrypt('123456789'),
                                    "country_code" => $country_code,
                                    "phone" => $phone,
                                    "status" => 1
                                );

                                $users = DB::table('users')->where('country_code', $insertData['country_code'])->where('phone', $insertData['phone'])->get();

                                if ($users->count() == 0) {
                                    $insertRecord++;
                                    $data  = array();
                                    $userId = DB::table('users')->insertGetId($insertData);

                                    if ($userId) {

                                        $arrayName = array('user_id' => $userId, 'role_id' => 2);
                                        DB::table('user_role')->insertGetId($arrayName);

                                        if ($importData[16]) {
                                            $cate = DB::table('categories')->where('name', $importData[16])->first();
                                            $cateid = (!empty($cate))? $cate->id: '';
                                        } else {
                                            $cateid = '';
                                        }
                                        $insertBusinessData  = array(
                                            'user_id' => $userId,
                                            'name' => $importData[4],
                                            'phone' => $importData[6],
                                            'country_code' => $country_code,
                                            'mobile' => $importData[2],
                                            'membership_type' => 0,
                                            'address' => $importData[8],
                                            'street_address' => $importData[8],
                                            'country' => $importData[11],
                                            'state' => $importData[10],
                                            'city' => $importData[9],
                                            'taluka' => $importData[9],
                                            'district' => $importData[9],
                                            'pincode' => $importData[12],
                                            'email_id' => $importData[7],
                                            'website_url' => $importData[13],
                                            'latitude' => $importData[14],
                                            'longitude' =>  $importData[15],
                                            'parent_category' =>  $cateid,

                                        );
                                        //  $businessId =  DB::table('business')->insertGetId($insertBusinessData);
                                        $business = Business::create($insertBusinessData);
                                        $businessId = $business->id;
                                        $insertOwnersData  = array(
                                            'business_id' =>  $businessId,
                                            'full_name' => $importData[0],
                                            'email_id' => $email
                                        );
                                        $businessId =  DB::table('owners')->insertGetId($insertOwnersData);
                                    }
                                } else {

                                    $pand = array(
                                        "row" => $i1,
                                        "name" => $importData[0],
                                        "email" => $email,
                                        "country_code" => $country_code,
                                        "phone" => $phone,
                                        "msg" => 'Duplicate entry.'
                                    );
                                    $notInsert++;
                                    $pendingRecord[] = $pand;
                                }
                            } else if(!empty($importData[4])) {
                                info('importing csv file without user');
                                $insertRecord++;
                                if ($importData[16]) {
                                    $cate = DB::table('categories')->where('name', $importData[16])->first();
                                    $cateid = (!empty($cate))? $cate->id: '';
                                } else {
                                    $cateid = '';
                                }

                                if ($importData[5]) {
                                    $assetType = AssetType::where('name', $importData[5])->first();
                                    $assetTypeId = (!empty($assetType))? $assetType->id: 2;
                                } else {
                                    $assetTypeId = 2;
                                }
                                $insertBusinessData  = array(
                                    'user_id' => 0,
                                    'created_by' => auth()->id(),
                                    'asset_type_id' => $assetTypeId,
                                    'name' => $importData[4],
                                    'phone' => $importData[6],
                                    'country_code' => $country_code,
                                    'mobile' => $importData[2],
                                    'membership_type' => 0,
                                    'address' => $importData[8],
                                    'street_address' => $importData[8],
                                    'country' => $importData[11],
                                    'state' => $importData[10],
                                    'city' => $importData[9],
                                    'taluka' => $importData[9],
                                    'district' => $importData[9],
                                    'pincode' => $importData[12],
                                    'email_id' => $importData[7],
                                    'website_url' => $importData[13],
                                    'latitude' => $importData[14],
                                    'longitude' =>  $importData[15],
                                    'parent_category' =>  $cateid,
                                    'is_normal_view' =>  $importData[17],
                                    'short_description' =>  $importData[18],
                                    'description' =>  $importData[21],

                                );
                                $business = Business::create($insertBusinessData);
                                $businessId = $business->id;

                                $descData = [];
                                $english = 'english';
                                $hindi = config('constant.HI');
                                $rajsthani = config('constant.RJ');
                                if(!empty($importData[19])) {
                                    $descData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$hindi,
                                        'short_description' =>$importData[19],
                                        'description' =>$importData[22],
                                    ];
                                }
                                if(!empty($importData[20])) {
                                    $descData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$rajsthani,
                                        'short_description' =>$importData[20],
                                        'description' =>$importData[23],
                                    ];
                                }
                                if(!empty($descData)) {
                                    EntityDescriptionLanguage::insert($descData);
                                }

                                $knowData = [];
                                if(!empty($importData[24])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' => $english,
                                        'title' =>$importData[24],
                                        'description' =>$importData[27],
                                    ];
                                }
                                if(!empty($importData[25])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$hindi,
                                        'title' =>$importData[25],
                                        'description' =>$importData[28],
                                    ];
                                }
                                if(!empty($importData[26])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$rajsthani,
                                        'title' =>$importData[26],
                                        'description' =>$importData[29],
                                    ];
                                }

                                // for multiple value
                                if(!empty($importData[30])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' => $english,
                                        'title' =>$importData[30],
                                        'description' =>$importData[33],
                                    ];
                                }
                                if(!empty($importData[31])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$hindi,
                                        'title' =>$importData[31],
                                        'description' =>$importData[34],
                                    ];
                                }
                                if(!empty($importData[32])) {
                                    $knowData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$rajsthani,
                                        'title' =>$importData[32],
                                        'description' =>$importData[35],
                                    ];
                                }
                                if(!empty($knowData)) {
                                    EntityKnowMore::insert($knowData);
                                }

                                $customData = [];
                                if(!empty($importData[36])) {
                                    $customData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$english,
                                        'title' =>$importData[36],
                                        'description' =>$importData[39],
                                    ];
                                }
                                if(!empty($importData[37])) {
                                    $customData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$hindi,
                                        'title' =>$importData[37],
                                        'description' =>$importData[40],
                                    ];
                                }
                                if(!empty($importData[38])) {
                                    $customData[] = [
                                        'entity_id' => $businessId,
                                        'language' =>$rajsthani,
                                        'title' =>$importData[38],
                                        'description' =>$importData[41],
                                    ];
                                }
                                if(!empty($customData)) {
                                    EntityCustomField::insert($customData);
                                }
                            }
                            $i1++;
                        }
                        return view('Admin.CsvImport', compact('pendingRecord', 'notInsert', 'insertRecord'));
                    } else {
                        return view('Admin.CsvImport', compact('pendingRecord', 'notInsert', 'insertRecord'));
                    }
                } else {
                    return Redirect::to("admin/csvimport/")->with('error', 'Import file formate is wrong! Please chack');
                }
            } else {

                return Redirect::to("admin/csvimport/")->with('error', 'Import file formate is wrong! Please chack');
            }
        }
    }

}
