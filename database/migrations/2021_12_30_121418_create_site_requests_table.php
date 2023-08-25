<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index();
            $table->unsignedInteger('sender_by')->index();
            $table->unsignedInteger('user_id')->index();
            $table->tinyInteger('status')->index()->default(0)->comment('0: Pending, 1: Accepted, 2: Rejected');
            $table->dateTime('joined_at')->index()->nullable();
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('sender_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_requests');
    }
}
