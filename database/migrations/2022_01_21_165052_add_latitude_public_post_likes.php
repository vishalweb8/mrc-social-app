<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatitudePublicPostLikes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_post_likes', function (Blueprint $table) {
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('ip_address', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_post_likes', function (Blueprint $table) {
            $table->dropColumn(['latitude']);
            $table->dropColumn(['longitude']);
            $table->dropColumn(['ip_address']);
        });
    }
}
