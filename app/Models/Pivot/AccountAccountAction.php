<?php

namespace App\Models\Pivot;

use App\Models\Account;
use App\Models\AccountAction;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountAccountAction extends Pivot
{
    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'is_done' => 'boolean',
    ];

    /**
     * Get account of this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get account action of this model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountAction()
    {
        return $this->belongsTo(AccountAction::class);
    }
}
