<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use OwenIt\Auditing\Contracts\Auditable;

class Coupon extends Model implements Auditable
{
    use HasFactory,
        \OwenIt\Auditing\Auditable;

    /**
     * The attributes & relationships that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'audits', #Contain history changes of this model
        'code',
    ];

    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'description',
        'amount',
        'used_amount',

        'maximum_value',
        'minimum_value',
        'maximum_discount',
        'minimum_discount',
        'percentage_discount',
        'direct_discount',
        'usable_at',
        'usable_closed_at',

        'price',
        'offered_at',
        'offer_closed_at',

        'creator_id',
        'latest_updater_id'
    ];

    protected $casts = [
        'buyable' => 'boolean',
        'usable_at' => 'datetime',
        'usable_closed_at' => 'datetime',
        'offered_at' => 'datetime',
        'offer_closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Custom
        static::creating(function ($query) {
            $query->creator_id = $query->creator_id ?? optional(auth()->user())->id;
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });

        static::updating(function ($query) {
            $query->latest_updater_id = $query->latest_updater_id ?? optional(auth()->user())->id;
        });
    }

    /**
     * Get user was created this model
     *
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get user was updated latest this model
     *
     */
    public function latestUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_updater_id');
    }

    /**
     * Get users bought this coupon
     *
     */
    public function buyers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * Get supported games by this coupon
     *
     */
    public function supportedGames(): MorphToMany
    {
        return $this->morphedByMany(
            Game::class,
            'couponnable'
        )
            ->withTimestamps();
    }

    /**
     * Get the value that this coupon can reduce
     * and use it now if user can use this coupon.
     *
     */
    public function calculateDiscountAndUseNow(?User $user, float $usedToCalculateDiscount, ?float $usedToLimitValue = null): float
    {
        if (!$this->isUsable($user)) return 0;

        $this->update([
            'used_amount' => $this->used_amount + 1,
        ]);

        return $this->calculateDiscount($usedToCalculateDiscount, $usedToLimitValue);
    }

    /**
     * Get the value that the coupon can reduce
     *
     */
    public function calculateDiscount(float $usedToCalculateDiscount, ?float $usedToLimitValue = null): float
    {
        # Limit value can use this coupon
        if (!is_null($usedToLimitValue)) {
            if (
                !is_null($this->maximum_value)
                && $usedToLimitValue > $this->maximum_value
            ) {
                return 0;
            }

            if (
                !is_null($this->minimum_value)
                && $usedToLimitValue < $this->minimum_value
            ) {
                return 0;
            }
        }

        # Calculate discount
        $discount = $usedToCalculateDiscount * $this->percentage_discount / 100;
        $discount += $this->direct_discount;

        # Limit discount
        $discount = !is_null($this->maximum_discount) && $discount > $this->maximum_discount
            ? $this->maximum_discount
            : $discount;
        $discount = !is_null($this->minimum_discount) && $discount < $this->minimum_discount
            ? $this->minimum_discount
            : $discount;

        return $discount;
    }

    /**
     * Check whether this coupon can use in current time
     *
     */
    public function isUsable(?User $user): bool
    {
        if (!$this->isBuyer($user)) {
            if (!$this->canUseByEveryone())
                return false;

            if ($this->getUsableAmount() <= 0) {
                return false;
            }
        }

        if (!$this->isUnexpired())
            return false;

        return true;
    }

    /**
     * Determine whether this coupon can use by everyone
     *
     */
    public function canUseByEveryone(): bool
    {
        return is_null($this->price);
    }

    /**
     * Determine whether user bought this coupon
     *
     */
    public function isBuyer(?User $user): bool
    {
        if (is_null($user)) return false;

        return !is_null(
            $this->buyers()->where($user->getKeyName(), $user->getKey())->first()
        );
    }

    /**
     * Determine whether this coupon is unexpected,
     * usable_at <= current time <= usable_closed_at
     *
     */
    public function isUnexpired(): bool
    {
        # Coupon can use in future
        if (!is_null($this->usable_at) && $this->usable_at->gte(now())) {
            return false;
        }

        # Coupon is expired
        if (!is_null($this->usable_closed_at) && $this->usable_closed_at->lte(now())) {
            return false;
        }

        return true;
    }

    /**
     * Get usable amount of this coupon.
     *
     * @return int will greater or equal 0
     */
    public function getUsableAmount(): int
    {
        if (is_null($this->amount)) return 999999;

        $result = $this->amount - $this->used_amount;
        return $result > 0 ? $result : 0;
    }
}
