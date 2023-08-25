<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Config;
use App\SubscriptionPlan;

class PaymentTransaction extends Model
{
    protected $table='payment_transactions';
    protected $fillable=[
    	'user_id','business_id','type','plan_id','order_id','status','transaction_id','created_at','updated_at'
    ];

    /**
     * get all transations for admin
     * @param: $filters array of search parameters
     * @param: $paginate boolean.
     * @param: $isFromAdminPanel boolean.
     */
    public function getAll($filters = array(), $paginate = false, $isFromAdminPanel = false)
    {
        $filters['take'] = 10;
        $transations = PaymentTransaction::orderBy('id', 'DESC');
        $user = auth()->user();
        if(!$user->isSuperAdmin() && !empty($user->sql_query)) {
            $transations->whereHas('business', function ($query) use ($user) {
                $query->whereRaw($user->sql_query);
            });
        }
        
        if (isset($filters['page']) && $filters['page'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
            $transations->skip($filters['page'])->take($filters['take']);
        } else {
            $transations->skip(1)->take(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        
        // if($filters['businesses'] != '')
        // {
        //     $transations->whereHas("users.singlebusiness" , function($query) use ($filters) {
        //         $query->select('id', 'name','user_id')->where('id','=',$filters['businesses']);
        //     });
        // }
        // else
        // {
        //     $transations->whereHas("users.singlebusiness" , function($query) use ($filters) {
        //         $query->select('id', 'name','user_id');
        //     });
        // }

        if($filters['plans'] != '')
        {
            $transations->whereHas("plans" , function($query2) use ($filters) {
                $query2->select('id', 'name')->where('id','=',$filters['plans']);
            });
        }
        else
        {
            $transations->whereHas("plans" , function($query2) use ($filters) {
                $query2->select('id', 'name');
            });
        }

        if($filters['status'] != '')
        {
            $transations->where('status','=',$filters['status']);
        }

        if ($isFromAdminPanel == true) {
            if (isset($paginate) && $paginate == true && isset($filters['take']) && $filters['take'] > 0) {
                return $transations->paginate($filters['take']);
            } elseif ($paginate) {
                return $transations->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $transations->get();
            }
        } else {
            if (isset($paginate) && $paginate == true) {
                return $transations->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            } else {
                return $transations->get();
            }
        }
    }

    public function users()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function plans()
    {
        return $this->belongsTo('App\SubscriptionPlan','plan_id');
    }

    public function businesses()
    {
        return $this->hasMany('App\Business','user_id');
    }

    public function business()
    {
        return $this->hasOne('App\Business','user_id','user_id');
    }
}
