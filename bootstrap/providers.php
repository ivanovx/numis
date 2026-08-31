<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Nuwave\Lighthouse\LighthouseServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    LighthouseServiceProvider::class,
];
