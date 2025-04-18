<?php

namespace Freemius\Laravel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Freemius\Laravel\Subscription;

class SubscriptionCancelled
{
    use Dispatchable;
    use SerializesModels;

    /**
     * The billable entity.
     */
    public Model $billable;

    /**
     * The subscription instance.
     */
    public Subscription $subscription;

    /**
     * The payload array.
     */
    public array $payload;

    public function __construct(Model $billable, Subscription $subscription, array $payload)
    {
        $this->billable = $billable;
        $this->subscription = $subscription;
        $this->payload = $payload;
    }
}
