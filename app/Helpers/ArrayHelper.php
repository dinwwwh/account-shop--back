<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ArrayHelper
{
    /**
     * Convert all- array keys to CAMEL CASE (like: proAnApple)
     *
     * @param array $arr
     * @return array
     */
    static function convertArrayKeysToCamelCase(array $arr, int $depth = 1): array
    {
        if ($depth == 0) return $arr;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[Str::camel($key)] = is_array($value)
                ? static::convertArrayKeysToCamelCase($value, $depth - 1)
                : $value;
        }
        return $result;
    }

    /**
     * Convert all- array keys to SNAKE CASE (like: pro_an_apple)
     *
     * @param array $arr
     * @return array
     */
    static function convertArrayKeysToSnakeCase(array $arr, int $depth = -1): array
    {
        if ($depth == 0) return $arr;
        $result = [];
        foreach ($arr as $key => $value) {
            $result[Str::snake($key)] = is_array($value)
                ? static::convertArrayKeysToSnakeCase($value, $depth - 1)
                : $value;
        }
        return $result;
    }

    /**
     * Convert an array with key is snackCase to camelCase
     * Notice this method diff array_diff function
     * And just compare values of keys that it's key existed
     *
     * @param array $arr
     * @param array $comparedArr
     * @return array
     */
    static public function diff(array $arr, array $comparedArr): array
    {
        $difference = [];
        foreach ($arr as $key => $value) {
            if (array_key_exists($key, $comparedArr) && $value != $comparedArr[$key]) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }
}
