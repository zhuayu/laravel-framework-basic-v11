<?php

namespace App\Http\Controllers\Api\Admin\Vip;

use App\Http\Controllers\Controller;
use App\Models\Vip\VipUserHistory;
use App\Http\Requests\Api\Admin\Vip\VipUserHistoryIndexRequest;
use App\Http\Resources\PaginationCollection;

class VipUserHistoryController extends Controller
{
    public function index(VipUserHistoryIndexRequest $request){
        $data = $request->validated();
        $history = VipUserHistory::with('sku')
            ->with('user')
            ->search($data)
            ->orderBy('id', 'desc')
            ->paginate($request->input('page_size', 10));
        return new PaginationCollection($history);
    }
}