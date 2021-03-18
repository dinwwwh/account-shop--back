<?php

namespace App\Hooks;

use App\Models\Account;

class ApprovingAccountHook
{
    static public function  execute(Account $account)
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
    }
}
