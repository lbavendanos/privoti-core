<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $baseUrl = $this->isUser($notifiable) ? config('core.cms_url') : config('core.store_url');

            return $baseUrl.sprintf('/password/reset/%s?email=%s', $token, $notifiable->getEmailForPasswordReset());
        });

        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $baseUrl = $this->isUser($notifiable) ? config('core.cms_url') : config('core.store_url');
            $modelName = $this->isUser($notifiable) ? 'user' : 'customer';

            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            $temporarySignedRoute = URL::temporarySignedRoute(
                sprintf('auth.%s.email.verify', $modelName),
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                ['id' => $id, 'hash' => $hash]
            );

            $query = parse_url($temporarySignedRoute, PHP_URL_QUERY);

            return $baseUrl.sprintf('/confirm-email?id=%s&hash=%s&%s', $id, $hash, $query);
        });
    }

    private function isUser(object $notifiable): bool
    {
        return $notifiable::class === \App\Models\User::class;
    }
}
