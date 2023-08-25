<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeContactDetailInSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_contact_details', function (Blueprint $table) {
            if (Schema::hasColumn('site_contact_details', 'country_id')) {
                $table->dropColumn(['country_id','city_id','state_id','pincode']);
            }
            $table->integer('location_id')->nullable()->index()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_contact_details', function (Blueprint $table) {
            $table->dropColumn(['location_id']);
        });
    }
}
