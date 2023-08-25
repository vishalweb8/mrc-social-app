<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeedbackColumnInBusinessBrandingInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_branding_inquiries', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_branding_inquiries', function (Blueprint $table) {
            $table->dropColumn(['feedback']);
        });
    }
}
