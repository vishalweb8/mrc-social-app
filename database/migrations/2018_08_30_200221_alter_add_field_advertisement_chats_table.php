<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddFieldAdvertisementChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('chats', function($table) {
            $table->engine = 'InnoDB';

            $table->integer('business_id')->unsigned()->nullable()->change();
            $table->integer('advertisement_id')->unsigned()->nullable()->after('business_id');
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
        Schema::table('chats', function($table) {
            $table->dropColumn([
                'advertisement_id'
            ]);
        });
    }
}
