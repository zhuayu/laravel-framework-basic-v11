<?php

namespace App\Http\Controllers\Api\Web\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wechat\WechatApp;
use App\Http\Requests\Api\Web\Wechat\WechatMiniProgramOAuthRequest;

class WechatMiniProgramController extends Controller
{
    public function oauth(WechatMiniProgramOAuthRequest $request, $id) {

        $logger = fileLogger('wechat', 'miniprogram-oauth');
        $data = $request->validated();

        $wechatApp = WechatApp::findOrFail($id);
        $app =  new Application([
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
            'use_stable_access_token' => true,
        ]);

        try {
            // 获取 session 信息
            $session = $app->getClient()->get('/sns/jscode2session', [
                'appid' => $app->getConfig()['app_id'],
                'secret' => $app->getConfig()['secret'],
                'js_code' => $data['code'],
                'grant_type'
            ])->toArray();
            $logger->info('微信 session：' . json_encode($session));
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
