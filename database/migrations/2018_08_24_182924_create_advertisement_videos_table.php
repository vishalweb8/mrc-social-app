<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('advertisement_id')->unsigned();
            $table->string('video_link',100); 
            $table->timestamps();

            $table->foreign('advertisement_id')->references('id')->on('advertisements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advertisement_videos', function($table) {
            $table->dropForeign('advertisement_videos_advertisement_id_foreign');
            $table->dropColumn([
                'advertisement_id'
            ]);
        });
        Schema::dropIfExists('advertisement_videos');
    }
}
