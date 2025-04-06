<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Freemius API Token
    |--------------------------------------------------------------------------
    |
    | The Freemius API Token is used to authenticate with the Freemius
    | API. You can find your API Token in the Freemius dashboard. You can
    | find your API Token in the Freemius dashboard Store > Product > Settings > Api Token.
    |
    */

    'api_key' => env('FREEMIUS_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Freemius Product Secret Key
    |--------------------------------------------------------------------------
    |
    | The Freemius Product Secret Key is used to verify that the webhook
    | requests are coming from Freemius.
    | You can find your Product Secret Key in the Freemius dashboard Store > Product > Settings > Information >Keys.
    |
    */

    'secret_key' => env('FREEMIUS_PRODUCT_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Freemius Url Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI where routes from Freemius will be served
    | from. The URL built into Freemius is used by default; however,
    | you can modify this path as you see fit for your application.
    |
    */

    'path' => env('FREEMIUS_PATH', 'freemius'),

    /*
    |--------------------------------------------------------------------------
    | Freemius Store ID
    |--------------------------------------------------------------------------
    |
    | This is the ID of your Freemius store. You can find your store
    | ID in the Freemius dashboard Store > Product > Settings > Keys.
    |
    */

    'store' => env('FREEMIUS_STORE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Freemius Product ID
    |--------------------------------------------------------------------------
    |
    | This is the ID of your Freemius product. You can find your product
    | ID in the Freemius dashboard Store > Product > Settings > Keys.
    |
    */

    'product_id' => env('FREEMIUS_PRODUCT_ID'),
    /*
    |--------------------------------------------------------------------------
    | Default Redirect URL
    |--------------------------------------------------------------------------
    |
    | This is the default redirect URL that will be used when a customer
    | is redirected back to your application after completing a purchase
    | from a checkout session in your Freemius store.
    |
    */

    'redirect_url' => null,

    'currency_locale' => env('FREEMIUS_CURRENCY_LOCALE', 'en'),
    
    'sandbox' => env('FREEMIUS_SANDBOX', false),
];
