<?php

namespace App\Models\Pivot;

use App\Models\Account;
use App\Models\AccountAction;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;

class AccountAccountAction extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [];

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
