<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Customer;
use App\Models\User;
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
        /** @phpstan-ignore-next-line */
        ResetPassword::createUrlUsing(function (User|Customer $notifiable, string $token): string {
            $baseUrl = $this->isUser($notifiable) ? Config::string('core.cms_url') : Config::string('core.store_url');

            return $baseUrl.sprintf('/password/reset/%s?email=%s', $token, $notifiable->getEmailForPasswordReset());
        });

        VerifyEmail::createUrlUsing(function (User|Customer $notifiable): string {
            $baseUrl = $this->isUser($notifiable) ? Config::string('core.cms_url') : Config::string('core.store_url');
            $modelName = $this->isUser($notifiable) ? 'user' : 'customer';

            /** @var int $id */
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            $temporarySignedRoute = URL::temporarySignedRoute(
                sprintf('auth.%s.email.verify', $modelName),
                Carbon::now()->addMinutes(Config::integer('auth.verification.expire', 60)),
                ['id' => $id, 'hash' => $hash]
            );

            $query = parse_url($temporarySignedRoute, PHP_URL_QUERY);

            return $baseUrl.sprintf('/confirm-email?id=%s&hash=%s&%s', $id, $hash, $query);
        });
    }

    private function isUser(User|Customer $notifiable): bool
    {
        return $notifiable::class === User::class;
    }
}
