<?php
namespace Freemius\Laravel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Freemius\Laravel\Events\LicenseKeyCreated;
use Freemius\Laravel\Events\LicenseKeyUpdated;
use Freemius\Laravel\Events\OrderCreated;
use Freemius\Laravel\Events\OrderRefunded;
use Freemius\Laravel\Events\SubscriptionCancelled;
use Freemius\Laravel\Events\SubscriptionCreated;
use Freemius\Laravel\Events\SubscriptionExpired;
use Freemius\Laravel\Events\SubscriptionPaused;
use Freemius\Laravel\Events\SubscriptionPaymentFailed;
use Freemius\Laravel\Events\SubscriptionPaymentRecovered;
use Freemius\Laravel\Events\SubscriptionPaymentSuccess;
use Freemius\Laravel\Events\SubscriptionResumed;
use Freemius\Laravel\Events\SubscriptionUnpaused;
use Freemius\Laravel\Events\SubscriptionUpdated;
use Freemius\Laravel\Events\WebhookHandled;
use Freemius\Laravel\Events\WebhookReceived;
use Freemius\Laravel\Exceptions\InvalidCustomPayload;
use Freemius\Laravel\Exceptions\LicenseKeyNotFound;
use Freemius\Laravel\Exceptions\MalformedDataError;
use Freemius\Laravel\Http\Middleware\VerifyWebhookSignature;
use Freemius\Laravel\Http\Throwable\BadRequest;
use Freemius\Laravel\Http\Throwable\NotFound;
use Freemius\Laravel\Freemius;
use Freemius\Laravel\LicenseKey;
use Freemius\LaraveL\Payment;
use Freemius\Laravel\Subscription;
use Symfony\Component\HttpFoundation\Response;


final class WebhookController extends Controller
{
    /**
     * Create a new WebhookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // if (config('freemius.secret_key')) {
        //     $this->middleware(VerifyWebhookSignature::class);
        // }
    }

    /**
     * Handle a Freemius webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->all();

        if (! isset($payload['type'])) {
            return new Response('Webhook received but no event name was found.');
        }

        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            try {
                $this->{$method}($payload);
            } catch (BadRequest $e) {
                return new Response($e->getMessage(), 400);
            } catch (NotFound $e) {
                return new Response($e->getMessage(), 404);
            } catch (\Exception $e) {
                return new Response(sprintf('Internal server error: %s', $e->getMessage()), 500);
            }

            WebhookHandled::dispatch($payload);

            return new Response('Webhook was handled.');
        }

        return new Response('Webhook received but no handler found.');
    }

    /**
     * Handle cart created.
     *
     * @param  array  $payload
     * @return void
     */
    public function handleCartCreated(array $payload): void
    {
        if (!isset($payload['objects']['cart'])) {
            throw new BadRequest('Missing cart data in payload');
        }

        $cart = $payload['objects']['cart'];
        
        // Create or update customer
        Freemius::$customerModel::firstOrCreate([
            'billable_id' => $cart['user_id'] ?? null,
            'billable_type' => config('freemius.model'),
        ], [
            'freemius_id' => $cart['id'] ?? null,
            'status' => $cart['status'] ?? null,
            'plan_id' => $cart['plan_id'] ?? null,
            'pricing_id' => $cart['pricing_id'] ?? null,
            'email' => $cart['email'] ?? null,
            'first_name' => $cart['first'] ?? null,
            'last_name' => $cart['last'] ?? null,
            'country_code' => $cart['country_code'] ?? null,
            'payment_method' => $cart['payment_method'] ?? null,
        ]);
    }

    /**
     * Handle payment created.
     *
     * @param  array  $payload
     * @return void
     */
    public function handlePaymentCreated(array $payload): void
    {
        $payment = $payload['objects']['payment'];
        if (!isset($payment)) {
            throw new BadRequest('Missing Payment data in payload');
        }

        $billable = $this->Freemius($payload);

        
        if (Schema::hasTable((new Freemius::$orderModel())->getTable())) {
           
            $user = $payload['objects']['user'];
            
            $order = $billable->orders()->create([
                'freemius_id' => $user['id'],
                'plan_id' => $payment['plan_id'],
                'pricing_id' => $payment['pricing_id'],
                'total' => $payment['gross'],
                'currency' => $payment['currency'],
                'external_id' => $payment['external_id'],
                'vat' => $payment['vat'],
                'is_renewal' => $payment['is_renewal'],
                'created_at' => $payment['created'],
            ]);
        } else {
            $order = null;
        }

        OrderCreated::dispatch($billable, $order, $payload);
    }

    /**
     * Handle payment refund.
     *
     * @param  array  $payload
     * @return void
     */
    public function handlePaymentRefund(array $payload): void
    {
        $billable = $this->Freemius($payload);

        // Todo v2: Remove this check
        if (Schema::hasTable((new Freemius::$orderModel())->getTable())) {
            if (! $order = $this->findOrder($payload['data']['id'])) {
                return;
            }

            $order = $order->sync($payload['data']['s']);
        } else {
            $order = null;
        }

        OrderRefunded::dispatch($billable, $order, $payload);
    }

    /**
     * Handle subscription created.
     *
     * @param  array  $payload
     * @return void
     */
    public function handleSubscriptionCreated(array $payload): void
    {
        $payloadSubscription = $payload['objects']['subscription'];

        try {

            if (!isset($payloadSubscription)) {
                \Log::error('Missing subscription data in payload');
                throw new BadRequest('Missing subscription data');
            }

            
            $billable = $this->resolveBillable($payload);

            // \Log::info('Creating subscription with attributes:', [
            //     'user_id' => $payload['user_id'] ?? null,
            //     'plan_id' => $payloadSubscription['plan_id'] ?? null,
            //     'pricing_id' => $payloadSubscription['pricing_id'] ?? null,
            //     'license_id' => $payloadSubscription['license_id'] ?? null,
            // ]);

            //Fetch data from freemius api for verification
            $subscription_id = $payload['data']['subscription_id'];
            $responseApi = $this->fetchSubscription($subscription_id);
            // $uri = "subscriptions/{$subscription_id}.json";
            // $response = Freemius::api('GET', $uri, []);
            // if ($response === null) {
            //     \Log::error('Failed to fetch subscription data from Freemius API');
            //     throw new BadRequest('Failed to fetch subscription data');
            // }
            // \Log::info('Subscription data api:', ['response' => $response]);
            
            //retrieve plan
            //https://api.freemius.com/v1/products/{product_id}/plans/{plan_id}.json
            //fill data from response Freemius API
            $planId = $responseApi['plan_id'];


            $subscription = $billable->subscriptions()->create([
                'freemius_user_id' => $payload['user_id'] ?? null,
                'subscription_id' => $payload['data']['subscription_id'],
                'status' => 'active',
                'external_id' => $payloadSubscription['external_id'],
                'plan_id' => (string) ($payloadSubscription['plan_id'] ?? ''),
                'pricing_id' => (string) ($payloadSubscription['pricing_id'] ?? ''),
                'license_id' => (string) ($payloadSubscription['license_id'] ?? ''),
                'trial_ends_at' => isset($payloadSubscription['trial_ends']) ? Carbon::make($payloadSubscription['trial_ends']) : null,
                'next_payment' => isset($payloadSubscription['next_payment']) ? Carbon::make($payloadSubscription['next_payment']) : null,
            ]);

            SubscriptionCreated::dispatch($billable, $subscription, $payload);
        } catch (\Exception $e) {
            \Log::error('Subscription creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

   

    /**
     * Handle subscription cancelled.
     *
     * @param  array  $payload
     * @return void
     */
    private function handleSubscriptionCancelled(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['objects']['subscription']);

        if ($subscription->billable) {
            SubscriptionCancelled::dispatch($subscription->billable, $subscription, $payload);
        }
    }

   
    /**
     * Handle license created.
     *
     * @param  array  $payload
     * @return void
     */
    private function handleLicenseCreated(array $payload): void
    {
        $licenseKey = LicenseKey::fromPayload($payload);

        LicenseKeyCreated::dispatch($licenseKey->billable(), $licenseKey);
    }

    /**
     * Handle license expired.
     *
     * @param  array  $payload
     * @return void
     */
    private function handleLicenseExpired(array $payload): void
    {
        $key = $payload['data']['attributes']['key'] ?? '';
        $licenseKey = LicenseKey::withKey($key)->first();

        if ($licenseKey === null) {
            throw LicenseKeyNotFound::withKey($key);
        }

        $licenseKey = $licenseKey->sync($payload['data']['attributes']);

        LicenseKeyUpdated::dispatch($licenseKey->billable(), $licenseKey);
    }



    private function resolveBillable(array $payload)
    {
        $custom = $payload['objects']['user'] ?? null;

        if (!isset($custom['email'])) {
            throw new InvalidCustomPayload("Missing user email in payload.");
        }

        return $this->findOrCreateCustomer(
            $custom['user_id'] ?? null,  // Use user_id from the payload
            $custom['email']
        );
    }

    /**
     * @return \Freemius\Laravel\Billable
     */
    private function findOrCreateCustomer(?int $freemiusId, string $email)
    {
        return Freemius::$customerModel::firstOrCreate(
            ['email' => $email],  // Look for an existing customer by email
            ['freemius_id' => $freemiusId] // Create a new customer if not found
        )->billable;
    }

    private function findSubscription(string $subscriptionId): ?Subscription
    {
        return Freemius::$subscriptionModel::firstWhere('freemius_id', $subscriptionId);
    }

    private function findPayment(string $paymentId): ?Order
    {
        return Freemius::$paymentModel::firstWhere('freemius_user_id', $paymentId);
    }

    /**
     * @return array|null Response from Api
     */
    private function fetchSubscription(string $subscriptionId): ?array
    {
        $uri = "subscriptions/{$subscriptionId}.json";
        $response = Freemius::api('GET', $uri, []);
        if ($response === null) {
            \Log::error('Failed to fetch subscription data from Freemius API');
            throw new BadRequest('Failed to fetch subscription data');
        }
        \Log::info('Subscription data api:', ['response' => $response]);
        return $response->json();  // Convert Response object to array
    }

    /**
     * @return array|null Response from Api
     */
    private function fetchPlan(string $planId): ?array
    {
        $uri = "plans/{$planId}.json";
        $response = Freemius::api('GET', $uri, []);
        if ($response === null) {
            \Log::error('Failed to fetch payment data from Freemius API');
            throw new BadRequest('Failed to fetch payment data');
        }
        \Log::info('Payment data api:', ['response' => $response]);
        return $response->json();
    }
    /**
     * @return array|null Response from Api
     */
    private function fetchPayment(string $paymentId): ?array
    {
        
        $uri = "payments/{$paymentId}.json";
        $response = Freemius::api('GET', $uri, []);
        if ($response === null) {
            \Log::error('Failed to fetch payment data from Freemius API');
            throw new BadRequest('Failed to fetch payment data');
        }
        \Log::info('Payment data api:', ['response' => $response]);
        return $response->json();
    }
}