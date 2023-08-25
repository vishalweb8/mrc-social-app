<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFiledInRegisterOtpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_register_otp', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->enum('type',['register','password'])->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_register_otp', function (Blueprint $table) {
            $table->dropColumn(['email','type']);
        });
    }
}
