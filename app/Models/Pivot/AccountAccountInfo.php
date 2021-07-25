<?php

namespace App\Models\Pivot;

use App\Models\Account;
use App\Models\AccountInfo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountAccountInfo extends Pivot
{

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'values',
    ];

    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'values' => 'array'
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
    public function accountInfo()
    {
        return $this->belongsTo(AccountInfo::class);
    }
}
