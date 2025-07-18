<?php

namespace App\Http\Controllers\Api\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Web\User\UserUpdateRequest;
use App\Http\Requests\Api\Web\User\UserLoginByAccountRequest;
use App\Http\Requests\Api\Web\User\UserLoginByPhoneRequest;
use App\Http\Requests\Api\Web\User\UserLoginSendSMSRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Overtrue\EasySms\PhoneNumber;
use Overtrue\EasySms\EasySms;
use Illuminate\Support\Str;
use Auth;

class UserController extends Controller
{
    public function userInfo() {
        $user = Auth::user();
        return $this->success($user);
    }

    public function loginByAccount(UserLoginByAccountRequest $request) {
        $data = $request->validated();
        $user = User::where('account', $data['account'])->first();
        if ($user && $user->disabled) {
            return $this->error(403, '该账号已被禁用');
        }

        // 情况1：用户存在且密码正确
        if ($user && Hash::check($data['password'], $user->password)) {
            return $this->loginResponse('web', $user, 1);
        }

        // 情况2：用户存在但密码错误
        if ($user) {
            return $this->error(1, "密码错误");
        }

        // 情况3:自动创建用户
        $newUser = User::create([
            'account' => $data['account'],
            'password' => $data['password'], // 自动触发模型中的哈希转换
            'name' => $data['account'],
        ]);

        return $this->loginResponse('web', $newUser, 1);
    }

    public function bindAccount(UserLoginByAccountRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        // 检查账户是否已被其他用户使用
        $existingUser = User::where('account', $data['account'])
            ->where('id', '!=', $user->id)
            ->first();
        if ($existingUser) {
            return $this->error(1, "该账户已被其他用户使用");
        }

        $user->update([
            'account' => $data['account'],
            'password' => $data['password'],
        ]);
        return $this->success(null, '账户密码设置成功');
    }

    public function loginSendSMS(UserLoginSendSMSRequest $request)
    {
        $data = $request->validated();
        $phonePrefix = $data['phone_prefix'];
        $countryCode = ltrim($phonePrefix, '+');
        $phone = $data['phone'];

        // 检查用户状态
        $existingUser = User::where('phone_prefix', $phonePrefix)
            ->where('phone', $phone)
             ->first();

        if ($existingUser && $existingUser->disabled) {
            return $this->error(403, '该账号已被禁用');
        }

        $code = app()->environment('production') 
            ? str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT)
            : '1234';

        if (app()->environment('production')) {
            try {
                $number = new PhoneNumber($phone, $countryCode);
                $easySms = new EasySms(config('easysms'));
                
                $easySms->send($number, [
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
            'phone_prefix' => $phonePrefix,
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

    public function loginByPhone(UserLoginByPhoneRequest $request)
    {
        $data = $request->validated();
        $result = $this->verifySMSCode($data);

        if ($result !== true) {
            return $result; // 返回错误响应
        }

        // 查找或创建用户
        $user = User::firstOrCreate(
            ['phone' => $data['phone']],
            ['phone_prefix' => $data['phone_prefix']]
        );

        if ($user->disabled) {
            return $this->error(403, '该账号已被禁用');
        }

        return $this->loginResponse('web', $user, 1);
    }

    public function bindPhone(UserLoginByPhoneRequest $request) {
        $data = $request->validated();
        $result = $this->verifySMSCode($data);

        if ($result !== true) {
            return $result; // 返回错误响应
        }

        // 检查手机号是否已被绑定
        if (User::where('phone', $data['phone'])->exists()) {
            return $this->error(409, "该手机号已被其他用户绑定");
        }

        // 更新当前用户的手机号
        Auth::user()->update([
            'phone_prefix' => $data['phone_prefix'],
            'phone' => $data['phone'],
        ]);

        return $this->success(null, "手机绑定成功");
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
        if ($cacheData['phone_prefix'] !== $data['phone_prefix'] || 
            $cacheData['phone'] !== $data['phone']) {
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
