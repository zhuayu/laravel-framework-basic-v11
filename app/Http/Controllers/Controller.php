<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // 成功返回封装
    public function success($data = [], $message = 'success')
    {
        return response()->json([
            'error_code' => 0,
            'msg' => $message,
            'data' => $data,
        ]);
    }

    // 失败返回封装
    public function error($code = 1, $message = '', $status = 200)
    {
        return response()->json([
            'error_code' => $code,
            'msg' => $message,
        ], $status);
    }
}
