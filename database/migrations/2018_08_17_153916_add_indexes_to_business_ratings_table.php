<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToBusinessRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('business_ratings', function($table) {
            $table->index('user_id'); 
            $table->index('business_id');
            $table->index('rating');
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
        Schema::table('business_ratings', function($table) {
            $table->dropIndex('user_id'); 
            $table->dropIndex('business_id');
            $table->dropIndex('rating');
        });
    }
}
