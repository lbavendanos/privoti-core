<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $baseUrl = get_class($notifiable) === \App\Models\User::class ? config('app.store_url') : config('app.dashboard_url');

            return $baseUrl . "/password/reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        VerifyEmail::createUrlUsing(function (object $notifiable) {
            $baseUrl = get_class($notifiable) === \App\Models\User::class ? config('app.store_url') : config('app.dashboard_url');
            $type = 'verify-email';
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            $temporarySignedRoute = URL::temporarySignedRoute(
                'auth.user.email.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                ['id' => $id, 'hash' => $hash]
            );

            $query = parse_url($temporarySignedRoute, PHP_URL_QUERY);

            return $baseUrl . "/auth/confirm?type={$type}&id={$id}&token={$hash}&{$query}";
        });
    }
}
