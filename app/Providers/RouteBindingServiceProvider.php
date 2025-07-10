<?php

namespace App\Providers;

use App\Models\Wrestler;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteBindingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::bind('wrestler', function ($value) {
            return Wrestler::where('slug', $value)
                ->orWhere('id', $value)
                ->firstOrFail();
        });

    }
}
