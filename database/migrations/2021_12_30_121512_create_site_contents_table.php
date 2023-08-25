<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->index();
            $table->unsignedBigInteger('content_id')->index();
            $table->unsignedInteger('shared_by')->index()->nullable();
            $table->boolean('is_shared')->index()->default(0)->comment('0: Created, 1: Shared');
            $table->tinyInteger('type')->index()->default(1)->comment('1: Post, 2: Entity, 3: Job');
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('CASCADE');
            $table->foreign('shared_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_contents');
    }
}
