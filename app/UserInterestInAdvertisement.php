<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;
use Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInterestInAdvertisement extends Model
{
    //
    use SoftDeletes;
    
    protected $table = 'user_interest_in_advertisement';

    protected $fillable = [ 'user_id', 'advertisement_id', 'comment'];
    
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }    

    public function advertisement()
    {
        return $this->belongsTo(App\Advertisement::class);
    }
    
    public function chats()
    {
        return $this->belongsTo(Chats::class, 'advertisement_id', 'advertisement_id');
    }
    
    /**
     * Save the data for advertisement interest. If user already showed interest than data will not be added.
     */
    public function insertUpdate($data)
    {
        $savedData = UserInterestInAdvertisement::withTrashed()
                    ->where('user_id', $data['user_id'])
                    ->where('advertisement_id', $data['advertisement_id'])
                    ->first();
        if (!$savedData) {
            $data['created_at'] = Carbon::now();
            return UserInterestInAdvertisement::create($data);
        } else {            
            if($savedData->deleted_at != '') {
                $savedData->restore();
                $savedData->created_at = Carbon::now();
            }
            $savedData->updated_at = Carbon::now();
            $savedData->comment = $data['comment'];
            $savedData->save();
        }

        return $savedData;
    }

    
    /**
     * Delete the item for selected user.
     */
    public function deletedAdvertisement($data)
    {
        // $queryBuilder = UserInterestInAdvertisement::where('user_id', $data['user_id'])
        //             ->where('advertisement_id', $data['advertisement_id'])
        //             ->first();
        $queryBuilder = UserInterestInAdvertisement::find($data['interest_id']);

        if ($queryBuilder) {
            return $queryBuilder->delete();
        }
        return false;
    }

    /**
     * Find deleted interest response and re-enable 
     */
    public function checkDuplicateInterest($data)
    {
        $isSavedAndDeleted = UserInterestInAdvertisement::onlyTrashed()
                            ->where('user_id', $data['user_id'])
                            ->where('advertisement_id', $data['advertisement_id'])
                            ->count();

        if($isSavedAndDeleted > 0) {
            UserInterestInAdvertisement::onlyTrashed()
                            ->where('user_id', $data['user_id'])
                            ->where('advertisement_id', $data['advertisement_id'])
                            ->restore();
        }

        // return UserInterestInAdvertisement::where('user_id', $data['user_id'])
        //             ->where('advertisement_id', $data['advertisement_id'])
        //             ->count();
    }

    public function getInterestIdByAdsAndUserId($data)
    {
        $isThere = UserInterestInAdvertisement::where('user_id', $data['user_id'])
                            ->where('advertisement_id', $data['advertisement_id'])
                            ->first();        

        if($isThere) {
            return $isThere->id;
        }
        return "0";
    }
}