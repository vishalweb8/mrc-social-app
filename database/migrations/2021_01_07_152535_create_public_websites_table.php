<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_websites', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('website_name')->unique();
            $table->string('website_slug_name')->unique();
            $table->unsignedInteger('template_id');
            $table->unsignedInteger('pw_template_color_id')->nullable();
            $table->unsignedInteger('pw_plan_id');
            $table->date('pw_plan_start_date')->nullable();
            $table->date('pw_plan_end_date')->nullable();
            $table->unsignedInteger('pw_payment_id')->nullable();
            $table->string('pw_domain')->nullable();
            $table->string('pw_slug_domain')->nullable();
            $table->integer('pw_type')->comment('1-ryuva,2-domain');
            $table->integer('status')->default(0)->comment('0-Pending,1-Submitted,2-Live,3-Paused,4-Removed');
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
        Schema::dropIfExists('public_websites');
    }
}
