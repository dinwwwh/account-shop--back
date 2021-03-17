<?php

namespace App\Actions;

use App\Models\Account;

class UpdatingAccount
{
    static public function make(Account $account)
    {
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
