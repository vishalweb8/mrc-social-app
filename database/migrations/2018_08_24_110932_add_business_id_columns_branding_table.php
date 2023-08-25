<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessIdColumnsBrandingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('branding')) {
            Schema::table('branding', function (Blueprint $table) {            
                $table->integer('business_id')->unsigned()->nullable()->after('type');

                $table->foreign('business_id')->references('id')->on('business');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        if (Schema::hasTable('branding')) {
            Schema::table('branding', function (Blueprint $table) {
                $table->dropForeign('branding_business_id_foreign');
                $table->dropColumn([
                    'business_id'
                ]);
            });
        }
    }
}
