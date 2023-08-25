<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnIsClosedInAdvertisementsTable extends Migration
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
            $table->integer('is_closed')->default(1)->after('sponsored');
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
            $table->dropColumn([
                'is_closed'
            ]);
        });
    }
}
