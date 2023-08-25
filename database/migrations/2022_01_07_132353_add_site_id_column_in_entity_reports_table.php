<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteIdColumnInEntityReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->index()->nullable();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entity_reports', function (Blueprint $table) {
            $table->dropColumn(['site_id']);
        });
    }
}
