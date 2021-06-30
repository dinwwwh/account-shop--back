<?php

namespace App\PivotModels;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountHasGameInfos extends Pivot
{
    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'value' => 'json'
    ];
}
