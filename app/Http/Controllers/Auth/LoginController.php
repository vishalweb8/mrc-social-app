<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Role;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use Illuminate\Support\Facades\Request;
// use Input;
use Redirect;
use Symfony\Component\Console\Input\Input;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = 'admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function index()
    {
        return view('Admin.Login');
    }

    public function authenticate(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        if (Auth::attempt(['email' => $email, 'password' => $password,'status' => 1])) {
            $this->checkAndRedirect();
        }
        elseif (Auth::attempt(['phone' => $email, 'password' => $password,'status' => 1])) {
            $this->checkAndRedirect();
        }

        return Redirect::back()->withInput()->withErrors(trans('validation.invalidcombo'));
    }
    
    /**
     * for check valid user and redirect on dashboard page
     *
     * @return void
     */
    public function checkAndRedirect()
    {
        try {
            $roleIds = Role::where('name','<>','User')->pluck('id')->toArray();
            if(Auth::user()->hasAnyRole($roleIds)) {
                return redirect()->intended('admin/dashboard');
            }
            Auth::logout();
        } catch (\Throwable $th) {
            \Log::error('generating error while login:- '.$th);
        }
        return redirect('admin/login')->withErrors(trans('validation.invalidcombo'));
    }
}
