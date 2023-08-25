<?php
                                       
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StateRequest;
use Auth;
use Input;
use Config;
use Redirect;
use App\State;
use App\Country;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objState = new State();
        $this->objCountry = new Country();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $states = State::select('*')->has('country')->with('country');
            $user = auth()->user();
            return DataTables::of($states)
                ->addColumn('action', function($state) use($user) {
                    $editBtn = $deleteBtn = '';
                    $class = 'delete-state';
                    $encriptId = Crypt::encrypt($state->id);
                    $attributes = [
                        "data-url" => route('state.destroy',$state->id)
                    ];

                    if($user->can(config('perm.editState'))) {
                        $editUrl = route('state.edit',$encriptId);
                        $editBtn = getEditBtn($editUrl);
                    }
                    if($user->can(config('perm.deleteState'))) {
                        $deleteBtn = getDeleteBtn("#",$class, $attributes);
                    }                    
                    return $editBtn.$deleteBtn;
                })
                ->make(true);
        }
        return view('Admin.State.index');
    }

    public function add()
    {
        $data = [];
        $countries = $this->objCountry->getAll();
        return view('Admin.State.edit', compact('data','countries'));
    }
    public function getState()
    {
          $selected_country = Input::get('selected_country');
       
       

      $country_id = $this->objCountry->where('name',$selected_country)->first();

       $countryId =  $country_id->id;
        
      $stateList =     $this->objState->select('name')->where('country_id',$countryId )->get();
        

      $statedata = '<option value="">Select State</option>';
      foreach ($stateList as $key => $value) {
        $statedata .="<option value=".$value->name.">".$value->name ."</option>";

          # code...
      }
       return   $statedata;
    } 
    public function getStateList()
    {
          $selected_country = Input::get('selected_country');
       
       


       $countryId =  $selected_country;
        
      $stateList =     $this->objState->select('name','id')->where('country_id',$countryId )->get();
        

      $statedata = '<option value="">Select State</option>';
      foreach ($stateList as $key => $value) {
        $statedata .="<option value=".$value->id.">".$value->name ."</option>";

          # code...
      }
       return   $statedata;
    }

    

    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $data = $this->objState->find($id);
            $countries = $this->objCountry->getAll();
            if($data) {
                return view('Admin.State.edit', compact('data','countries'));
            } else {
                return Redirect::to("admin/state")->with('error', trans('labels.recordnotexist'));
            }
        } catch (DecryptException $e) {
            return view('errors.404');
        }
        
    }

    public function save(StateRequest $request)
    {
        $postData = Input::all();
        unset($postData['_token']);

        $stateName = $postData['name'];
        if(!empty($stateName))
        {
            //Formatted country name
            $formattedAddr = str_replace(' ','+',$stateName);
            //Send request and receive json data by stateName
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
        $response = $this->objState->insertUpdate($postData);
        if ($response) {
            return Redirect::to("admin/state")->with('success', trans('labels.statesuccessmsg'));
        } else {
            return Redirect::to("admin/state")->with('error', trans('labels.stateerrormsg'));
        }
    }

    public function delete($id)
    {
        $data = $this->objState->find($id);
        $response = $data->delete();
        if ($response) {
            return Redirect::to("admin/state")->with('success', trans('labels.statedeletesuccessmsg'));
        }
    }

}
