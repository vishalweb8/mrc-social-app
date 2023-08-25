<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class NotificationGroupNew extends Model
{
    use SoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_groups';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['created_by', 'title', 'description', 'external_link', 'target_link', 'sender_type', 'filters_data', 'notification_count', 'status', 'approved_by', 'sent_at', 'approved_at'];

    /**
     * for get user detail
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function scopeFilters($query, $filters)
	{	
		if(isset($filters['user_id']) && !empty($filters['user_id']))
		{
			$query->where('created_by', $filters['user_id']);
		}

		if (isset($filters['skip']) && $filters['skip'] >= 0 && isset($filters['take']) && $filters['take'] > 0) {
			$query->skip($filters['skip'])->take($filters['take']);
		}
		return $query->orderBy('notification_groups.id','desc');
	}
}
