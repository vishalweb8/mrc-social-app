<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Category;
use App\City;
use App\Country;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\NotificationGroupNew;
use App\Owners;
use App\State;
use App\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('permission:'.config('perm.listNotification'), ['only' => ['index']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filterStatus = $request->status;
            $notificationFor = $request->notification_for;
            $notifications = NotificationGroupNew::select('notification_groups.*')->has('user')->with('user');
            $notifications->addSelect(\DB::raw("CASE
            WHEN  sender_type = 'all' THEN 'Send to everyone'
            WHEN  sender_type = 'all_member' THEN 'Send to all members'
            WHEN  sender_type = 'all_business' THEN 'Send to all business'
            else
            'Target specific audience' end as sender_type"));
            if(!empty($filterStatus)) {
                $notifications->where('notification_groups.status',$filterStatus);
            }
            $user = auth()->user();
            if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
                $notifications->whereHas('user.singlebusiness', function ($query) use ($user) {
                    $query->whereRaw($user->sql_query);
                })->orWhere('notification_groups.created_by',$user->id);
            }
            return DataTables::of($notifications)
                ->addColumn('action', function($notification) use($notificationFor,$user) {
                    $class = $btn = '';
                    $attributes = [
                        "data-id" => $notification->id,
                        "data-url" => route('notification.update',$notification->id)
                    ];

                    if($notificationFor == 'approval') {
                        if($user->can(config('perm.approveNotification'))) {
                            $class = 'approve-notification';
                            $btn .= getBtn('Approve',$class, $attributes);
                        }
                        if($user->can(config('perm.rejectNotification'))) {
                            $class = 'btn-danger reject-notification';
                            $btn =$btn . ' '. getBtn('Reject',$class, $attributes);
                        }
                    } else {
                        if($notification->status == 'drafted') {
                            if($user->can(config('perm.sendNotification'))) {
                                $class = 'send-notification';
                                $btn = getBtn('Send',$class, $attributes);
                            }
                        } else {
                            if($user->can(config('perm.sendNotification'))) {
                                $class = 'resend-notification';
                                $btn = getBtn('Resend',$class, $attributes);
                            }
                        }
                    }
                    return $btn;
                })
                ->make(true);
        }

        $categories = Category::where('parent','0')->get();
        $countries =  Country::orderBy('name')->get();
        $states =  State::orderBy('name')->get();
        $cities =  Helpers::getCities();
        $districts = getDistricts();
        $educations = getEducations();
        $castes = getCaste();
        return view('Admin.Notification.index',compact('categories','countries','states','cities','districts','educations','castes'));
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
        //
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
        try {
            $notification = NotificationGroupNew::findOrFail($id);
            $notification->status = $request->status;
            $msg = $request->status;
            if($request->status == 'pending') {
                $msg = "sent for approval";
                $notification->sent_at = now();
            } else if($request->status == 'approved') {
                dispatch(new SendNotificationJob($id));
                $notification->approved_at = now();
                $notification->approved_by = \Auth::id();
            }

            $notification->save();
            return redirect()->back()->with('success', 'Notification '.$msg.' successfully');
        } catch (\Throwable $th) {
            return redirect()->route('notification.index')->withErrors([$th->getMessage()]);
        }
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

    public function getUserForSendNotification(Request $request)
    {
        if ($request->ajax()) {            
            $users = (new User())->getUsers($request);
            return DataTables::of($users)
                ->editColumn('phone', function($user){                    
                    return $user->country_code .' '. $user->phone;                    
                })
                ->addColumn('business_id', function($user){
                    if(!empty($user->singlebusiness)) {
                        return $user->singlebusiness->id;
                    }
                    return '';                    
                })
                ->addColumn('business_name', function($user){
                    if(!empty($user->singlebusiness)) {
                        return $user->singlebusiness->name;
                    }
                    return '';                    
                })
                ->editColumn('membership_type', function($user){
                    $membershipType = '';
                    if(!empty($user->singlebusiness)) {
                        if($user->singlebusiness->membership_type == 2) {
                            $membershipType = 'Lifetime';
                        } else if($user->singlebusiness->membership_type == 1) {
                            $membershipType = 'Premium';
                        } else {
                            $membershipType = 'Basic';
                        }

                        return $membershipType;
                    }
                    return '';                    
                })
                ->make(true);
        }
    }
}
