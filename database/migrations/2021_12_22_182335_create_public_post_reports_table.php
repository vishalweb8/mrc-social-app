<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicPostReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('entity_reports', function (Blueprint $table) {
            $table->integer('entity_id')->nullable()->change(); 
            $table->integer('post_id')->index()->nullable()->after('entity_id');            
            $table->integer('asset_type_id')->index()->nullable()->after('post_id');
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
            $table->dropColumn(['post_id','asset_type_id']);
        });
    }
}
