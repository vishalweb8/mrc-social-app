<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Log;
use App\UserSetting;

class UserSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [];
        $responseData = ['status' => 0, 'message' => trans('apimessages.default_error_msg')];
        try
        {
             $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'settings' => 'required|array|min:1',
                'type' => 'required',
            ]);
            if ($validator->fails())
            {
                $responseData = ['status' => 0, 'message' => $validator->messages()->all()];
            }
            else
            {
                $input = $request->all();
                foreach($input['settings'] as $key => $val)
                {
                    $data = UserSetting::firstOrNew(['user_id' =>  $request['user_id'], "name" => $key, "type" => $request['type']]);
                    $data->user_id = $request['user_id'];
                    $data->name = $key;
                    $data->status= $val;
                    $data->	type = $request['type'];
                    $data->save();
                }
                $responseData['status'] = 1;
                $responseData['message'] =  trans('apimessages.user_setting_updated_success');
               // Log::info("all good success==");
            }

        } catch (\Exception $e) {
           
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData);
        
        //
    }

      /**
     * getuserSettingById
     *
     * @param  mixed $request
     * @return void
     */
    public function  getuserSettingById(Request $request) {
        $responseData = ['status' => 1, 'message' => trans('apimessages.default_error_msg')];
        $statusCode = 400;


        try
        {
            $requestData = array_map('trim',$request->all());
            $validator = Validator::make($requestData, [
                'user_id' => ['required'],
                'type' => ['required'],
            ]);

            if ($validator->fails()) {
                $responseData = ['status' => 0, 'message' => $validator->messages()->all()];
            } else {

            $UserSetting = UserSetting::where('user_id',$request->user_id)->where('type',$request->type)->get();

            $mainArray = [];
            if ($UserSetting)
            {
      
                foreach ($UserSetting as $key => $code)
                {
                    $listArray = [];
                       $listArray = $code;
                        $mainArray[] = $listArray;
                    
                }
                if (empty($mainArray)) {
                    // $this->log->info('API getCountryCode successfully');
                    $responseData['status'] = 0;
                    $responseData['message'] =  trans('apimessages.user_setting_not_found');
                    $responseData['data'] = $mainArray;
                    $statusCode = 200;
               }else {

                // $this->log->info('API getCountryCode successfully');
                $responseData['status'] = 1;
                $responseData['message'] =  trans('apimessages.get_user_setting');
                $responseData['data'] = $mainArray;
                $statusCode = 200;

               }
               
                
            }
            else
            {
                $responseData['status'] = 0;
                $responseData['message'] = trans('apimessages.norecordsfound');
                $responseData['data'] =  $mainArray;
                $statusCode = 200;
            }
        }

        } catch (Exception $e) {
            $this->log->error('API something went wrong while get user setting', array('error' => $e->getMessage()));
            $responseData = ['status' => 0, 'message' => $e->getMessage()];
            return response()->json($responseData, $statusCode);
        }
        return response()->json($responseData, $statusCode);

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
        //
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
    public function destroy($id)
    {
        //
    }
}
