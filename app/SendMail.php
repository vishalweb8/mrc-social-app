<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SendMail extends Model
{
	use SoftDeletes;

    protected $fillable = ['subject', 'mail_body', 'type', 'start_id', 'end_id', 'number_of_sent','is_sent'];
}
