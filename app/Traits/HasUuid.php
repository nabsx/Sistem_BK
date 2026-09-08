<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait HasUuid
 *
 * Pasang trait ini di semua Model yang primary key-nya UUID.
 * - Otomatis generate UUID v4 saat model dibuat (creating).
 * - Menonaktifkan auto-increment & set keyType ke string.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}