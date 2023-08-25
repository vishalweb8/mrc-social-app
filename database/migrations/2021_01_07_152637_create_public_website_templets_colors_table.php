<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicWebsiteTempletsColorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_website_templets_colors', function (Blueprint $table) {
            $table->id();
            $table->string('color_name');
            $table->string('preview_image');
            $table->unsignedInteger('template_id');
            $table->binary('color_html');
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
        Schema::dropIfExists('public_website_templets_colors');
    }
}
