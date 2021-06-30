<?php

namespace App\PivotModels;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountAccountInfo extends Pivot
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
