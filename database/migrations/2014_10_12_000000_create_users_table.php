<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->string('name', 255);
            $table->string('phone', 255)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('short_description', 255)->nullable();
            $table->date('birthday');
            $table->string('gender', 255);
            $table->string('relationship_status', 255)->nullable();
            $table->string('hometown', 255)->nullable();
            $table->string('current_residence', 255)->nullable();
            $table->string('job', 255)->nullable();
            $table->string('education', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->bigInteger('role_id')->default(2);
            $table->tinyInteger('user_status_code')->default(0);
            $table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists('users');
    }
}
