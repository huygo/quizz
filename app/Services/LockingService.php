<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LockingService
{
    /**
     * @param string $key
     * @param int $seconds
     * @return bool
     */
    public static function isLocking(string $key, int $seconds): bool
    {
        $lock = Cache::lock($key, $seconds);

        return !$lock->get();
    }

    public static function release(string $key): void
    {
        Cache::lock($key)->release();
    }

    public static function checkLocking(string $key): bool
    {
        try {
            $expiresAt = Carbon::now()->endOfDay();
            $value=Cache::get($key);
            if (!$value) {
                Cache::add($key, 1, $expiresAt);
                return true;
            }else if ($value<5) {
                Cache::put($key, ($value+1), $expiresAt);
                return true;
            }else{
                return false;
            } 
        } catch (\Exception $e) {
            return false;
        }
    }
}
