<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("app_setting:{$key}", 300, function () use ($key, $default) {
                if (!\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                    return $default;
                }
                $setting = static::where('key', $key)->first();
                return $setting ? $setting->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set (upsert) a setting value by key and clear its cache.
     */
    public static function set(string $key, mixed $value): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                \Illuminate\Support\Facades\Schema::create('app_settings', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
            }
            static::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget("app_setting:{$key}");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AppSetting::set failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the result release datetime as a UTC Carbon instance.
     * Defaults to 2026-09-16 12:00:00 UTC (= 5:30 PM IST).
     */
    public static function resultReleaseDatetime(): \Carbon\Carbon
    {
        $raw = static::get('result_release_datetime', '2026-09-16 12:00:00');
        return \Carbon\Carbon::parse($raw, 'UTC');
    }

    /**
     * Check whether results have been released (release time has passed).
     */
    public static function resultsReleased(): bool
    {
        return now('UTC')->gte(static::resultReleaseDatetime());
    }
}
