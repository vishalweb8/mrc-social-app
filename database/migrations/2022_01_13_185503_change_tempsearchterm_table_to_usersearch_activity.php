<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTempsearchtermTableToUsersearchActivity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('temp_search_term', 'user_search_activities');   
        Schema::table('user_search_activities', function (Blueprint $table) { 
            $table->string('latitude',50);
            $table->string('longitude',50);
            $table->string('ip_address',30);
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('user_search_activities', 'temp_search_term');
        Schema::table('temp_search_term', function (Blueprint $table) { 
           $table->dropColumn(['latitude', 'longitude', 'ip_address']);
        });  
    }
}
