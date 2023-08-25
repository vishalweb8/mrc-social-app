<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\SendMail;
use Illuminate\Http\Request;
use Log;

class SendMailController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listSendEmail'), ['only' => ['index']]);
        $this->middleware(['permission:'.config('perm.addSendEmail')],   ['only' => ['create','store']]);
        $this->middleware('permission:'.config('perm.addSendEmail'), ['only' => ['create']]);
        $this->middleware('permission:'.config('perm.editSendEmail'), ['only' => ['edit','update']]);
        $this->middleware('permission:'.config('perm.deleteSendEmail'),   ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mails = SendMail::latest()->get();
		return view('Admin.SendMail.index', compact('mails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.SendMail.create');
    }    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
			'subject' => 'required',
			'mail_body' => 'required',
			'type' => 'required|in:user,business',
			'start_id' => 'required|numeric',
			'end_id' => 'required|numeric',
		]);

        try {
			$data = $request->except(['_token']);
			$mailData = SendMail::create($data);
			dispatch(new SendMailJob($mailData));
			return redirect()->route('sendMail.index')->with("success","Mail sent successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while sending mail: ".$e);
			return redirect()->route('sendMail.index')->with("error",$e->getMessage());
		}
    }    
    
    /**
     * edit
     *
     * @param  mixed $sendMail
     * @return void
     */
    public function edit(SendMail $sendMail)
    {
        return view('Admin.SendMail.edit',compact('sendMail'));
    }
    
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $sendMail
     * @return void
     */
    public function update(Request $request,SendMail $sendMail)
    {
        $request->validate([
			'subject' => 'required',
			'mail_body' => 'required',
			'type' => 'required|in:user,business',
			'start_id' => 'required|numeric',
			'end_id' => 'required|numeric',
		]);

        try {
			$data = $request->except(['_token']);
			$sendMail->update($data);
			dispatch(new SendMailJob($sendMail));
			return redirect()->route('sendMail.index')->with("success","Mail re-sent successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while re-sending mail: ".$e);
			return redirect()->route('sendMail.index')->with("error",$e->getMessage());
		}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SendMail  $sendMail
     * @return \Illuminate\Http\Response
     */
    public function destroy(SendMail $sendMail)
    {
        try {
			$sendMail->delete();
			return redirect()->route('sendMail.index')->with("success","Send mail deleted successfully");
		} catch (\Exception $e) {
			Log::error("Getting error while deleting Send mail: ".$e);
			return redirect()->route('sendMail.index')->with("error",$e->getMessage());
		}
    }
}
