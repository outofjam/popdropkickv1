<?php

namespace App\Providers;

use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
            // Trust DigitalOcean App Platform proxies
            $this->app['request']->server->set('HTTPS', 'on');
            $this->app['request']->server->set('SERVER_PORT', 443);

            URL::forceScheme('https');
            
        }
        Gate::define('viewApiDocs', static function ($user = null) {
            return true;
        });

        EditAction::configureUsing(function (EditAction $action): void {
            $action->slideOver();
        });


        // 👇 log every attempt
        Auth::attempting(function ($event) {
            Log::info('Auth attempting', [
                'credentials' => $event->credentials,
                'remember' => $event->remember,
            ]);
        });
    }
}
