<?php

namespace Freemius\Laravel;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Freemius\Laravel\Exceptions\FreemiusApiError;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;

class Freemius
{
    public const VERSION = '1.0.0';

    public const API = 'https://api.freemius.com/v1';

    /**
     * Indicates if migrations will be run.
     */
    public static bool $runsMigrations = true;

    /**
     * Indicates if routes will be registered.
     */
    public static bool $registersRoutes = true;

    /**
     * The customer model class name.
     */
    public static string $customerModel = Customer::class;

    /**
     * The subscription model class name.
     */
    public static string $subscriptionModel = Subscription::class;

    /**
     * The payment model class name.
     */
    public static string $paymentModel = Payment::class;

    /**
     * Perform a Freemius API call.
     *
     * @throws Exception
     * @throws FreemiusApiError
     */
    public static function api(string $method, string $uri, array $payload = []): Response
    {
        if (empty($apiKey = config('freemius.api_key'))) {
            throw new Exception('Freemius API key not set.');
        }
        if (empty($productId = config('freemius.product_id'))) {
            throw new Exception('Freemius Product ID not set.');
        }
       
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($apiKey)
            ->withUserAgent('Freemius\Laravel/' . static::VERSION)
            ->accept('application/vnd.api+json')
            ->contentType('application/vnd.api+json')
            ->$method(static::API . "/products/{$productId}/{$uri}", $payload);

        if ($response->failed()) {
            throw new FreemiusApiError($response['errors'][0]['detail'], (int) $response['errors'][0]['status']);
        }

        return $response;
    }
}