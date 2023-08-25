<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnInTempSearchTermTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_search_term', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->after('id');
            $table->unsignedInteger('result_count')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_search_term', function (Blueprint $table) {
            $table->dropColumn(['user_id','result_count']);
        });
    }
}
