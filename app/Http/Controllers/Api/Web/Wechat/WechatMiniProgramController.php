<?php

namespace App\Http\Controllers\Api\Web\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wechat\WechatApp;
use App\Http\Requests\Api\Web\Wechat\WechatMiniProgramOAuthRequest;

class WechatMiniProgramController extends Controller
{
    public function oauth(WechatMiniProgramOAuthRequest $request, $id) {
        $data = $request->validated();
        $wechatApp = WechatApp::findOrFail($id);
        $app =  new Application([
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
            'use_stable_access_token' => true,
        ]);

        try {
            // 获取 session 用户信息
            $session = $app->getClient()->get('/sns/jscode2session', [
                'appid' => $app->getConfig()['app_id'],
                'secret' => $app->getConfig()['secret'],
                'js_code' => $data['code'],
                'grant_type'
            ])->toArray();
            $logger = fileLogger('wechat', 'miniprogram-oauth');
            $logger->info('session：' . json_encode($session));


            // 用户注册
            WechatAppUser::updateOrCreate([
                'app_id' => $id,
                'openid' => $session['openid'],
            ], [
                "unionid" => $session['unionid'],
                "session_key" => $session['session_key'],
            ]);
            $user = User::where(['unionid' => $session['unionid']])->first();
            if (!$user) {
                $user = User::create($session['unionid']);
            } else {
                $user->update(['visited_at' => Carbon::now()]);
            }

            // 手动生成 Token 返回小程序客户端
            $token = JWT::login($user, 'miniprogram', 1);
            return $this->success([
                'userInfo' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            \Log::error('小程序登录失败：' . $e->getMessage());
            return $this->error(500, '登录失败，请稍后再试' . $e->getMessage());
        }
    }

    // 微信手机号码授权
    public function getPhone(WechatMiniProgramOAuthRequest $request, $id)
    {
        $data = $request->validated();
        $wechatApp = WechatApp::findOrFail($id);
        $app =  new Application([
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
            'use_stable_access_token' => true,
        ]);
        $phoneData = $app->getClient()->postJson('/wxa/business/getuserphonenumber', [
            'code' => $data['code']
        ])->toArray();
        return $this->success($phoneData);
    }

}
