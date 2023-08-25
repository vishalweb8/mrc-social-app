<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderKeyIdAndUniqueRequestIdToPaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('OderKeyId')->nullable()->after('order_id');
            $table->string('UniqueRequestId')->nullable()->after('OderKeyId');
            $table->string('status_message')->nullable()->after('status');    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('OderKeyId');
            $table->dropColumn('UniqueRequestId');
            $table->dropColumn('status_message');
        });
    }
}
