<?php

namespace App\Services\Wechat\Official;

use Illuminate\Support\Str;
use App\Models\Wechat\WechatApp;
use App\Models\Wechat\WechatAppUser;
use EasyWeChat\OfficialAccount\Application;
use Carbon\Carbon;
use Cache;

class WechatOfficialMessageService
{
    public function handle($message)
    {
        if (!empty($message['MsgType'])) {
            if ($message['MsgType'] == 'event') {
                $method = 'handle' . Str::studly(strtolower($message['Event'])) . Str::studly(strtolower($message['MsgType'])) . 'Message';
            } else {
                $method = 'handle' . Str::studly(strtolower($message['MsgType'])) . 'Message';
            }
            if (method_exists($this, $method) && is_callable([$this, $method])) {
                return call_user_func([$this, $method], $message);
            }
        }
        return null;
    }

    // 关注
    private function handleSubscribeEventMessage($message)
    {
        $wechatApp = WechatApp::firstWhere([
            'original_id' => $message['ToUserName'],
        ]);

        if (!$wechatApp) {
            return null;
        }

        $qrSceneKey = $message['EventKey'];
        // 来自特定生成的二维码，进行扫码关注的行为
        if (Str::startsWith($qrSceneKey, 'qrscene_login_')) {
            $key = mb_substr($qrSceneKey, 8);

            $user = $this->getOfficiaSubscribelUserInfo($message['ToUserName'], $message['FromUserName']);
            if ($user) {
                Cache::put($key, $user->id, 3600);
                $user->update(['visited_at' => Carbon::now()]);
            }
        } else {
            // 普通的关注行为
            $app = new Application([
                'original_id' => $wechatApp->original_id,
                'app_id' => $wechatApp->app_id,
                'secret' => $wechatApp->secret,
                'response_type' => 'array',
            ]);
            $officialUser = $app->getClient()->get('/cgi-bin/user/info', [
                'openid' => $message['FromUserName'],
            ])->toArray();

            WechatAppUser::updateOrCreate([
                'app_id' => $wechatApp->id,
                'openid' => $message['FromUserName'],
                'unionid' => $officialUser['unionid'],
            ], [
                'subscribe' => $officialUser['subscribe']
            ]);
        }

    }

    // 取消关注
    private function handleUnsubscribeEventMessage($message)
    {
        $wechatApp = WechatApp::firstWhere([
            'original_id' => $message['ToUserName'],
        ]);

        if (!$wechatApp) {
            return null;
        }
        $wechatAppUser = WechatAppUser::firstWhere([
            'app_id' => $wechatApp->id,
            'openid' => $message['FromUserName'],
        ]);
        if ($wechatAppUser) {
            $wechatAppUser->subscribe = 0;
            $wechatAppUser->save();
        }
    }

    // 扫码
    private function handleScanEventMessage($message)
    {
        if (empty($message['EventKey'])) {
            return null;
        }
        $key = $message['EventKey'];
        if (Str::startsWith($key, 'login_')) {
            $user = $this->getOfficiaSubscribelUserInfo($message['ToUserName'], $message['FromUserName'], $inviterId);
            if ($user) {
                Cache::put($key, $user->id, 3600);
                $user->update(['visited_at' => Carbon::now()]);
            }
        }
    }

    private function getOfficiaSubscribelUserInfo($originalId, $openid) {
        $wechatApp = WechatApp::firstWhere([
            'original_id' => $originalId,
        ]);

        if (!$wechatApp) {
            return null;
        }

        $app = new Application([
            'original_id' => $wechatApp->original_id,
            'app_id' => $wechatApp->app_id,
            'secret' => $wechatApp->secret,
            'response_type' => 'array',
            'use_stable_access_token' => true,
        ]);

        $wechatAppUser = WechatAppUser::firstOrCreate([
            'app_id' => $wechatApp->id,
            'openid' => $openid,
        ]);

        $officialUser = $app->getClient()->get('/cgi-bin/user/info', [
            'openid' => $openid,
        ])->toArray();

        $userInfo = [
            'subscribe' =>  $officialUser['subscribe'],
            'unionid' =>  $officialUser['unionid'],
        ];

        $wechatAppUser->update($userInfo);

        $user = User::firstWhere([
            'unionid' => $wechatAppUser->unionid
        ]);
        if (!$user) {
            $user = User::create(['unionid' => $wechatAppUser->unionid]);
        }
        return $user;
    }

    // 点击自定义菜单
    private function handleClickEventMessage($message)
    {
        #TBD
    }
}
