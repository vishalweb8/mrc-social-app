<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobVacanciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->integer('application_id');
            $table->integer('location_id');
            $table->integer('user_id');
            $table->string('title');
            $table->longText('description');
            $table->longText('external_link');
            $table->longText('address');
            $table->string('qualification');
            $table->integer('experience')->nullable();
            $table->string('workplace_type');
            $table->string('employment_type');
            $table->string('company_name');
            $table->string('type');  
            $table->string('image_url');
            $table->integer('status')->default(1)->comment("1 = active, 0 = deactive");
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
        Schema::dropIfExists('job_vacancies');
    }
}
