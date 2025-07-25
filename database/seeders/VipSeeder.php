<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vip\Vip;
use App\Models\Vip\VipSku;

class VipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vips = $this->getVipData(new Vip());
        foreach ($vips as $vip) {
            Vip::updateOrCreate([
                'slug' => $vip['slug'],
            ], $vip);
        }
        $skus = $this->getSkuData(new VipSku());
        foreach ($skus as $sku) {
            VipSku::updateOrCreate([
                'slug' => $sku['slug'],
            ], $sku);
        }
    }

    protected function getVipData($vipModel)
    {
        $now = \Carbon\Carbon::now();
        return [
            [
                'name' => '赋能版',
                'slug' => 'VIP',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '进化版',
                'slug' => 'VVIP',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '超级版',
                'slug' => 'SVIP',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }

    protected function getSkuData($vipSkuModel)
    {
        $now = \Carbon\Carbon::now();
        $data = [
            [
                'name' => '赋能版/日',
                'slug' => 'ONE_DAY_VIP',
                'number' => 1,
                'current_price' => 1,
                'origin_price' => 1,
                'vip_id' => 1,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '赋能版/月',
                'slug' => 'ONE_MONTH_VIP',
                'number' => 30,
                'current_price' => 30,
                'origin_price' => 30,
                'vip_id' => 1,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '赋能版/年',
                'slug' => 'TWELVE_MONTH_VIP',
                'number' => 365,
                'current_price' => 199,
                'origin_price' => 199,
                'vip_id' => 1,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '进化版/日',
                'slug' => 'ONE_DAY_VVIP',
                'number' => 1,
                'current_price' => 2,
                'origin_price' => 2,
                'vip_id' => 2,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '进化版/月',
                'slug' => 'ONE_MONTH_VVIP',
                'number' => 30,
                'current_price' => 60,
                'origin_price' => 60,
                'vip_id' => 2,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '进化版/年',
                'slug' => 'TWELVE_MONTH_VVIP',
                'number' => 365,
                'current_price' => 599,
                'origin_price' => 599,
                'vip_id' => 2,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '超级版/日',
                'slug' => 'ONE_DAY_SVIP',
                'number' => 1,
                'current_price' => 3,
                'origin_price' => 3,
                'vip_id' => 3,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '超级版/月',
                'slug' => 'ONE_MONTH_SVIP',
                'number' => 30,
                'current_price' => 90,
                'origin_price' => 90,
                'vip_id' => 3,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '超级版/年',
                'slug' => 'TWELVE_MONTH_SVIP',
                'number' => 365,
                'current_price' => 1999,
                'origin_price' => 1999,
                'vip_id' => 3,
                'stock' => 9999,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        return $data;
    }
}
