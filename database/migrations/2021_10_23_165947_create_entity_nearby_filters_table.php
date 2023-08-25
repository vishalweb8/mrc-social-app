<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityNearbyFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_nearby_filters', function (Blueprint $table) {
            $table->id();
            $table->integer('entity_id');
            $table->boolean('is_enable_filter')->default(0);
            $table->integer('top_limit')->default(5);
            $table->string('asset_type_id')->nullable();
            $table->string('title')->nullable();
            $table->text('sql_query')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_nearby_filters');
    }
}
