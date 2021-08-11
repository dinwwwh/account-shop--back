<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Game;
use App\Models\AccountType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Request;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function view(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user is manager of all accounts
     *
     * @param \App\Models\User $user
     * @param \App\Models\Account $account
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->can('manage', 'App\Models\AccountType');
    }

    /**
     * Determine whether the user can READ LOGIN INFOS of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function readLoginInfos(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user is buyer
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.buyer.readable_login_infos_status_codes', [])
            )
            && $user->is($account->buyer)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.readable_login_infos_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.readable_login_infos_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can READ ACCOUNT INFOS of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function readAccountInfos(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user is buyer
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.buyer.readable_account_infos_status_codes', [])
            )
            && $user->is($account->buyer)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.readable_account_infos_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.readable_login_infos_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AccountType  $accountType
     * @return mixed
     */
    public function create(User $user, AccountType $accountType)
    {
        return  $accountType->isUsableUser($user);
    }

    /**
     * Determine whether the user can approve models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function startApproving(User $user, Account $account)
    {
        if (!in_array(
            $account->latestAccountStatus->code,
            config('account.status_codes_pending_approval', [])
        )) {
            return false;
        }

        return $account->accountType->isApprovableUser($user);
    }

    /**
     * Determine whether the user can approve models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function endApproving(User $user, Account $account)
    {
        if (
            !$user->is($account->latestAccountStatus->creator)
            && !$this->manage($user)
        ) {
            return false;
        }

        return  $account->accountType->isApprovableUser($user);
    }

    /**
     * Determine whether the user can buy models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function buy(User $user, Account $account)
    {
        $bestPrice = $account->calculatePrice(
            app('request')->couponCode
        );

        if (!$user->checkEnoughGoldCoin($bestPrice))
            return false;

        return  in_array(
            $account->latestAccountStatus->code,
            config('account.buyable_status_codes', [])
        )
            && is_null($account->buyer_id);
    }

    /**
     * Determine whether the user can update GAME INFOS the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function updateGameInfos(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_game_infos_status_codes')
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.updatable_game_infos_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can update ACCOUNT INFOS of this account.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return boolean
     */
    public function updateAccountInfos(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_account_infos_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.updatable_account_infos_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can update LOGIN INFOS of this account.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return boolean
     */
    public function updateLoginInfos(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_login_infos_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.updatable_login_infos_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can update IMAGES of this account.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return boolean
     */
    public function updateImages(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_images_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.updatable_images_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can update COST of this account.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return boolean
     */
    public function updateCost(User $user, Account $account)
    {
        # user is a manager
        if (
            $this->manage($user)
        ) {
            return true;
        }

        # user can approve account
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.approver.updatable_cost_status_codes', [])
            )
            && $this->endApproving($user, $account)
        ) {
            return true;
        }

        # user is creator
        if (
            in_array(
                $account->latestAccountStatus->code,
                config('account.creator.updatable_cost_status_codes', [])
            )
            && $user->is($account->creator)
        ) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function delete(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function restore(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return mixed
     */
    public function forceDelete(User $user, Account $account)
    {
        //
    }
}
