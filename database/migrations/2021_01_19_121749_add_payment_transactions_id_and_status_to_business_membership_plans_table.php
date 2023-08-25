<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTransactionsIdAndStatusToBusinessMembershipPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_membership_plans', function (Blueprint $table) {
            $table->string('payment_transactions_id')->nullable()->after('net_payment');
            $table->integer('status')->default(0)->after('payment_transactions_id');
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
            $table->dropColumn('payment_transactions_id');
            $table->dropColumn('status');
        });
    }
}
