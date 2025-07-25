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
        // VIP 商品
        Schema::create('vip', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('英文标示符');
            $table->string('name')->comment('展示名称');
            $table->timestamps();
            $table->softDeletes();
        });

        // VIP 库存量单元
        Schema::create('vip_skus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vip_id');
            $table->string('slug')->unique()->comment('英文标示符');
            $table->string('name')->comment('展示名称');
            $table->decimal('current_price')->default(0)->comment('定价(分)');
            $table->decimal('origin_price')->default(0)->comment('市场价(分)');
            $table->string('cover_url', 1024)->nullable()->comment('封面图');
            $table->unsignedBigInteger('number')->comment('天数');
            $table->unsignedInteger('sold_count')->default(0)->comment('销量');
            $table->unsignedTinyInteger('is_online')->default(0)->comment('是否上架');
            $table->unsignedBigInteger('stock')->default(0)->comment('库存');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('vip_id')->references('id')->on('vip');
        });

        // 用户 VIP 记录
        Schema::create('vip_user_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vip_id');
            $table->unsignedBigInteger('sku_id')->nullable();
            $table->string('order_id')->nullable()->comment('订单 ID');
            $table->unsignedBigInteger('number')->comment('天数');
            $table->string('remark')->nullable()->comment('备注');
            $table->tinyInteger('type')->default(1)->comment('类型：1 增加、-1 减少');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->datetime('expired_at')->nullable()->comment('过期时间');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('vip_id')->references('id')->on('vip');
            $table->foreign('sku_id')->references('id')->on('vip_skus');
        });

        // 用户拥有的 VIP
        Schema::create('vip_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vip_id');
            $table->string('slug');
            $table->datetime('expired_at')->nullable()->comment('过期时间');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['user_id', 'vip_id']);
            $table->foreign('vip_id')->references('id')->on('vip');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('vip_slug')->nullable()->comment('VIP类型');
            $table->datetime('vip_expired')->nullable()->comment('VIP过期时间');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vip_slug');
            $table->dropColumn('vip_expired');
        });
        Schema::dropIfExists('vip_user_history');
        Schema::dropIfExists('vip_users');
        Schema::dropIfExists('vip_skus');
        Schema::dropIfExists('vip');
    }
};
