<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Core Front URLs
    |--------------------------------------------------------------------------
    |
    | These values are used to define the base URLs for the CMS and Store
    | applications. They are essential for linking to the CMS and Store
    | from the core application. You can set these in your ".env" file.
    |
    */

    'cms_url' => env('CORE_CMS_URL', 'http://localhost:3000'),
    'store_url' => env('CORE_STORE_URL', 'http://localhost:3001'),

    /*
    |--------------------------------------------------------------------------
    | Core Country Code
    |--------------------------------------------------------------------------
    |
    | This value is used to determine the country code for your application.
    | It can be used for localization, currency formatting, and other
    | region-specific functionalities. Set this in your ".env" file.
    |
    */

    'country_code' => env('CORE_COUNTRY_CODE', 'US'),

    /*
    |--------------------------------------------------------------------------
    | Core Timezone
    |--------------------------------------------------------------------------
    |
    | Here you can define the default timezone for your application.
    | It is important for date and time handling across the application.
    | You can set this in your ".env" file to match your application's needs.
    |
    */

    'timezone' => env('CORE_TIMEZONE', 'UTC'),
];
