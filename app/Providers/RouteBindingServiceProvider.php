<?php

namespace App\Providers;

use App\Models\Championship;
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

        // Custom binding for championship to accept either UUID or slug
        Route::bind('championship', function ($value) {
            return Championship::where('id', $value)
                ->orWhere('slug', $value)
                ->firstOrFail();
        });

    }
}
