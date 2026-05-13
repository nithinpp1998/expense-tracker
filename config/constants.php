<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Constants
|--------------------------------------------------------------------------
|
| Central source of truth for every static value used across the project.
| Tune per-environment values via the corresponding .env keys; everything
| else is a hard domain constraint that should not change at runtime.
|
| Usage:  config('constants.pagination.default_per_page')
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Pagination
    |----------------------------------------------------------------------
    | Default and maximum per-page sizes enforced in every listing endpoint.
    | Controllers always read these values — never hardcode them inline.
    |
    | ENV:  PAGINATION_DEFAULT_PER_PAGE, PAGINATION_MAX_PER_PAGE
    */
    'pagination' => [
        'default_per_page' => (int) env('PAGINATION_DEFAULT_PER_PAGE', 15),
        'max_per_page'     => (int) env('PAGINATION_MAX_PER_PAGE', 100),
    ],

    /*
    |----------------------------------------------------------------------
    | Expense Domain
    |----------------------------------------------------------------------
    | Validation boundaries for expense fields.  amount_min / amount_max
    | mirror the decimal(12,2) column; description_max_length mirrors the
    | VARCHAR(500) column.  These must stay in sync with the schema.
    */
    'expense' => [
        'amount_min'             => 0.01,
        'amount_max'             => 99999999.99,
        'description_max_length' => 500,
        'search_max_length'      => 200,
        'decimal_places'         => 2,
    ],

    /*
    |----------------------------------------------------------------------
    | Category Domain
    |----------------------------------------------------------------------
    | name_max_length mirrors the VARCHAR(100) column.
    | default_color is the fallback hex used when the user omits a color.
    |
    | ENV:  CATEGORY_DEFAULT_COLOR
    */
    'category' => [
        'name_max_length' => 100,
        'default_color'   => env('CATEGORY_DEFAULT_COLOR', '#6b7280'),
    ],

    /*
    |----------------------------------------------------------------------
    | Reports & Caching
    |----------------------------------------------------------------------
    | cache_ttl_seconds — how long aggregated report results are cached.
    | cache_bust_months — how many past months to invalidate when an
    |                     expense is created, updated, or deleted.
    |
    | ENV:  REPORT_CACHE_TTL
    */
    'reports' => [
        'cache_ttl_seconds' => (int) env('REPORT_CACHE_TTL', 3600),
        'cache_bust_months' => 3,
    ],

    /*
    |----------------------------------------------------------------------
    | Rate Limiting
    |----------------------------------------------------------------------
    | All named rate limiters registered in AppServiceProvider read from
    | here so a single .env change adjusts every guard simultaneously.
    | The login limit must match the tooManyAttempts() check in LoginRequest.
    |
    | ENV:  RATE_LIMIT_API, RATE_LIMIT_LOGIN,
    |       RATE_LIMIT_REPORTS, RATE_LIMIT_EMAIL_VERIFY
    */
    'rate_limits' => [
        'api_per_minute'          => (int) env('RATE_LIMIT_API', 60),
        'login_per_minute'        => (int) env('RATE_LIMIT_LOGIN', 5),
        'reports_per_minute'      => (int) env('RATE_LIMIT_REPORTS', 20),
        'email_verify_per_minute' => (int) env('RATE_LIMIT_EMAIL_VERIFY', 6),
    ],

];
