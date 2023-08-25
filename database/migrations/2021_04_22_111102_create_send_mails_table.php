<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_mails', function (Blueprint $table) {
            $table->id();
			$table->string('subject');
			$table->text('mail_body')->nullable();
			$table->enum('type',['user','business'])->default('user');
			$table->unsignedInteger('start_id')->nullable();
			$table->unsignedInteger('end_id')->nullable();
			$table->unsignedInteger('number_of_sent')->default(0);
			$table->boolean('is_sent')->nullable()->comment('1- Sent, 0- Faild');
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
        Schema::dropIfExists('send_mails');
    }
}
