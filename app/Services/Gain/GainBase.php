<?php
namespace App\Services\Gain;

use App\Models\User;
use App\Models\Gain\GainRule;
use App\Models\Gain\GainUser;
use App\Models\Gain\GainUserHistory;
use App\Services\Feishu\WebhookService;
use DB;

class GainBase {
    protected $user = null;
    protected $markRule = null;
    protected $params = null;

    public function __construct(User $user, GainRule $gainRule, array $params)
    {
        $this->user = $user;
        $this->gainRule = $gainRule;
        $this->params = $params;
    }

    /**
     * 从用户传参中找到当前规则中需要的各种参数
     * @param $gainRule
     * @param $params
     * @return array
     */
    protected function getRuleParams($gainRule, $params)
    {
        $ruleParams = explode(',', $gainRule->param);
        $result = [];
        foreach ($ruleParams as $param) {
            $result[$param] = $params[$param];
        }
        return $result;
    }

    /*
     * 计算表达式中的结果
     * @param $exp 待解析表达式
     * @param $params 替代$exp中对应变量
     */
    protected function getRuleParseResult($exp, $params)
    {
        $newExp = $exp; // 原始计算表达式
        foreach ($params as $key => $value) {
            $newExp = str_replace($key,$value,$newExp);
        }
        return eval("return $newExp ;");
    }

    /**
     * 用于子类 calculate 方法内部调用
     * @param $userRule
     * @throws \Exception
     */
    protected function setGainHistory($userRule)
    {
        $gainRule = $this->gainRule;
        $user = $this->user;

        $paramValue = $this->getRuleParams($gainRule, $this->params);
        $number = $this->getRuleParseResult($gainRule->rule, $paramValue);
        $userGain = GainUser::where([
            'user_id' => $user->id,
            'gain_id' => $gainRule->gain_id,
            'slug' => $gainRule->slug,
        ])->first();

        $preNumber = $userGain
            ? $userGain->number
            : 0;
        $curNumber = $userGain
            ? $userGain->number + $number
            : $number;

        // 消费的情况
        if($curNumber < 0) {
            throw new \Exception('没有足够的货币/积分可以消费');
        }

        DB::beginTransaction();
        try {
            GainUser::updateOrCreate([
                'user_id' => $user->id,
                'gain_id' => $gainRule->gain_id,
                'slug' => $gainRule->slug,
            ],[
                'number' => $curNumber,
            ]);

            // 增加存储历史记录
            GainUserHistory::create([
                'user_id' => $user->id,
                'pay_id' => isset($this->params['pay_id']) ? $this->params['pay_id'] : null,
                'slug' => $gainRule->slug,
                'gain_id' => $gainRule->gain_id,
                'gain_rule_id' => $gainRule->id,
                'name' => $gainRule->name,
                'action' => $gainRule->action,
                'param' => $gainRule->param,
                'param_value' => json_encode($paramValue),
                'number' => $number,
                'previous_total' => $preNumber,
                'current_total' => $curNumber,
                'type' => $this->params['type'],
                'remark' => $this->params['remark'] ?? null,
            ]);

            $userRule->increment('rate');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("添加货币/积分失败:" . $e->getMessage());
            $webhookService = new WebhookService();
            $webhookService->devNotification("添加货币/积分失败:" . $e->getMessage(), $e->getTraceAsString());
        }
    }
}

