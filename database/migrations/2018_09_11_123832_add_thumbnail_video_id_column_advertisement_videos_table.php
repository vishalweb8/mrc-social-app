<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThumbnailVideoIdColumnAdvertisementVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('advertisement_videos')) {
            Schema::table('advertisement_videos', function (Blueprint $table) {            
                $table->string('video_id', 50)->after('video_link');
                $table->string('thumbnail', 250)->after('video_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        if (Schema::hasTable('advertisement_videos')) {
            Schema::table('advertisement_videos', function (Blueprint $table) {
                $table->dropColumn([
                    'video_id',
                    'thumbnail',
                ]);
            });
        }
    }
}
