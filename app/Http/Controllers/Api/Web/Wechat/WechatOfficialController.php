<?php

namespace App\Http\Controllers\Api\Web\Wechat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wechat\WechatApp;
use App\Models\Wechat\WechatAppUser;
use App\Services\Wechat\Official\WechatOfficialMessageService;
use App\Http\Requests\Api\Web\Wechat\WechatOfficialOAuthRequest;
use App\Http\Requests\Api\Web\Wechat\WechatOfficialSDKConfigRequest;
use App\Http\Requests\Api\Web\Wechat\WechatOfficialQrCodeRequest;
use App\Http\Requests\Api\Web\Wechat\WechatOfficialScanAuthRequest;
use EasyWeChat\OfficialAccount\Application;
use Carbon\Carbon;
use Cache;

class WechatOfficialController extends Controller
{
    // 公众号 h5 网页授权登陆
    public function oauth(WechatOfficialOAuthRequest $request, $id)
    {
        $logger = fileLogger('wechat-official', 'h5-oauth');
        $logger->info('request params: ' . json_encode($request->all()));

        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
        ]);
        $oauth = $app->getOAuth();
        $authUser = $oauth->userFromCode($_GET['code']);
        $openid = $authUser['id'];
        $unionid = $authUser['raw']['unionid'];

        // 存在用户没有关注公众号，不能通过 openid 获取用户详情的情况
        $logger->info('auth user: ' . json_encode($authUser));
        $logger->info('user original info: ' . json_encode($authUser['raw']));

        WechatAppUser::updateOrCreate([
            'app_id' => $id,
            'openid' => $openid,
        ], [
            "unionid" => $unionid,
        ]);

        $user = User::firstWhere(['unionid' => $unionid]);
        if (!$user) {
            $user = User::create($userInfo['unionid']);
        } else {
            $user->update(['visited_at' => Carbon::now()]);
        }

        $redirect_uri = $request->redirect_uri;
        return $this->loginResponse('official', $user, 1, $redirect_uri);
    }

    public function sdkConfig(WechatOfficialSDKConfigRequest $request, $id)
    {
        $data = $request->validated();
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
        ]);
        $utils = $app->getUtils();
        $config = $utils->buildJsSdkConfig(
            url: $data['url'],
            jsApiList: $data['apis'],
            openTagList: array_key_exists('tags', $data) ? $data['tags'] : [],
            debug: false,
        );
        return $this->success($config);
    }

    public function scanQrCode(WechatOfficialQrCodeRequest $request, $id)
    {
        $data = $request->validated();
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'use_stable_access_token' => true,
            'response_type' => 'array',
        ]);

        $key = $scene . '_' . Uuid::uuid4()->getHex();
        $temporary = $app->getClient()->postJson('/cgi-bin/qrcode/create', [
            'expire_seconds' => 3600,
            'action_name' => "QR_STR_SCENE",
            'action_info' => [
                "scene" => [
                    "scene_str" => $key
                ]
            ],
        ])->toArray();
        return $this->success([
            'key' => $key,
            'url' => "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . $temporary['ticket'],
        ]);
    }

    public function scanStatus(WechatOfficialScanAuthRequest $request)
    {
        $data = $request->validated();
        $value = Cache::get($data['key']);
        return $this->success(['value' => $value]);
    }

    public function scanOAuth(WechatOfficialScanAuthRequest $request)
    {
        $data = $request->validated();
        $userId = Cache::get($data['key']);
        if (!$userId) {
            return $this->error(1, "key invalid");
        }
        $user = User::findOrFail($userId);
        $user->update(['visited_at' => Carbon::now()]);
        return $this->loginResponse($data['platform'], $user, 1);
    }

        // 用于微信公众号服务端验证(注意ENV要开启正式环境以及关闭debug模式)
    public function messageCheck($id)
    {
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
        ]);
        $response = $app->getServer()->serve();
        return $response;
    }

    // 微信公众号消息回调
    public function messageCallback(Request $request, $id)
    {
        $logger = fileLogger('wechat-official', 'message');
        $logger->info('request params: ' . json_encode($request->all()));
        $wechatApp = WechatApp::findOrFail($id);
        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
        ]);
        $server = $app->getServer();
        $server->with(function ($message, \Closure $next) use ($logger) {
            $logger->info('request message: ' . json_encode($message));
            return (new WechatOfficialMessageService())->handle($message->toArray());
        });
        return $server->serve();
    }

}
