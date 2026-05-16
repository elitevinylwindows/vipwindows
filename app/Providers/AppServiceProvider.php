<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helper/helpers.php');
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
