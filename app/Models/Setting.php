<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get all package prices as an array.
     */
    public static function packagePrices(): array
    {
        $rows = static::whereIn('key', ['price_basic', 'price_premium', 'price_deluxe', 'price_addon'])->get()->keyBy('key');
        return [
            'basic'   => (int) ($rows['price_basic']->value   ?? 199),
            'premium' => (int) ($rows['price_premium']->value ?? 269),
            'deluxe'  => (int) ($rows['price_deluxe']->value  ?? 349),
            'addon'   => (int) ($rows['price_addon']->value   ?? 25),
        ];
    }

    /**
     * Set a value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}
