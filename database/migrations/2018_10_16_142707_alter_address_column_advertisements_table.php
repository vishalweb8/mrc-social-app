<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddressColumnAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('advertisements', function($table) {
            $table->string('address', 500)->nullable()->change();
            $table->string('street_address', 500)->nullable()->change();
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
        Schema::table('advertisements', function($table) {
            $table->dropColumn('address');
            $table->dropColumn('street_address');
        });
    }
}
