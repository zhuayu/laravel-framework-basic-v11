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
        $table->string('unionid')->nullable()->unique()->comment('微信 unionid');
        $table->string('phone_prefix')->nullable()->comment('电话');
        $table->string('phone')->nullable()->unique()->comment('电话');
        $table->string('name')->nullable()->comment('昵称');
        $table->string('account')->nullable()->unique()->comment('帐户');;
        $table->string('password')->nullable()->comment('密码');
        $table->string('avatar_url')->nullable()->comment('用户图像url');
        $table->json('config')->nullable()->comment('配置信息');
        $table->json('extra')->nullable()->comment('额外信息');
        $table->datetime('visited_at')->nullable()->comment('最后登录时间');
        $table->unsignedTinyInteger('disabled')->default(0)->comment('软件禁用');
        $table->timestamps();
    });

    Schema::create('wechat_apps', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->smallInteger('type')->default(1)->comment('应用类型 1:服务号 2:订阅号 3:小程序 4:网页 5:App');
        $table->string('name')->comment('应用名称');
        $table->string('original_id')->nullable()->comment('微信原始 ID');
        $table->string('app_id')->unique()->comment('应用 ID');
        $table->string('secret')->comment('应用密钥');
        $table->string('token')->nullable()->comment('令牌');
        $table->string('aes_key')->nullable()->comment('消息加解密密钥');
        $table->string('payment_merchant_id')->nullable()->comment('支付商户 ID');
        $table->string('payment_key')->nullable()->comment('支付key');
        $table->string('payment_cert_path')->nullable()->comment('支付证书路径');
        $table->string('payment_key_path')->nullable()->comment('支付证书路径');
        $table->timestamps();
    });

    Schema::create('wechat_app_users', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('app_id');
        $table->string('openid')->comment('openid');
        $table->string('unionid')->nullable()->comment('unionid');
        $table->string('nickname')->nullable()->comment('昵称');
        $table->string('gender')->nullable()->comment('性别 1:男 2:女 0:未知');
        $table->string('country')->nullable()->comment('国家');
        $table->string('province')->nullable()->comment('省');
        $table->string('city')->nullable()->comment('市');
        $table->string('avatar_url', 1024)->nullable()->comment('头像');
        $table->string('session_key')->nullable()->comment('微信小程序登录态');
        $table->boolean('subscribe')->nullable()->comment('是否关注');
        $table->timestamps();
        $table->index(['app_id', 'openid']);
        $table->foreign('app_id')->references('id')->on('wechat_apps');
    });

  }

  /**
     * Reverse the migrations.
     */
  public function down(): void
  {
    Schema::dropIfExists('wechat_app_users');
    Schema::dropIfExists('wechat_apps');
    Schema::dropIfExists('users');
  }
};
