<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Auth;
use DB;
use Config;
use Crypt;
use Cache;

class Chats extends Model 
{
    use SoftDeletes, CascadeSoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'chats';

    /**
     * Type:
     * 
     * 1- Send Enquiry,
     * 2- Investment opportunity 
     * 3- Send Inquiry to Customer.
     * 4- Ads Interest
     */    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'business_id', 'advertisement_id', 'customer_id', 'member_id', 'customer_read_flag', 'member_read_flag', 'created_at', 'updated_at','type'];
    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $cascadeDeletes = ['getChatMessages'];

    protected $primaryKey = 'id';
    
    
    
    public function getUser()
    {
        return $this->belongsTo('App\User', 'customer_id');
    }
    
    public function getUserMember()
    {
        return $this->belongsTo('App\User', 'member_id');
    }
    
    public function getBusiness() 
    {
        return $this->belongsTo('App\Business', 'business_id');
    }
    
    public function getChatMessages() 
    {
        return $this->hasMany('App\ChatMessages', 'chat_id');
    }
    
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) {
            $updateData = [];
            foreach ($this->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }
            return Chats::where('id', $data['id'])->update($updateData);
        } else {
             return Chats::create($data);
        }
    }

    
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = Chats::whereNull('deleted_at');

        if(isset($filters) && !empty($filters)) 
        {
            if(isset($filters['userId']) && !empty($filters['userId']))
            {
                $userId = $filters['userId'];
                $getData->where(function($query) use ($userId) {
                        $query->whereRaw('customer_id = '.$userId.' AND customer_read_flag = 1')
                            ->orWhereRaw('member_id = '.$userId.' AND member_read_flag = 1');
                    });
            }
            if(isset($filters['skip']) && isset($filters['take']) && !empty($filters['take']))
            {
                $getData->skip($filters['skip'])->take($filters['take']);
            }
            if(isset($filters['updated_at']))
            {
                $getData->orderBy('updated_at', 'DESC');
            }
            else
            {
                $getData->orderBy('id', 'DESC');
            }
        }
        else
        {
            $getData->orderBy('id', 'DESC');
        }
        if(isset($paginate) && $paginate == true) 
        {
            return $response = $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } 
        else 
        {
            return $response = $getData->get();
        }
    }

    /**
     * Get Unread Threads Count
     */
    public function getUnreadThreadsCount($userId) 
    {
        // $threads = Chats::where(function($query) use ($userId){
        //                 $query->where('customer_id', $userId)
        //                     ->orWhere('member_id', $userId);
        //             });
        // $threads->whereHas('getChatMessages',function ($query) use ($userId) {
        //             $query->whereRaw("NOT FIND_IN_SET(".$userId.", read_by)");
        //         });

        // return $threads->count();
        $tCount = 0;
        $SQLQuery = "SELECT 
                        IFNULL(SUM(totalUnRead), 0) AS totalUnRead
                    FROM `chats`
                        JOIN (
                            SELECT `chat_messages`.`chat_id`, count(`chat_messages`.`chat_id`) totalUnRead 
                            FROM `chat_messages` 
                            WHERE NOT FIND_IN_SET($userId, read_by) AND `chat_messages`.`deleted_at` is null
                            GROUP BY `chat_messages`.`chat_id`
                        ) `chat_messages` ON `chats`.`id` = `chat_messages`.`chat_id`
                    WHERE (`customer_id` = :customer_id or `member_id` = :member_id) AND `chats`.`deleted_at` is null";
        $queryResult = DB::select($SQLQuery, [':customer_id' => 0, ":member_id" =>$userId] );

        if(count($queryResult) > 0){
            $tCount = $queryResult[0]->totalUnRead;
        }
        return $tCount;
    }
    
    /**
     * Get the duplicate Interest Thread
     */
    public function checkDuplicateInterestThread($data)
    {
        return Chats::where('customer_id', $data['customer_id'])
                    ->where('advertisement_id', $data['advertisement_id'])
                    ->first();
    }
    
    public function getAdvertisement() 
    {
        return $this->belongsTo('App\Advertisement', 'advertisement_id');
    }
}
