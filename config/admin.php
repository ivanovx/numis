<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial administrator
    |--------------------------------------------------------------------------
    |
    | Used by database/seeders/AdminUserSeeder.php. They are read here (and not
    | with env() inside the seeder) so they keep working when the configuration
    | is cached with `php artisan config:cache`.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@example.com'),

    'password' => env('ADMIN_PASSWORD', 'change-me-now'),

];
