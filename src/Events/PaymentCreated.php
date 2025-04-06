<?php

namespace Freemius\Laravel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Freemius\Laravel\Payment;

class PaymentCreated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * The billable entity.
     */
    public Model $billable;

    /**
     * The Payment entity.
     *
     * @todo v2: Remove the nullable type hint.
     */
    public ?Payment $Payment;

    /**
     * The payload array.
     */
    public array $payload;

    public function __construct(Model $billable, ?Payment $Payment, array $payload)
    {
        $this->billable = $billable;
        $this->Payment = $Payment;
        $this->payload = $payload;
    }
}
