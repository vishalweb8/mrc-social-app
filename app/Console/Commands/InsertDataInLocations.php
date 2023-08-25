<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Location;

class InsertDataInLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is use for insert data in location table.';

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

            info("Statring file read");
            // Import CSV to Database
            $filepath = public_path("pincode.csv");

            // Reading file
            $file = fopen($filepath, "r");

            $importData_arr = array();
            $i = 0;
            while (($filedata = fgetcsv($file, 100000, ",")) !== FALSE) {
                $num = count($filedata);

                if ($i > 0) {

                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                }
                $i++;
            }
            fclose($file);
            info("Generate Array");
            foreach ($importData_arr as $sheetData) { 
                $data = Location::firstOrNew(['pincode' =>  $sheetData[5], "city" => $sheetData[4]]);
                $data->country = $sheetData[1];
                $data->state = $sheetData[2];
                $data->district = $sheetData[3];
                $data->city = $sheetData[4];
                $data->pincode = $sheetData[5];
                if($sheetData[1] == 'India'){
                    $data->country_code = "+91";
                }
                if ($sheetData[5]) {
                    $data->save();
                }
            }
            info("Data inserted");
        } catch (\Throwable $th) {
            Log::error("Getting error while insert data in location" . $th);
        }
    }
}
