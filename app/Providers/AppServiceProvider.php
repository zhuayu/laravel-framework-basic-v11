<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Utils\JWT;
use App\Models\User;
use Illuminate\Support\Str;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('web-token', function ($request) {
            $jwt = ($token = $request->header('Authorization'))
                ? Str::replaceFirst('Bearer ', '', $token)
                : $request->cookie('token');

            if (!$jwt) {
                return null;
            }

            $decode = JWT::decode($jwt);

            if (!$decode) {
                return null;
            }

            return User::find($decode->sub);
        });
    }
}
