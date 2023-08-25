<?php

namespace App\Jobs;

use App\Business;
use App\SendMail;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
			$isSent = false;
			$count = 0;
			$startId = $this->data->start_id;
			$endId = $this->data->end_id;
			if($this->data->type == 'user') {
				$emails = User::whereBetween('id',[$startId,$endId])->where('email','<>','')->whereNotNull('email')->pluck('email')->toArray();
			} else {
				$emails = Business::whereBetween('id',[$startId,$endId])->where('email_id','<>','')->whereNotNull('email_id')->pluck('email_id')->toArray();
			}
			if(!empty($emails)) {
				Mail::send([], [], function($message) use ($emails) {
					
					$message->to(config('constant.MAIL_TO'));
					$message->replyTo(config('constant.REPLY_TO'));
					$message->bcc($emails);
					$message->subject($this->data->subject);
					$message->setBody($this->data->mail_body, 'text/html');
				});

				$failCount = count(Mail::failures());
				if($failCount > 0) {
					Log::error("Bulk emails sending failed : ".$this->data->id);
					$count = count($emails) - $failCount;
				} else {
					info("Bulk emails successfully sent to all.");
					$isSent = true;
					$count = count($emails);			
				}
			} else {
				info("Bulk emails is empty for send mail.");
			}
		} catch (\Exception $e) {
			Log::error("Getting error while sending mail job: ".$e);
		}
		SendMail::whereId($this->data->id)->update(['is_sent'=>$isSent,'number_of_sent'=>$count]);
    }
}
