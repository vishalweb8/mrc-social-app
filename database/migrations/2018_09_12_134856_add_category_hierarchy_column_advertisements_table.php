<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCategoryHierarchyColumnAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table) {            
                $table->string('category_hierarchy', 500)->nullable();
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
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn([
                    'category_hierarchy'
                ]);
            });
        }
    }
}
