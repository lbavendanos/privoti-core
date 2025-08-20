<?php

declare(strict_types=1);

namespace App\Domains\Store\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

final class VerifyNewEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public User $user) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify new email notification mail message for the given URL.
     *
     * @return MailMessage
     */
    private function buildMailMessage(string $url)
    {
        return (new MailMessage)
            ->subject(Lang::get('Verify New Email Address'))
            ->line(Lang::get('Please click the button below to verify your new email address.'))
            ->action(Lang::get('Verify New Email Address'), $url)
            ->line(Lang::get('If you did not request an change email, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     */
    private function verificationUrl(object $notifiable): string
    {
        $type = 'verify-new-email';
        $id = $this->user->getKey();
        $email = $notifiable->routes['mail'];
        $hash = sha1((string) $notifiable->routes['mail']);

        $temporarySignedRoute = URL::temporarySignedRoute(
            'auth.customer.email.new.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $id, 'email' => $email, 'hash' => $hash]
        );

        $query = parse_url($temporarySignedRoute, PHP_URL_QUERY);

        return config('core.store_url').sprintf('/auth/confirm?type=%s&id=%s&email=%s&token=%s&%s', $type, $id, $email, $hash, $query);
    }
}
