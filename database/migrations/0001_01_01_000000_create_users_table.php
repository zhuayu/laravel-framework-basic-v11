<?php

  use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
     * Run the migrations.
     */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('email')->nullable()->unique();
      $table->string('unionid')->nullable()->unique()->comment('微信 unionid');
      $table->string('phone')->nullable()->unique()->comment('电话');
      $table->string('password')->nullable()->comment('密码');
      $table->string('nickname')->nullable()->comment('昵称');
      $table->string('realname')->nullable()->comment('真实姓名');
      $table->string('avatar_url')->nullable()->comment('用户图像url');
      $table->unsignedTinyInteger('gender')->nullable()->comment('性别');
      $table->datetime('visited_at')->nullable()->comment('最后登录时间');
      $table->timestamps();
    });
  }

  /**
     * Reverse the migrations.
     */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
