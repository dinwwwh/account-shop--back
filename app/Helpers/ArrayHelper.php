<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ArrayHelper
{
    /**
     * Convert an array with key is snackCase to camelCase
     *
     * @param array $arr
     * @return array
     */
    static function convertToCamelKey(array $arr, int $depth = 1): array
    {
        if ($depth <= 0) return $arr;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[Str::camel($key)] = is_array($value)
                ? static::convertToCamelKey($value, $depth - 1)
                : $value;
        }
        return $result;
    }
}
