<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\BusinessRequest;
use Auth;
use Input;
use Config;
use Redirect;
use App\User;
use App\AgentRequest;
use App\Http\Controllers\Controller;
use Crypt;
use Helpers;
use Image;
use Cache;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->objUser = new User();
        $this->objAgentRequest = new AgentRequest();
        
        $this->loggedInUser = Auth::guard();
        $this->log = new Logger('agent-controller');
        $this->log->pushHandler(new StreamHandler(storage_path().'/logs/monolog-'.date('m-d-Y').'.log'));
    }

    public function index()
    {
        if (Cache::has('agentRequestList')){
            $agentRequestList = Cache::get('agentRequestList');
        } else {
            $agentRequestList = AgentRequest::has('user')->with('user')->get();
            Cache::put('agentRequestList', $agentRequestList, 60);
        }
        $this->log->info('Admin agent request list page', array('admin_user_id' =>  Auth::id()));
        return view('Admin.ListAgentRequest', compact('agentRequestList'));
    }

    
    public function rejectedIndex()
    {        
        $agentRequestList = $this->objAgentRequest->getAllRejected();
        $this->log->info('Admin agent request list page', array('admin_user_id' =>  Auth::id()));

        $isRejected = true;
        return view('Admin.ListAgentRequest', compact('agentRequestList','isRejected'));
    }

    public function agentRequest($id)
    {
        $id = Crypt::decrypt($id);
        $data = $this->objAgentRequest->find($id);
        $response = $data->delete();
        if ($response) {
            $userData['id'] = $data->user_id;
            $userData['agent_approved'] = Config::get('constant.AGENT_APPROVED_FLAG');
            $this->objUser->insertUpdate($userData);
            Cache::forget('agentRequestList');
            $this->log->info('Admin agent request approved successfully', array('admin_user_id' =>  Auth::id()));
            return Redirect::to("admin/agents")->with('success', trans('labels.agentrequestapprovedsuccessmsg'));
        }
    }
 
    /**
     * Rejecting user request with comment
     * 
     * @param Illuminate\Http\Request $request
     * 
     */
    public function rejectAgentRequest(Request $request)
    {
        $id = Crypt::decrypt($request->txtCommentId);
        $data = $this->objAgentRequest->find($id);
        $data->admin_comment = $request->txtComment;
        $data->save();
        $response = $data->delete();

        if ($response) {
            $userData['id'] = $data->user_id;
            $userData['agent_approved'] = Config::get('constant.AGENT_DECLINE_FLAG');
            $this->objUser->insertUpdate($userData);
            Cache::forget('agentRequestList');
            $this->log->info('Admin agent request rejected successfully', array('admin_user_id' =>  Auth::id()));
            return Redirect::to("admin/agents")->with('success', trans('labels.agentrequestrejectedsuccessmsg'));
        }
    }
}
