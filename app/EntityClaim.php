<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EntityClaim extends Model
{    
    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = ['entity_id','claim_by','document','status'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d, H:m',
    ];
    
    /**
     * get full url of documnt
     *
     * @return void
     */
    public function getDocumentAttribute($document)
	{
		$url = '';
		if(!empty($document)) {
			$url = \Storage::disk(config('constant.DISK'))->url($document);
		}
    	return $url;
	}
    
    /**
     * for get user detail
     *
     * @return void
     */
    public function claimBy()
    {
        return $this->belongsTo(User::class, 'claim_by');
    }
    
    /**
     * entity
     *
     * @return void
     */
    public function entity()
    {
        return $this->belongsTo(Business::class, 'entity_id');
    }
}
