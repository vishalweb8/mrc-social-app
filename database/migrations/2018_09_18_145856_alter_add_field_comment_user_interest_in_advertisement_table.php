<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddFieldCommentUserInterestInAdvertisementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('user_interest_in_advertisement', function($table) {
            $table->string('comment', 500)->nullable()->after('advertisement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('user_interest_in_advertisement', function($table) {
            $table->dropColumn([
                'comment'
            ]);
        });
    }
}
