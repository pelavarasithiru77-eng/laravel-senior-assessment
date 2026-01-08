<?php

namespace Vendor\UserDiscounts;

use Illuminate\Support\ServiceProvider;

class UserDiscountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
       
    }

    public function boot(): void
    {
       
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
