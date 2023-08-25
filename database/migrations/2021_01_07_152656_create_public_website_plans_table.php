<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicWebsitePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_website_plans', function (Blueprint $table) {
            $table->id();
            $table->string('pw_plan_name');
            $table->string('pw_plan_features');
            $table->integer('pw_plan_mrp');
            $table->integer('pw_plan_amount');
            $table->string('pw_plan_duration');
            $table->integer('status')->comment('0-in active,1-active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_website_plans');
    }
}
