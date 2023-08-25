<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobAppliedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_applieds', function (Blueprint $table) {
            $table->id();
            $table->integer('job_vacancy_id');
            $table->integer('user_id');
            $table->string('email',50);
            $table->string('mobile_no',20);
            $table->integer('experience');
            $table->string('document_file');
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
        Schema::dropIfExists('job_applieds');
    }
}
