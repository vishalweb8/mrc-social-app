<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicWebsiteTempletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_website_templets', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('preview_image');
            $table->string('preview_image_thumb');
            $table->longText('template_html');
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
        Schema::dropIfExists('public_website_templets');
    }
}
