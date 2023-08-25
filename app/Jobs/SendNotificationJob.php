<?php

namespace App\Jobs;

use App\Helpers\Helpers;
use App\NotificationGroupNew;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            info('notification id:- '.$this->id);
            $notification = NotificationGroupNew::where('id',$this->id)->where('status','approved')->first();
            if($notification) {
                $data['title'] = $notification->title;
                $data['message'] = $notification->description;                
                $data['type'] = '10';                
                $filtersData = getFiltersDataInArray($notification->filters_data);
                $filters = new Request($filtersData);
                if($notification->sender_type == 'all' && $filters->notification_to == 'sendToAll') {                    
                    $token = "/topics/ryec"; 
                    Helpers::topicPushNotification($token, $data);
                } else {
                    $filtersData = getFiltersDataInArray($notification->filters_data);
                    $filters = new Request($filtersData);
                    info($filters);
                    $users = (new User())->getUsers($filters,false);
                    $users->with('devices');
                    $users = $users->get();
                    $usersCount = $users->count();
                    info("users count:- ".$usersCount);
                    $notification->notification_count = $usersCount;
                    $notification->save();
                    foreach ($users as $user) {                   
                        foreach ($user->devices as $device) {
                            if(!empty($device->device_token)) {
                                Helpers::topicPushNotification($device->device_token,$data);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            \Log::error("Getting error while sending notifications:- ".$th);
        }
    }
}
