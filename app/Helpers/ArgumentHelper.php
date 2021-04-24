<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class ArgumentHelper
{
    /**
     * if first parameter is an array or like array
     * will return it or return parameter
     *
     * @return array
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function firstOrAll($parameters)
    {
        if (!isset($parameters[0])) {
            return $parameters;
        }

        if (is_array($parameters[0]) || ($parameters[0] instanceof Collection)) {
            $parameters = $parameters[0];
        }

        return $parameters;
    }
}
