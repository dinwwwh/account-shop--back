<?php

namespace App\Models\Pivot;

use App\Models\Account;
use App\Models\GameInfo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountHasGameInfos extends Pivot
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
    public function gameInfo()
    {
        return $this->belongsTo(GameInfo::class);
    }
}
