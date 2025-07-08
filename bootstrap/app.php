<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {

            Route::prefix('api/web')
                ->namespace('App\Http\Controllers\Api\Web')
                ->group(base_path('routes/api_web.php'));

            Route::prefix('api/admin')
                ->namespace('App\Http\Controllers\Api\Admin')
                ->group(base_path('routes/api_admin.php'));

            Route::prefix('api/admin')
                ->namespace('App\Http\Controllers\Api\Serve')
                ->group(base_path('routes/api_serve.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,  // 添加这行注册 'auth' 别名
            'AdminPermission' => \App\Http\Middleware\AdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            // 处理 HttpExceptionInterface 异常
            if ($e instanceof HttpExceptionInterface) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'data' => null,
                        'code' => $e->getStatusCode(),
                        'msg' => $e->getMessage(),
                    ], $e->getStatusCode());
                }
            }

            // 处理 ModelNotFoundException 异常
            if ($e instanceof ModelNotFoundException) {
                $modelConst = $e->getModel() . '::MODEL_NAME';
                $modelName = defined($modelConst) ? constant($modelConst) : '模型';
                $ids = $e->getIds();
                $modelId = is_array($ids) ? implode(',', $ids) : $ids;

                return response()->json([
                    'data' => null,
                    'error_code' => 404,
                    'msg' => $modelName . "「ID：{$modelId}」不存在",
                ], 200);
            }

            // 处理 ValidationException 异常
            if ($e instanceof ValidationException) {
                $error = $e->errors();
                $firstError = reset($error)[0] ?? $e->getMessage();
                
                return response()->json([
                    'data' => null,
                    'error_code' => 422,
                    'msg' => $firstError,
                ], 200);
            }

            // 处理 NotFoundHttpException 异常
            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'data' => null,
                    'error_code' => 404,
                    'msg' => "Router Not Found",
                ], 404);
            }

            // 处理其他所有异常
            if (!$e instanceof HttpExceptionInterface) {
                // 生产环境不返回详细错误
                if (app()->environment('production')) {
                    return response()->json([
                        'data' => null,
                        'error_code' => 500,
                        'msg' => "服务问题，请联系管理员",
                    ], 500);
                }
                
                // 开发环境返回详细错误
                return response()->json([
                    'data' => null,
                    'error_code' => 500,
                    'msg' => $e->getMessage(),
                    'trace' => $e->getTrace()
                ], 500);
            }
        });
    })->create();