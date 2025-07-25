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
        Schema::create('gains', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('英文标示符: 货币 coin 积分 mark');
            $table->string('name')->comment('展示名称');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gain_rules', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->unsignedBigInteger('gain_id');
            $table->string('name', 64)->comment('规则名称');
            $table->string('action', 128)->unique()->comment('规则的 action 例如: coin_regist_finish');
            $table->string('param', 128)->nullable()->comment('规则的参数逗号隔开 例如: num,uid');
            $table->string('rang')->default('once')->comment('周期范围: daily、once、none');
            $table->smallInteger('rate')->default(1)->comment('周期内奖励次数');
            $table->string('rule', 64)->nullable()->comment('增加数量或者比例，1为加1，-1为减1,*10为乘10，/10为除10 单位分');
            $table->integer('max_total')->default(-1)->comment('周期范围内允许最大增加数量，-1为没有限制');
            $table->tinyInteger('status')->default(1)->comment('状态：0 未启用 1 启用');
            $table->integer('sort')->nullable()->comment('排序');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gain_user_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('gain_id');
            $table->unsignedInteger('gain_rule_id');
            $table->string('slug');
            $table->unsignedInteger('rate')->default(0)->comment('周期内已奖励的次数');
            $table->timestamp('rate_start_at')->nullable()->comment('周期开始时间');
            $table->timestamp('rate_end_at')->nullable()->comment('周期结束时间');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'gain_rule_id', 'rate_start_at', 'rate_end_at'], 'idx_rate_st_et');
        });

        Schema::create('gain_user_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('gain_id');
            $table->string('slug')->comment('英文标示符: 货币 coin 积分 mark');
            $table->unsignedInteger('gain_rule_id');
            $table->string('pay_id')->nullable()->comment('pay项目订单id; 走支付渠道此字段不为空');
            $table->string('name', 64)->comment('规则名字');
            $table->string('action', 128)->comment('用户执行的action');
            $table->string('param', 128)->nullable()->comment('用户执行的action参数');
            $table->text('param_value')->nullable()->comment('params中扩展特殊参数的值');
            $table->integer('number')->default(0)->comment('action一次增加的量');
            $table->integer('previous_total')->default(0)->comment('上一次最后汇总的量');
            $table->integer('current_total')->default(0)->comment('当前计算后的总量');
            $table->unsignedTinyInteger('type')->comment('类型：1 支出；2 收入');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamps();
            $table->softDeletes();
        });

        // 用户
        Schema::create('gain_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('gain_id');
            $table->string('slug')->comment('英文标示符: 货币 coin 积分 mark');
            $table->integer('number')->comment('单位分');
            $table->timestamps();
            $table->unique(['user_id', 'slug']);
            $table->foreign('gain_id')->references('id')->on('gains');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {        
        Schema::dropIfExists('gain_user_rules');
        Schema::dropIfExists('gain_user_histories');
        Schema::dropIfExists('gain_users');
        Schema::dropIfExists('gain_rules');
        Schema::dropIfExists('gains');
    }
};
