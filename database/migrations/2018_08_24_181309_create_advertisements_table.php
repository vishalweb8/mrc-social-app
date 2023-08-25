<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('ads_type')->default(0)->comment('0 - Buy, 1 - Sell, 2 - Service');
            $table->integer('user_id')->unsigned();            
            $table->string('name', 100);
            $table->string('description', 2000)->nullable();
            $table->string('ads_slug', 100)->nullable()->comment('Ads name slug');
            $table->string('price', 100)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('street_address', 255)->nullable();
            $table->integer('country')->unsigned()->nullable();
            $table->integer('state')->unsigned()->nullable();
            $table->string('city', 100)->nullable();
            $table->string('pincode',8)->nullable();
            $table->string('latitude',100)->nullable();
            $table->string('longitude',100)->nullable();
            $table->tinyInteger('promoted')->default(0)->comment('0 - Not promoted, 1 - Promoted');
            
            $table->bigInteger('interest_count')->default(0)->comment('Business visits counts');
            $table->bigInteger('visit_count')->default(0)->comment('Business visits counts');

            $table->tinyInteger('approved')->default(0)->comment('0 - Pending, 1 - Approved, 2 - Rejected');
            $table->integer('approved_by')->nullable();

            $table->tinyInteger('sponsored')->default(0)->comment('0 - No, 1 - Yes');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('country')->references('id')->on('country');
            $table->foreign('state')->references('id')->on('state');

            $table->index('ads_type');
            $table->index('name');            
            $table->index('city');
            $table->index('approved');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advertisements', function($table) {
            $table->dropForeign('advertisements_user_id_foreign');
            $table->dropForeign('advertisements_country_foreign');
            $table->dropForeign('advertisements_state_foreign');
            $table->dropColumn([
                'user_id',
                'country',
                'state'
            ]);
            $table->dropIndex('ads_type'); 
            $table->dropIndex('name'); 
            $table->dropIndex('city');
            $table->dropIndex('approved');
        });

        Schema::dropIfExists('advertisements');
    }
}
