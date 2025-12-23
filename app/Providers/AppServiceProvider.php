<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Task::observe(TaskObserver::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
            ];
        });

        // Reutiliza o mesmo limiter para registro (mitigação de bruteforce / abuse)
        RateLimiter::for('register', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
            ];
        });

        RateLimiter::for('auth', function (Request $request) {
            return [
                Limit::perMinute(60)->by(optional($request->user())->id.'|'.$request->ip()),
            ];
        });

        // Definir URL do reset para ambientes API (evita depender de rota web password.reset)
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $baseUrl = config('app.frontend_url') ?: config('app.url');

            return rtrim((string) $baseUrl, '/').'/reset-password?token='.$token.'&email='.urlencode($notifiable->email);
        });
    }
}
