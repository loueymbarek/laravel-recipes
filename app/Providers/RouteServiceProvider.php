<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        \Log::info('Configuring rate limiting...');
    
        RateLimiter::for('global', function (Request $request) {
            \Log::info('Applying global rate limit');
            return Limit::perMinute(60);
        });
    
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by(optional($request->user())->id ?: $request->ip())->response(function () {
                return response()->json(['message' => 'Too many login attempts, please try again later.'], 429);
            });
        });
    
        RateLimiter::for('profile-update', function (Request $request) {
            \Log::info('Applying profile-update rate limit');
            return Limit::perMinute(5)->by($request->user()->id);
        });
    }
    
}
