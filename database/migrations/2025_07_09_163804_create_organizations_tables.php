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
        // 组织表
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('组织名称');
            $table->string('cover_url')->nullable()->comment('图像 url');
            $table->foreignId('creator_id')->comment('创建人ID')->constrained('users');
            $table->string('status')->default('pending')->comment('状态: pending-待审核, active-已激活, suspended-已停用, rejected-已拒绝');
            $table->unsignedInteger('member_count')->default(0)->comment('组织成员总数');
            $table->timestamps();
            $table->softDeletes()->comment('软删除标记');
        });

        // 部门表
        Schema::create('org_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->comment('所属组织ID')->constrained('organizations');
            $table->string('name', 50)->comment('部门名称');
            $table->nestedSet();
            $table->timestamps();
        });

        // 部门用户关联表
        Schema::create('org_dept_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->comment('所属组织ID')->constrained('organizations');
            $table->foreignId('dept_id')->nullable()->comment('部门ID')->constrained('org_departments');
            $table->foreignId('user_id')->nullable()->comment('用户ID')->constrained('users');
            $table->string('phone')->nullable()->comment('电话，用于邀请和导入');
            $table->string('name')->nullable()->comment('昵称');
            $table->tinyInteger('status')->default(0)->comment('状态: 0待激活, 1 已激活, 2 禁用');
            $table->timestamp('joined_at')->useCurrent()->comment('加入时间');
        });

        // 群组表
        Schema::create('org_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->comment('所属组织ID')->constrained('organizations');
            $table->string('name', 50)->comment('群组名称');
            $table->string('cover_url')->nullable()->comment('图像 url');
            $table->foreignId('creator_id')->comment('创建人ID')->constrained('users');
            $table->unsignedInteger('member_count')->default(0)->comment('群组成员总数');
            $table->timestamps();
            $table->index('org_id');
        });

        // 群组成员表
        Schema::create('org_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->comment('所属组织ID')->constrained('organizations');
            $table->foreignId('group_id')->comment('群组ID')->constrained('org_groups');
            $table->foreignId('user_id')->comment('用户ID')->constrained('users');
            $table->foreignId('creator_id')->comment('创建人ID')->constrained('users');
            $table->string('role')->default('member')->comment('角色: member-成员, admin-管理者, owner-拥有者');
            $table->timestamp('joined_at')->useCurrent()->comment('加入时间');
            $table->unique(['group_id', 'user_id'], 'group_user_unique');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_group_users');
        Schema::dropIfExists('org_groups');
        Schema::dropIfExists('org_dept_users');
        Schema::dropIfExists('org_departments');
        Schema::dropIfExists('organizations');
    }
};