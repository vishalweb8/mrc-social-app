<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('title');
            $table->string('category');
            $table->text('content');
            $table->enum('source',['myself','external'])->nullable();
            $table->string('external_link')->comment('when source is external then store external link')->nullable();
            $table->text('post_keywords')->nullable();
            $table->string('moderator_keywords')->nullable();
            $table->enum('type',['admin','business_user'])->default('business_user');
			$table->enum('status',['draft','active','inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_posts');
    }
}
