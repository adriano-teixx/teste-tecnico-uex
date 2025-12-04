<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    /**
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'key',
        'value',
        'description',
    ];

    /**
     * Retrieve the stored value for the given key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $entry = static::where('key', $key)->first();

        return $entry !== null ? $entry->value : $default;
    }

    /**
     * Store the given value under the provided key.
     */
    public static function setValue(string $key, mixed $value, ?int $userId = null): static
    {
        /** @var self $setting */
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'user_id' => $userId]
        );

        return $setting;
    }

    /**
     * A setting belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
