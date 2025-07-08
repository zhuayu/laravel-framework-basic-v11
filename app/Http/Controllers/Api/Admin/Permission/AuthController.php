<?php

namespace App\Http\Controllers\Api\Admin\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission\Administrator;
use App\Models\Permission\RoleUser;
use App\Http\Requests\Api\Admin\Permission\AdminLoginByPhoneRequest;
use App\Http\Requests\Api\Admin\Permission\AdminLoginSendSMSRequest;
use Overtrue\EasySms\EasySms;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Cache;
use DB;

class AuthController extends Controller
{
        public function loginSendSMS(AdminLoginSendSMSRequest $request)
    {
        $data = $request->validated();
        $phone = $data['phone'];

        $existingUser = Administrator::where('phone', $phone)->first();
        if (!$existingUser) {
            return $this->error(403, '没有当前手机号管理员');
        }

        $code = app()->environment('production') 
            ? str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT)
            : '1234';

        if (app()->environment('production')) {
            try {
                $easySms = new EasySms(config('easysms'));
                $easySms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data' => ['code' => $code],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $e) {
                $message = $e->getException('aliyun')->getMessage() ?? '短信发送异常';
                return $this->error(500, $message);
            }
        }

        // 存储验证码
        $expiresAt = now()->addMinutes(5);
        $key = 'login_sms_code_' . Str::random(20);

        Cache::put($key, [
            'phone' => $phone,
            'code' => $code,
            'attempts' => 0,
            'expired_at' => $expiresAt->toDateTimeString()
        ], $expiresAt);

        return $this->success([
            'key' => $key,
            'expired_at' => $expiresAt->toDateTimeString(),
        ], '验证码发送成功，有效期5分钟');
    }

    public function loginByPhone(AdminLoginByPhoneRequest $request)
    {
        $data = $request->validated();
        $result = $this->verifySMSCode($data);

        if ($result !== true) {
            return $result; // 返回错误响应
        }

        $administrator = Administrator::where('phone', $request->phone)->first();
        if (!$administrator) {
            return $this->error(1, '该手机号无权登录，请联系管理员添加权限 ～');
        }

        if(!RoleUser::where(['user_id' => $administrator->id])->count()) {
            return $this->error(1, '该手机号无权角色权限，请联系管理员添加权限 ～');
        }
        $administrator->update([ 'visited_at' => Carbon::now()]);

        return $this->loginResponse('admin', $administrator, 1);
    }

     /**
     * 验证短信验证码 (通用方法)
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse|true
     */
    private function verifySMSCode(array $data)
    {
        $key = $data['key'];
        $cacheData = Cache::get($key);

        // 验证码不存在或已过期
        if (!$cacheData) {
            return $this->error(400, '验证码已失效');
        }

        // 检查手机号匹配
        if ($cacheData['phone'] !== $data['phone']) {
            return $this->error(400, '手机号码或国家代码不匹配');
        }

        // 检查尝试次数
        if ($cacheData['attempts'] >= 5) {
            Cache::forget($key);
            return $this->error(429, '尝试次数过多，请重新获取验证码');
        }

        // 验证码不匹配
        if ($cacheData['code'] !== $data['code']) {
            $cacheData['attempts']++;
            $expiration = Carbon::parse($cacheData['expired_at']);
            Cache::put($key, $cacheData, $expiration);
            
            $remaining = 5 - $cacheData['attempts'];
            return $this->error(400, "验证码错误，还剩 {$remaining} 次尝试机会");
        }

        // 验证成功，清除缓存
        Cache::forget($key);
        return true;
    }
}
