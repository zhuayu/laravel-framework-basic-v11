<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wechat\WechatApp;

class WechatAppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            [
                'type' => 1,
                'name' => 'XXX公众号',
                'app_id' => env('WECHAT_OFFICIA_APPID'),
                'secret' => env('WECHAT_OFFICIA_SECRET'),
                'original_id' => env('WECHAT_OFFICIA_ORIGINID'),
            ],
            [
                'type' => 3,
                'name' => 'XXX网站应用',
                'app_id' => env('WECHAT_WEB_APPID'),
                'secret' => env('WECHAT_WEB_SECRET'),
            ],
            [
                'type' => 4,
                'name' => 'XXX小程序',
                'app_id' => env('WECHAT_MINIPROGRAM_APPID'),
                'secret' => env('WECHAT_MINIPROGRAM_SECRET'),
                'original_id' => env('WECHAT_MINIPROGRAM_ORIGINID'),
            ],
        ];

        foreach($apps as $app){
            WechatApp::updateOrCreate(['app_id' => $app['app_id']], $app);
        }
    }
}
