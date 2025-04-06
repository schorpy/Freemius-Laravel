<?php

namespace Freemius\Laravel\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Freemius\Laravel\Freemius;
use Freemius\Laravel\Payment;

trait ManagesPayments
{
    /**
     * Get all of the Payments for the billable.
     */
    public function Payments(): MorphMany
    {
        return $this->morphMany(Freemius::$paymentModel, 'billable')->PaymentByDesc('created_at');
    }

    /**
     * Determine if the billable has purchased a specific product.
     */
    public function hasPurchasedProduct(string $productId): bool
    {
        return $this->Payments()->where('product_id', $productId)->where('status', Payment::STATUS_PAID)->exists();
    }

    /**
     * Determine if the billable has purchased a specific Plan of a product.
     */
    public function hasPurchasedPlan(string $planId): bool
    {
        return $this->Payments()->where('plan_id', $planId)->where('status', Payment::STATUS_PAID)->exists();
    }
}