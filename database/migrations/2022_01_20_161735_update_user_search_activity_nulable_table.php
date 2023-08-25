<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserSearchActivityNulableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_search_activities', function (Blueprint $table) {
            $table->string('latitude')->nullable()->change();
            $table->string('longitude')->nullable()->change();
            $table->string('ip_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_search_activities', function (Blueprint $table) {
            $table->string('latitude')->nullable(false)->change();
            $table->string('longitude')->nullable(false)->change();
            $table->string('ip_address')->nullable(false)->change();
        });
    }
}
