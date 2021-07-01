<?php

namespace App\Helpers;

class RequestHelper
{
    /**
     * Handle special _with attribute in $request
     *
     * @param $request
     * @return array
     */
    static function generateRequiredModelRelationships($request): array
    {
        $_with = $request->_with;
        if (is_string($_with)) {
            $_with = explode('|', $_with);
        }

        if (is_array($_with)) {
            foreach ($_with as &$item) {
                $item = trim($item);
            }
            return $_with;
        }

        return [];
    }
}
