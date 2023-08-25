<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('business', function($table) {
            $table->index('user_id'); 
            $table->index('created_by'); 
            $table->index('agent_user'); 
            $table->index('category_id'); 
            $table->index('parent_category'); 
            $table->index('city'); 
            $table->index('mobile'); 
            $table->index('promoted'); 
            $table->index('approved'); 
            $table->index('membership_type');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('business', function($table) {
            $table->dropIndex('user_id'); 
            $table->dropIndex('created_by'); 
            $table->dropIndex('agent_user'); 
            $table->dropIndex('category_id'); 
            $table->dropIndex('parent_category'); 
            $table->dropIndex('city'); 
            $table->dropIndex('mobile'); 
            $table->dropIndex('promoted'); 
            $table->dropIndex('approved');
            $table->dropIndex('membership_type');
        });
    }
}
