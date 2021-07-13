<?php

namespace App\Helpers;

class ValidationHelper
{
    /**
     * Parse rules from an array.
     * Smarter than validation of laravel
     * Mustn't has | character in array
     *
     * @param string $rootName
     * @param array $rules
     * @return array
     */
    static function parseRulesByArray(string $rootName, array $rules)
    {
        $isExistedRootRules = key_exists('rootRules', $rules);
        $currentIndex = 0;
        $result = [
            $rootName => []
        ];
        if (key_exists('rootRules', $rules)) {
            $result[$rootName] = $rules['rootRules'];
            unset($rules['rootRules']);
        };
        foreach ($rules as $key => $rule) {
            if ($key === $currentIndex && !$isExistedRootRules) {
                $result[$rootName][] = $rule;
                $currentIndex++;
            } elseif (!is_array($rule)) {
                $result["$rootName.$key"] = $rule;
            } else {
                $result  = array_merge(
                    $result,
                    static::parseRulesByArray("$rootName.$key", $rule)
                );
            }
        }
        return $result;
    }
}
