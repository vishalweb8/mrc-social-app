<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name');
            $table->string('mobile_number');
            $table->float('rating')->nullable();
            $table->text('message')->nullable();
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
        Schema::dropIfExists('public_reviews');
    }
}
