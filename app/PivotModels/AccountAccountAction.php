<?php

namespace App\PivotModels;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Redactors\LeftRedactor;

class AccountAccountAction extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * Modify before store in audit
     *
     * @var array
     * */
    protected $attributeModifiers = [
        'is_done' => LeftRedactor::class,
    ];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes should be cast
     *
     * @var array
     */
    protected $casts = [
        'is_done' => 'boolean',
    ];
}
