<?php

namespace App\Utils;
use Illuminate\Support\Carbon;
use Firebase\JWT\JWT as JWTBase;
use Firebase\JWT\Key;

class JWT
{
    public static function login($user, $remember = false)
    {
        $expDays = static::expired_days($remember);
        $userType = 'user';
        $payload = [
            'iat' => time(),
            'iss' => url('/'),
            'exp' => Carbon::now()->addRealDays($expDays)->getTimestamp(),
            'aud' => env('APP_URL'),
            'sub' => $user->id,
            'type'=> $userType
        ];

        $logger = fileLogger('user-register', 'jwt');
        $logger->info('Landing_page_JWT '.json_encode($payload));
        return JWTBase::encode($payload, static::key(), 'HS256');
    }

    public static function encode($payload)
    {
        $payload = [
            'iat' => time(),
            'iss' => url('/'),
            'exp' => Carbon::now()->addRealDays(7)->getTimestamp(),
            'aud' => env('APP_URL'),
        ] + $payload;

        return JWTBase::encode($payload, static::key(), 'HS256');
    }

    public static function decode($jwt)
    {
        try {
            return JWTBase::decode($jwt, new Key(static::key(), 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function expired_days($remember)
    {
        $days = $remember ? config('auth.jwt.expired') : 1;
        return $days;
    }

    public static function expired_mins($remember) {
        $days = static::expired_days($remember);
        return $days * 24 * 60;
    }

    protected static function key()
    {
        return config('auth.jwt.secret');
    }
}
