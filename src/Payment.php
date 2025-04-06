<?php

namespace Freemius\Laravel;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Freemius\Laravel\Database\Factories\PaymentFactory;

/**
 * @property int $id
 * @property string|int $billable_id
 * @property string $billable_type
 * @property string $lemon_squeezy_id
 * @property string $identifier
 * @property string $product_id
 * @property string $variant_id
 * @property int $Payment_number
 * @property string $currency
 * @property int $subtotal
 * @property int $discount_total
 * @property int $tax
 * @property int $total
 * @property string|null $tax_name
 * @property string $status
 * @property string|null $receipt_url
 * @property bool $refunded
 * @property CarbonInterface|null $refunded_at
 * @property CarbonInterface $Paymented_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property Billable $billable
 */
class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PAID = 'paid';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'freemius_payments';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'integer',
        'discount_total' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
        'refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'paymented_at' => 'datetime',
    ];

    /**
     * Get the billable model related to the customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the Payment is pending.
     */
    public function pending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Filter query by pending.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if the Payment is failed.
     */
    public function failed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Filter query by failed.
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if the Payment is paid.
     */
    public function paid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Filter query by paid.
     */
    public function scopePaid(Builder $query): void
    {
        $query->where('status', self::STATUS_PAID);
    }

    /**
     * Check if the Payment is refunded.
     */
    public function refunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Filter query by refunded.
     */
    public function scopeRefunded(Builder $query): void
    {
        $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Determine if the Payment is for a specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return $this->product_id === $productId;
    }

    /**
     * Determine if the Payment is for a specific plan.
     */
    public function hasVariant(string $planId): bool
    {
        return $this->plan_id === $planId;
    }

    /**
     * Get the Payment's subtotal.
     */
    public function subtotal(): string
    {
        return Freemius::formatAmount($this->subtotal, $this->currency);
    }

    /**
     * Get the Payment's discount total.
     */
    public function discount(): string
    {
        return Freemius::formatAmount($this->discount_total, $this->currency);
    }

    /**
     * Get the Payment's tax.
     */
    public function tax(): string
    {
        return Freemius::formatAmount($this->tax, $this->currency);
    }

    /**
     * Get the Payment's total.
     */
    public function total(): string
    {
        return Freemius::formatAmount($this->total, $this->currency);
    }

    /**
     * Sync the Payment with the given attributes.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'customer_id' => $attributes['customer_id'],
            'product_id' => (string) $attributes['first_Payment_item']['product_id'],
            'plan_id' => (string) $attributes['first_Payment_item']['plan_id'],
            'identifier' => $attributes['identifier'],
            'Payment_number' => $attributes['Payment_number'],
            'currency' => $attributes['currency'],
            'subtotal' => $attributes['subtotal'],
            'discount_total' => $attributes['discount_total'],
            'tax' => $attributes['tax'],
            'total' => $attributes['total'],
            'tax_name' => $attributes['tax_name'],
            'status' => $attributes['status'],
            'receipt_url' => $attributes['urls']['receipt'] ?? null,
            'refunded' => $attributes['refunded'],
            'refunded_at' => isset($attributes['refunded_at']) ? Carbon::make($attributes['refunded_at']) : null,
            'Paymented_at' => isset($attributes['created_at']) ? Carbon::make($attributes['created_at']) : null,
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }
}