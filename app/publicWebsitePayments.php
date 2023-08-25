<?php

namespace App;

use App\PublicWebsite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class publicWebsitePayments extends Model
{
    use SoftDeletes;
	
    protected $table = 'public_website_payments';
    public $primaryKey = 'id';
    protected $fillable = ['pw_id', 'payment_amount','payment_date','pay_trans_id','payment_status','payment_message'];

    public function publicWebsiteName(){
     	return $this->hasOne(PublicWebsite::class, 'id', 'pw_id');
     }
}
