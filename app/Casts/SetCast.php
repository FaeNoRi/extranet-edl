<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SetCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        if (is_null($value) || $value === '') {
            return [];
        }

        return explode(',', $value);
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if (is_null($value)) {
            return '';
        }

        $values = is_array($value) ? $value : [$value];

        return implode(',', $values);
    }
}
