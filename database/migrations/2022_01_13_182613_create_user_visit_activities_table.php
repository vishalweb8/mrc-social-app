<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVisitActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_visit_activities', function (Blueprint $table) {
            $table->id(); 
            $table->integer('user_id'); 
            $table->integer('entity_id');
            $table->string('entity_type',50);
            $table->string('latitude',50);
            $table->string('longitude',50);
            $table->string('ip_address',30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_visit_activities');
    }
}
