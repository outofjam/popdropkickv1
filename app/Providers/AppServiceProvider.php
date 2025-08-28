<?php

namespace App\Providers;

use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url')); 
        }
        Gate::define('viewApiDocs', static function ($user = null) {
            return true;
        });

        EditAction::configureUsing(function (EditAction $action): void {
            $action->slideOver();
        });
    }
}
