<?php

namespace App\Http\Controllers\Api\Web\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wechat\WechatApp;
use App\Http\Requests\Api\Web\Wechat\WechatWebOAuthRequest;

class WechatWebController extends Controller
{
    public function oauth(WechatWebOAuthRequest $request, $id) {
        // 记录请求参数
        $params = $request->all();
        $logger = fileLogger('wechat', 'web-oauth');
        $logger->info('request all: ' . json_encode($params));

        // 通过 code 获取用户信息
        $data = $request->validated();
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'oauth' => [
                'scopes'   => ['snsapi_login'],
            ],
            'redirect_uri' => $data['redirect_uri']
        ]);
        $oauth = $app->getOAuth();
        $authUser = $oauth->userFromCode($request->code);;
        $logger->info('auth user: ' . json_encode($authUser));

         // 记录用户信息并生成用户
        $unionid = $authUser['raw']['unionid'];
        $openid = $authUser['raw']['openid'];
        WechatAppUser::updateOrCreate([
            'app_id' => $id,
            'openid' => $openid,
        ], [
            "unionid" => $unionid,
        ]);

        $user = User::where('unionid', $unionid)->first();
        if ($user) {
            $user->update(['visited_at' => Carbon::now()]);
        } else {
            $user = User::create(['unionid' => $unionid]);
        }

        // 登录并重定向到目标页面
        return $this->loginResponse('web', $user, 1, $data['redirect_uri']);
    }
}
