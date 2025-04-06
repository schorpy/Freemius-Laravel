<?php

namespace Freemius\Laravel\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Freemius\Laravel\Customer;
use Freemius\Laravel\Exceptions\InvalidCustomer;
use Freemius\Laravel\Freemius;

trait ManagesCustomer
{
    /**
     * Create a customer record for the billable model.
     */
    public function createAsCustomer(array $attributes = []): Customer
    {
        if ($customer = $this->customer) {
            return $customer;
        }
        // Create a new customer record in the local database
        $customer = $this->customer()->create([
            'plan_id' => $attributes['plan_id'] ?? $this->plan_id,
            'email' => $attributes['email'] ?? $this->email,
            'trial_ends_at' => $attributes['trial_ends_at'] ?? null,
        ]);

        return $customer;
    }

    /**
     * Get the customer related to the billable model.
     */
    public function customer(): MorphOne
    {
        return $this->morphOne(Customer::class, 'billable');
    }

    /**
     * Get the billable's name to associate with.
     */
    public function FreemiusName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the billable's email address to associate with.
     */
    public function FreemiusEmail(): ?string
    {
        return $this->email ?? null;
    }

    /**
     * Get the billable's country to associate with.
     *
     * This needs to be a 2 letter code.
     */
    public function FreemiusCountry(): ?string
    {
        return $this->country ?? null; // 'US'
    }

    /**
     * Get the billable's zip code to associate with .
     */
    // public function FreemiusZip(): ?string
    // {
    //     return $this->zip ?? null; // '10038'
    // }

    /**
     * Get the billable's tax number to associate with .
     */
    // public function FreemiusTaxNumber(): ?string
    // {
    //     return $this->tax_number ?? null; // 'GB123456789'
    // }

    /**
     * Get the customer portal url for this billable.
     */
    public function customerPortalUrl(): string
    {
        $this->assertCustomerExists();

        $storeId = config('freemius.store');
        $response = "https://users.freemius.com/store/{$storeId}";

        return $response;
    }

    /**
     * Generate a redirect response to the billable's customer portal.
     */
    public function redirectToCustomerPortal(): RedirectResponse
    {
        return new RedirectResponse($this->customerPortalUrl());
    }

    /**
     * Determine if the billable is already a Freemius customer and throw an exception if not.
     *
     * @throws InvalidCustomer
     */
    protected function assertCustomerExists(): void
    {
        if (is_null($this->customer) || is_null($this->customer->freemius_user_id)) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }
}