<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteContactDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_contact_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index();
            $table->string('name')->index();
            $table->string('mobile_no')->index()->nullable();
            $table->unsignedBigInteger('country_id')->index()->nullable();
            $table->unsignedBigInteger('state_id')->index()->nullable();
            $table->unsignedBigInteger('city_id')->index()->nullable();
            $table->text('address')->nullable();
            $table->string('pincode')->nullable();
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_contact_details');
    }
}
