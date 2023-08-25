<?php

namespace App\Http\Controllers\Admin;

use App\PaymentTransaction;
use App\Business;
use App\SubscriptionPlan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Cache;
use Input;

class PaymentTransactionsController extends Controller
{
    public function __construct()
    {        
        $this->middleware('auth');
        $this->objBusiness = new Business();
        $this->objPlan = new SubscriptionPlan();
        $this->objPayTrans = new PaymentTransaction();

        $this->loggedInUser = Auth::guard();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $postData = Input::all();
        //$businesses = $this->objBusiness->where('approved','1')->get();
        $plans = $this->objPlan->get();

        $filters['businesses'] = $request->businesses;
        $filters['plans'] = $request->plans;
        $filters['status'] = $request->status;

        if (false){
            $PaymentTransactions = Cache::get('PaymentTransactions');
        } else {
            $PaymentTransactions = $this->objPayTrans->getAll($filters, true, true);
            //Cache::put('PaymentTransactions', $PaymentTransactions, 60);
        }
        return view('Admin.Transations', compact('PaymentTransactions','plans','postData'));
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
     * @param  \App\PaymentTransactions  $paymentTransactions
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTransactions $paymentTransactions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PaymentTransactions  $paymentTransactions
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentTransactions $paymentTransactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentTransactions  $paymentTransactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentTransactions $paymentTransactions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PaymentTransactions  $paymentTransactions
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTransactions $paymentTransactions)
    {
        //
    }
}
