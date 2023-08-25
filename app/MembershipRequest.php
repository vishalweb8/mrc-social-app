<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
use Config;

class MembershipRequest extends Model
{
    use SoftDeletes;

    protected $table = 'membership_requests';
    protected $fillable = ['subscription_plans_id', 'user_id','reasons','status'];
    protected $dates = ['deleted_at'];

   
   public function subscriptionPlan()
    {
        return $this->belongsTo('App\SubscriptionPlan','subscription_plans_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function singlebusiness()
    {
        return $this->hasOne('App\Business','user_id');
    }

    public function getMembershipRequestDetailsById($id)
    {
        return MembershipRequest::select('*')
            ->with(['subscriptionPlan','user.singlebusiness'])
            ->where('id',$id)
            ->get();
    }
}
