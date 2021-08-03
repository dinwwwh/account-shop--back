<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class RulesCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if (!is_array($value) || !$this->checkRecursively($value)) return json_encode([]);
        return json_encode($value);
    }

    /**
     * Check where given data is string rules
     *
     * @param array $array
     * @return bool
     */
    public function checkRecursively(array $array): bool
    {
        $valid = true;
        foreach ($array as $item) {
            if (is_array($item)) $valid = $this->checkRecursively($item);
            elseif (!is_string($item)) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }
}
