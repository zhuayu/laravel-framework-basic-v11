<?php

namespace App\Http\Controllers\Api\Admin\Vip;

use App\Http\Controllers\Controller;
use App\Models\Vip\VipSku;

class VipSkuController extends Controller
{
    public function index(){
        $skus = VipSku::orderBy('id', 'desc')->get();
        return $this->success($skus);
    }
}