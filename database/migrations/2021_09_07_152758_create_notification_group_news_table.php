<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationGroupNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by');
            $table->string('title',255)->nullable();
            $table->text('description')->nullable();
            $table->string('external_link',255)->nullable();
            $table->string('target_link',255)->nullable();
            $table->enum('sender_type',['all','all_business','all_member','target_all','target_member','target_business'])->nullable();
            $table->text('filters_data')->nullable();
            $table->integer('notification_count')->default(0);
            $table->enum('status',['pending','drafted','approved','rejected'])->default('pending');
            $table->integer('approved_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['created_by', 'approved_by']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_groups');
    }
}
