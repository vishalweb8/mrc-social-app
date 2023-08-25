<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\NotificationList;
use App\Site;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class sendSiteActivityNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:site-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send site (group) activity notification to members';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $sites = Site::whereIsApproved(1)->whereStatus(1)
                ->withCount(['members' => function (Builder $query) {
                    $query->whereDate('created_at',today());
                },'posts' => function (Builder $query) {
                    $query->whereDate('created_at',today());
                }]);
            $sites = $sites->get();
            foreach($sites as $site) {
                if($site->posts_count > 0 || $site->members_count > 0) {
                    $members = User::select(['users.id','users.name'])
                    ->join('site_users', function($join) use($site) {
                        $join->on('site_users.user_id', '=', 'users.id');
                        $join->where('site_users.site_id', $site->id);
                    })
                    ->with('devices')->get();

                    if($site->posts_count > 0 && $site->members_count > 0) {
                        $message = $site->posts_count." new post shared and  {$site->members_count} new member joined in {$site->name} group.";
                    } else if($site->posts_count) {
                        $message = $site->posts_count." new post shared in {$site->name} group.";
                    } else {
                        $message = $site->members_count." new member joined in {$site->name} group.";
                    }

                    $data['title'] = $site->name.' activiy update';
                    $data['message'] = $message;
                    $data['type'] = '15';
                    $jsonData = json_encode(['site_id'=>$site->id]);
                    foreach ($members as $user) {
                        foreach ($user->devices as $device) {
                            if(!empty($device->device_token)) {
                                if($device->device_type == 1) {
                                    Helpers::pushNotificationForAndroid($device->device_token,$data);
                                } else if($device->device_type == 2) {
                                    Helpers::pushNotificationForiPhone($device->device_token,$data);
                                }
                            }
                        }
                        $data['user_id'] = $user->id;
                        $data['data'] = $jsonData;
                        NotificationList::create($data);
                    }
                    info("notify site({$site->id}) activity");
                }
            }
        } catch (\Throwable $th) {
            Log::error("Getting error while sending site activity notifications:- ".$th);
        }
    }
}
