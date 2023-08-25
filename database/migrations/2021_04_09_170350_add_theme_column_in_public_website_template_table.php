<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThemeColumnInPublicWebsiteTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_website_templets', function (Blueprint $table) {
            $table->string('template_theme')->after('template_html')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_website_templets', function (Blueprint $table) {
            $table->dropColumn(['template_theme']);
        });
    }
}
