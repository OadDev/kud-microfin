<?php

namespace App\Services;

use App\Mail\CustomerNotificationMail;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;

/**
 * Single entry point for every customer-facing email/push notification.
 * Callers just say what happened: CustomerNotifier::send('order_placed',
 * $customer, ['order_no' => ..., 'amount' => ...]). This resolves the
 * Admin-edited template, renders it, sends through whichever channels are
 * configured, and logs the outcome -- failures never bubble up, since a
 * broken mail/push config must not turn a real action (order placed, loan
 * approved, ...) into a 500 for the customer or admin performing it.
 */
class CustomerNotifier
{
    public static function send(string $templateKey, Customer $customer, array $placeholders = []): void
    {
        try {
            $template = NotificationTemplate::where('key', $templateKey)->where('is_active', true)->first();
            if (! $template) {
                return;
            }

            $customer->loadMissing('user');
            $user = $customer->user;
            if (! $user) {
                return;
            }

            $data = array_merge(['customer_name' => $user->name], $placeholders);

            if ($template->email_enabled && filled($user->email)) {
                static::sendMail($template, $data, $user->email, $customer->id);
            }

            if ($template->push_enabled) {
                static::sendPush($template, $data, $user->id, $customer->id);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected static function sendMail(NotificationTemplate $template, array $data, string $toEmail, int $customerId): void
    {
        $subject = NotificationTemplate::render($template->email_subject, $data);
        $settings = NotificationSetting::current();

        if (! $settings->mailReady()) {
            static::log($template->key, $customerId, $subject, 'mail', 'skipped', 'SMTP is not configured or enabled.');

            return;
        }

        static::applySmtpConfig($settings);

        try {
            $body = NotificationTemplate::render($template->email_body, $data);
            Mail::mailer('smtp')->to($toEmail)->send(new CustomerNotificationMail($subject, $body));
            static::log($template->key, $customerId, $subject, 'mail', 'sent');
        } catch (\Throwable $e) {
            report($e);
            static::log($template->key, $customerId, $subject, 'mail', 'failed', $e->getMessage());
        }
    }

    protected static function sendPush(NotificationTemplate $template, array $data, int $userId, int $customerId): void
    {
        $title = NotificationTemplate::render($template->push_title, $data);
        $settings = NotificationSetting::current();

        if (! $settings->pushReady()) {
            static::log($template->key, $customerId, $title, 'push', 'skipped', 'OneSignal is not configured or enabled.');

            return;
        }

        $body = NotificationTemplate::render($template->push_body, $data);
        $result = PushNotificationService::sendToUser($userId, $title, $body);

        static::log($template->key, $customerId, $title, 'push', $result['success'] ? 'sent' : 'failed', $result['error']);
    }

    /**
     * Points the 'smtp' mailer at the Admin-configured, DB-stored SMTP
     * account for this send. Laravel's SMTP transport is built lazily from
     * config, so overriding it right before Mail::mailer('smtp')->send()
     * works without touching .env.
     */
    public static function applySmtpConfig(NotificationSetting $settings): void
    {
        $scheme = match ($settings->smtp_encryption) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        config([
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => $settings->smtp_host,
            'mail.mailers.smtp.port' => $settings->smtp_port,
            'mail.mailers.smtp.username' => $settings->smtp_username,
            'mail.mailers.smtp.password' => $settings->smtp_password,
            'mail.mailers.smtp.encryption' => $settings->smtp_encryption,
            'mail.from.address' => $settings->smtp_from_address,
            'mail.from.name' => $settings->smtp_from_name ?: config('app.name'),
        ]);
    }

    protected static function log(string $templateKey, int $customerId, string $title, string $channel, string $status, ?string $error = null): void
    {
        NotificationLog::create([
            'template_key' => $templateKey,
            'customer_id' => $customerId,
            'title' => $title,
            'channel' => $channel,
            'status' => $status,
            'error' => $error,
        ]);
    }
}
