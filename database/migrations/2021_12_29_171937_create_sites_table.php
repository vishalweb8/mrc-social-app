<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->binary('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('link')->nullable();
            $table->integer('asset_type_id')->index()->nullable();
            $table->integer('created_by')->index()->nullable();
            $table->integer('approved_by')->index()->nullable();
            $table->boolean('is_approved')->index()->default(0)->comment('0: Not Approved, 1: Approved');
            $table->boolean('visibility')->index()->default(0)->comment('0: Public, 1: Private');
            $table->boolean('is_enable_request')->index()->default(1)->comment('0: No Request, 1: Request');
            $table->boolean('status')->index()->default(1)->comment('0: Inactive, 1: Active');
            $table->boolean('is_agree')->index()->default(1)->comment('0: Not Agree, 1: Agree');
            $table->dateTime('approved_at')->index()->nullable();
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
        Schema::dropIfExists('sites');
    }
}
