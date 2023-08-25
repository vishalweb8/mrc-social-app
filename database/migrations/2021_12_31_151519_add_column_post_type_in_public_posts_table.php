<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPostTypeInPublicPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_posts', function (Blueprint $table) {
            $table->tinyInteger('post_type')->index()->default(1)->comment('1: Public, 2: Group')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_posts', function (Blueprint $table) {
            $table->dropColumn(['post_type']);
        });
    }
}
