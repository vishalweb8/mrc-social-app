<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityReportReason extends Model
{
    protected $fillable = ['entity_report_id','reason_id'];

    public $timestamps = false;
    /**
     * for get reason
     *
     * @return void
     */
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }
}
