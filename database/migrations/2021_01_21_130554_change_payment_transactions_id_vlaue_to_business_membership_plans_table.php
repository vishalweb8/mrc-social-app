<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePaymentTransactionsIdVlaueToBusinessMembershipPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_membership_plans', function (Blueprint $table) {
            $table->string('payment_transactions_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_membership_plans', function (Blueprint $table) {
            $table->integer('payment_transactions_id')->change();
        });
    }
}
