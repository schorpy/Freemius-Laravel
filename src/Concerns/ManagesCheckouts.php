<?php

namespace Freemius\Laravel\Concerns;

use Freemius\Laravel\Checkout;
use Freemius\Laravel\Exceptions\MissingStore;
use Freemius\Laravel\Subscription;

trait ManagesCheckouts
{
    /**
     * Create a new checkout instance to sell a product.
     */
    public function checkout(string $plan_id, array $options = [], array $custom = []): Checkout
    {
        $customer = $this->createAsCustomer([
            'plan_id' => $plan_id,
        ]);
        
        $custom = array_merge($custom, [
            'billable_id' => (string) $this->getKey(),
            'billable_type' => $this->getMorphClass(),
        ]);

        return Checkout::make($this->FreemiusStore(), $plan_id)
            ->withName($options['name'] ?? (string) $this->FreemiusName())
            ->withEmail($options['email'] ?? (string) $this->FreemiusEmail())
            ->withBillingAddress(
                $options['country'] ?? (string) $this->FreemiusCountry(),
                $options['zip'] ?? (string) $this->FreemiusZip(),
            )
            ->withTaxNumber($options['tax_number'] ?? (string) $this->FreemiusTaxNumber())
            ->withDiscountCode($options['discount_code'] ?? '')
            ->withCustomPrice($options['custom_price'] ?? null)
            ->withCustomData($custom);
    }

    /**
     * Create a new checkout instance to sell a product with a custom price.
     */
    // public function charge(int $amount, string $variant, array $options = [], array $custom = [])
    // {
    //     return $this->checkout($variant, array_merge($options, [
    //         'custom_price' => $amount,
    //     ]), $custom);
    // }

    /**
     * Subscribe the customer to a new plan.
     */
    public function subscribe(string $variant, string $type = Subscription::DEFAULT_TYPE, array $options = [], array $custom = []): Checkout
    {
        return $this->checkout($variant, $options, array_merge($custom, [
            'subscription_type' => $type,
        ]));
    }

    /**
     * Get the configured Freemius store ID from the config.
     *
     * @throws MissingStore
     */
    protected function FreemiusStore(): string
    {
        if (! $store = config('freemius.store')) {
            throw MissingStore::notConfigured();
        }

        return $store;
    }
}