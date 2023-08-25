<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAdminCommentInAgentRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('agent_request')) {
            Schema::table('agent_request', function (Blueprint $table) {            
                $table->string('admin_comment', 1000)->nullable()->after('comment');
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
        if (Schema::hasTable('agent_request')) {
            Schema::table('agent_request', function (Blueprint $table) {
                $table->dropColumn([
                    'admin_comment'
                ]);
            });
        }
    }
}
