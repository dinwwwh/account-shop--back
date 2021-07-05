<?php

namespace App\Models\Pivot;

use App\Models\Account;
use App\Models\AccountInfo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Redactors\RightRedactor;

class AccountAccountInfo extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'value',
        'audits', #Contain history changes of this model
    ];

    /**
     * Modify before store data changes in audit
     * Should add attributes in $hidden property above
     *
     * @var array
     * */
    protected $attributeModifiers = [
        'value' => RightRedactor::class,
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
        'value' => 'json'
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
