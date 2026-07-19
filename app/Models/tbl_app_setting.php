<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class tbl_app_setting extends Model
{
    protected $table = 'tbl_app_settings';
    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'setting_key',
        'setting_value',
    ];

    public const KEY_SHOP_PROFIT_PERCENTAGE = 'shop_profit_percentage';
    public const KEY_INVESTOR_SPLIT_MODE = 'investor_split_mode';

    public static function getValue(string $key, $default = null)
    {
        $settings = static::allCached();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value === null ? null : (string) $value]
        );
        Cache::forget('app_settings_map');
    }

    public static function shopProfitPercentage(): float
    {
        return round((float) static::getValue(self::KEY_SHOP_PROFIT_PERCENTAGE, 0), 2);
    }

    public static function investorSplitMode(): string
    {
        $mode = static::getValue(self::KEY_INVESTOR_SPLIT_MODE, 'custom');

        return $mode === 'investment' ? 'investment' : 'custom';
    }

    public static function allCached(): array
    {
        return Cache::remember('app_settings_map', 60, function () {
            return static::query()->pluck('setting_value', 'setting_key')->all();
        });
    }
}
