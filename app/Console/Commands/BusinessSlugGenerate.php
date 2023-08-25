<?php

namespace App\Console\Commands;

use App\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BusinessSlugGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'business-slug:generate {--limit=1000 : Limit of row}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate business slug for missing slug of business';

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
        config(['sluggable.onUpdate' => true]);

        try {
            $limit = $this->option('limit');
            $businesses = Business::select('id','name')->whereNull('business_slug')->where('name','!=','')->limit($limit)->get();
            info("Start generate business slug");
            foreach ($businesses as $business) {
                $business->name = $business->name;
                $business->save();
            }
            info("End generate business slug");
            $this->info('Business slug successfully generated');
        } catch (\Throwable $th) {
            Log::error("Getting error while generate business slug".$th);
        }
        config(['sluggable.onUpdate' => false]);
    }
}
