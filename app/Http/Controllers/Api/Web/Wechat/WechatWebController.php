<?php

namespace App\Http\Controllers\Api\Web\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wechat\WechatApp;
use App\Http\Requests\Api\Web\Wechat\WechatWebOAuthRequest;

class WechatWebController extends Controller
{
        public function oauth(WechatWebOAuthRequest $request, $id) {

        $params = $request->all();
        $logger = fileLogger('wechat', 'web-oauth');
        $logger->info('request all: ' . json_encode($params));
        
        $data = $request->validated();
        $redirect_uri = $data['redirect_uri'];
        $platform = $data['platform'];
        
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'oauth' => [
                'scopes'   => ['snsapi_login'],
            ],
            'redirect_uri' => $request->redirect_uri
        ]);
        $oauth = $app->getOAuth();
        $authUser = $oauth->userFromCode($request->code);;
        $logger->info('auth user: ' . json_encode($authUser));

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

        return $this->loginResponse($platform, $user, 1, $redirect_uri);
    }
}
