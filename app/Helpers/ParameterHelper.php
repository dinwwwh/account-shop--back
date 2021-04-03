<?php

namespace App\Helpers;

use \Illuminate\Database\Eloquent\Collection;

class ParameterHelper
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
        if (is_array($parameters[0]) || ($parameters[0] instanceof \Illuminate\Database\Eloquent\Collection)) {
            $parameters = $parameters[0];
        }

        return $parameters;
    }
}
