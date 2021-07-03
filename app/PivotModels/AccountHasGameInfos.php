<?php

namespace App\PivotModels;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;

class AccountHasGameInfos extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'value' => 'json'
    ];
}
