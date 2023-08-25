<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsOfAdvertisementsNotificationListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('notification_list', function($table) {
            $table->integer('advertisement_id')->nullable()->after('business_name');
            $table->string('advertisement_name')->nullable()->after('advertisement_id');
            $table->integer('interest_id')->nullable()->after('advertisement_name');
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
        Schema::table('notification_list', function($table) {
            $table->dropColumn([
                'advertisement_id',
                'advertisement_name',
                'interest_id'
            ]);
        });
    }
}
