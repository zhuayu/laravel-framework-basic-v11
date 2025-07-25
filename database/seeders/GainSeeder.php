<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gain\Gain;

class GainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gainDatas = $this->getGainData();
        foreach ($gainDatas as $gainIndex => $gain) {
            Gain::updateOrCreate([
                'slug' => $gain['slug'],
            ], $gain);
        }
    }

    protected function getGainData() {
        $now = \Carbon\Carbon::now();
        return [
            [
                'name' => '积分',
                'slug' => 'mark',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '货币',
                'slug' => 'coin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '矿',
                'slug' => 'mineral',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];
    }
}
