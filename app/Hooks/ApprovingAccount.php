<?php

namespace App\Hooks;

use App\Models\Account;

class ApprovingAccount
{
    static public function  make(Account $account)
    {
        // Handle exception status code
        if (!in_array($account->status_code, config('account.status_codes.list'))) {
            $account->status_code = config('account.status_codes.default');
        }

        switch ($account->status_code) {
            case 'value':
                # code...
                break;

            default:
                # code...
                break;
        }

        return $account;
    }
}
