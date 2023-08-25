<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAppleIdAndTokenInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apple_id')->nullable()->index()->after('google_token');
            $table->text('apple_token')->nullable()->after('apple_id');
            $table->boolean('is_verified_phone')->default(1)->index()->after('apple_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apple_id','apple_token','is_verified_phone']);
        });
    }
}
