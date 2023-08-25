<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicWebsitePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_website_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pw_id');
            $table->integer('payment_amount');
            $table->date('payment_date');
            $table->string('pay_trans_id');
            $table->string('payment_status');
            $table->string('payment_message');
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
        Schema::dropIfExists('public_website_payments');
    }
}
