<?php

namespace App\Providers;

use App\Models\Dependent;
use App\Observers\DependentObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Dependent::observe(DependentObserver::class);
    }
}