<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('advertisement_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->tinyInteger('category_type')->default(0)->comment('0 - Parent, 1 - Child');
            $table->timestamps();

            $table->foreign('advertisement_id')->references('id')->on('advertisements');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advertisement_categories', function($table) {
            $table->dropForeign('advertisement_categories_advertisement_id_foreign');
            $table->dropForeign('advertisement_categories_category_id_foreign');
            $table->dropColumn([
                'advertisement_id',
                'category_id'
            ]);
        });
        Schema::dropIfExists('advertisement_categories');
    }
}
