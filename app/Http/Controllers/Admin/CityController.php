<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use Auth;
use Input;
use Config;
use Redirect;
use App\State;
use App\City;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objCity = new City();
        $this->objState = new State();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cities = City::select('*')->has('state')->with('state');
            $user = auth()->user();
            return DataTables::of($cities)
                ->addColumn('action', function($city) use($user) {
                    $class = 'delete-city';
                    $encriptId = Crypt::encrypt($city->id);
                    $attributes = [
                        "data-url" => route('city.destroy',$city->id)
                    ];
                    if($user->can(config('perm.editCity'))) {
                        $editUrl = route('city.edit',$encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteCity'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                    
                    return $editBtn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.City.index');
    }

    public function getCity()
    {
      $selected_state = Input::get('selected_state');
       
      $state_id = $this->objState->where('name',$selected_state)->first();
      $stateId =  $state_id->id;
      $cityList =     $this->objCity->select('name')->where('state_id',$stateId )->get();
      $cityData = '<option value="">Select City</option>';
      foreach ($cityList as $key => $value) {
        $cityData .="<option value=".$value->name.">".$value->name ."</option>";
      }
       return   $cityData;
    }
    public function getCityList()
    {
          $selected_state = Input::get('selected_state');
       
       $stateId =  $selected_state;
        
      $cityList =     $this->objCity->select('name','id')->where('state_id',$stateId )->get();
        
      $cityData = '<option value="">Select City</option>';
      foreach ($cityList as $key => $value) {
        $cityData .="<option value=".$value->name.">".$value->name ."</option>";
      }
       return   $cityData;
    }

    public function add()
    {
        $data = [];
        $states = $this->objState->getAll();
        return view('Admin.City.edit', compact('data','states'));
    }

    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $data = $this->objCity->find($id);
            $states = $this->objState->getAll();
            if($data) {
                return view('Admin.City.edit', compact('data','states'));
            } else {
                return Redirect::to("admin/city")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            return view('errors.404');
        }
        
    }

    public function save(CityRequest $request)
    {
        $postData = $request->all();
        $postData['position'] = ($request->position) ? $request->position : null ;
        unset($postData['_token']);
        
        $cityName = $postData['name'];
        if(!empty($cityName))
        {
            //Formatted country name
            $formattedAddr = str_replace(' ','+',$cityName);
            //Send request and receive json data by cityName
            $geocodeFromAddr = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddr.'&sensor=false'); 
            $output = json_decode($geocodeFromAddr);
            //Get latitude and longitute from json data
            if(isset($output->{'results'}[0]) && !empty($output->{'results'}[0]))
            {
                $data['latitude']  = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                $data['longitude'] = $output->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                //Return latitude and longitude of the given address
                if(!empty($data)){
                    $postData['latitude'] = $data['latitude'];
                    $postData['longitude'] = $data['longitude'];
                }
            }
        }
        $response = $this->objCity->insertUpdate($postData);
        if ($response) {
            return Redirect::to("admin/city")->with('success', trans('labels.citysuccessmsg'));
        } else {
            return Redirect::to("admin/city")->with('error', trans('labels.cityerrormsg'));
        }
    }

    public function delete($id)
    {
        $data = $this->objCity->find($id);
        $response = $data->delete();
        if ($response) {
            return Redirect::to("admin/city")->with('success', trans('labels.citydeletesuccessmsg'));
        }
    }

}
