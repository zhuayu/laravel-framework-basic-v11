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
        $user = Auth::id();
        return $this->success($user);
    }

    public function loginByAccount(UserLoginByAccountRequest $request) {
        $data = $request->validated();

        // 查找用户
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
            return response()->json(['message' => '密码错误'], 401);
        }

        // 情况3:自动创建用户
        $newUser = User::create([
            'account' => $data['account'],
            'password' => $data['password'], // 自动触发模型中的哈希转换
            'name' => $data['account'],
        ]);

        return $this->loginResponse('web', $newUser, 1);
    }

    public function loginSendSMS(UserLoginSendSMSRequest $request)
    {
        $data = $request->validated();
        $phonePrefix = $request->phone_prefix;
        $countryCode = ltrim($phonePrefix, '+');
        $phone = $request->phone;

        // 检查用户状态
        $existingUser = User::where('phone_prefix', $phonePrefix)
            ->where('phone', $phone)
             ->first();

        if ($existingUser && $existingUser->disabled) {
            return $this->error(403, '该账号已被禁用');
        }

        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            $number = new PhoneNumber($phone, $countryCode);
            try {
                $easySms = new EasySms(config('easysms'));
                $template = config('easysms.gateways.aliyun.templates.register');
                $result = $easySms->send($number, [
                    'template' => $template,
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                return $this->error(500, $message ?: '短信发送异常');
            }
        }

        $expiredMinutes = 5;
        $key = 'login_sms_code_' . \Str::random(15);
        $expiredAt = now()->addMinutes($expiredMinutes);

        \Cache::put($key, [
            'phone_prefix' => $phonePrefix,
            'phone' => $phone,
            'code' => $code,
            'attempts' => 0
        ], $expiredAt);

        return $this->success([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ], '验证码发送成功，有效期 ' . $expiredMinutes . ' 分钟');
    }

    public function loginByPhone(UserLoginByPhoneRequest $request)
    {
        $key = $request->key;
        $phonePrefix = $request->phone_prefix;
        $phone = $request->phone;
        $code = $request->code;

        $verifyData = \Cache::get($key);

        if (!$verifyData) {
            return $this->error(400, '验证码已失效');
        }

        // 验证国家代码和手机号
        if ($verifyData['phone_prefix'] !== $phonePrefix || $verifyData['phone'] !== $phone) {
            return $this->error(400, '手机号码或国家代码不匹配');
        }

        // 检查尝试次数
        if (isset($verifyData['attempts']) && $verifyData['attempts'] >= 5) {
            \Cache::forget($key);
            return $this->error(429, '尝试次数过多，请重新获取验证码');
        }

        if ($verifyData['code'] !== $code) {
            // 增加尝试次数
            $verifyData['attempts'] = ($verifyData['attempts'] ?? 0) + 1;
            \Cache::put($key, $verifyData, \Carbon\Carbon::parse($verifyData['expired_at'] ?? now()->addMinutes(5)));
            $remaining = 5 - $verifyData['attempts'];
            return $this->error(400, "验证码错误，还剩 {$remaining} 次尝试机会");
        }

        // 验证成功，清除缓存
        \Cache::forget($key);

        // 查找用户
        $user = User::where('phone_prefix', $phonePrefix)->where('phone', $phone)->first();

        if ($user) {
            if ($user->disabled) {
                return $this->error(403, '该账号已被禁用');
            }
        } else {
            // 创建新用户（国际手机号用户）
            $user = User::create([
                'phone_prefix' => $phonePrefix,
                'phone' => $phone,
            ]);
        }

        // 执行登录
        return $this->loginResponse('web', $user, 1);
    }

}
