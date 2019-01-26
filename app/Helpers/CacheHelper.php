<?php

namespace App\Helpers;


use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Delete all chosen keys from cache
     * @param array $keys
     */
    public static function forgetIfExists(array $keys) {
        foreach ($keys as $key) {
            if (Cache::has($key)) {
                Cache::forget($key);
            }
        }
    }
}