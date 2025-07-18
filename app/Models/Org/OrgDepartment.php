<?php

namespace App\Models\Org;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrgDepartment extends Model
{
    use NodeTrait;

    protected $table = 'org_departments';
    protected $fillable = ['org_id', 'name', 'member_count'];
    
    // 定义嵌套集合字段
    protected $leftColumn = '_lft';
    protected $rightColumn = '_rgt';
    protected $parentColumn = 'parent_id';
    protected $depthColumn = 'depth';
    
    // 关联组织
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }
    
    // 关联部门成员
    public function members(): HasMany
    {
        return $this->hasMany(OrgDepartmentUser::class, 'dept_id');
    }
    
    /**
     * 获取部门树
     */
    public static function getTree($orgId)
    {
        $root = self::where('org_id', $orgId)
            ->whereIsRoot()
            ->first();

        if (!$root) {
            return null;
        }
        $root->children = $root->descendants()->withDepth()->get()->toTree();
        return $root;
    }
    
    /**
     * 创建子部门
     */
    public function createChild($data)
    {
        $child = $this->children()->create($data);
        return $child;
    }
}