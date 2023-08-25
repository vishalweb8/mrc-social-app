<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessBrandingInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_branding_inquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('city')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile_number')->nullable();
			$table->enum('status',['pending','approved','rejected'])->default('pending');
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
        Schema::dropIfExists('business_branding_inquiries');
    }
}
