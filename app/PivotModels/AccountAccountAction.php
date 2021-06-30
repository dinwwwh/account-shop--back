<?php

namespace App\PivotModels;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountAccountAction extends Pivot
{
    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'is_done' => 'boolean',
    ];
}
