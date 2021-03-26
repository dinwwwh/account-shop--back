<?php
namespace App\Helpers;

class AccountConfigHelper {
    public static function makePower(...$values)
    {
        return [
            'can_read_sensitive_info' => $values[0] ?? false,
            'can_update_sensitive_info' => $values[0] ?? false,
        ];
    }
}
