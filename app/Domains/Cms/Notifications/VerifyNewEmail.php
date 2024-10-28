<?php

namespace App\Domains\Cms\Notifications;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyNewEmail extends Notification
{
    use Queueable;

    public $admin;

    /**
     * Create a new notification instance.
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

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
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
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
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $type = 'verify-new-email';
        $id = $this->admin->getKey();
        $email = $notifiable->routes['mail'];
        $hash = sha1($notifiable->routes['mail']);

        $temporarySignedRoute = URL::temporarySignedRoute(
            'auth.admin.email.new.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $id, 'email' => $email, 'hash' => $hash]
        );

        $query = parse_url($temporarySignedRoute, PHP_URL_QUERY);

        return config('app.cms_url') . "/auth/confirm?type={$type}&id={$id}&email={$email}&token={$hash}&{$query}";
    }
}
