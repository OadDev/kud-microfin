<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerNotificationMail;
use App\Models\NotificationSetting;
use App\Services\CustomerNotifier;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NotificationSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.notifications.settings', [
            'title' => 'Notification Settings', 'active' => 'notifications',
            'settings' => NotificationSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'onesignal_enabled' => ['nullable', 'boolean'],
            'onesignal_app_id' => ['nullable', 'string', 'max:255'],
            'onesignal_api_key' => ['nullable', 'string', 'max:1000'],
            'smtp_enabled' => ['nullable', 'boolean'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
            'emi_reminder_days_before' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $data['onesignal_enabled'] = $request->boolean('onesignal_enabled');
        $data['smtp_enabled'] = $request->boolean('smtp_enabled');

        $settings = NotificationSetting::current();

        // Password/API-key fields are left untouched when the admin leaves
        // them blank on a resave (the form never echoes the stored secret
        // back out), rather than blanking real credentials.
        if (blank($data['onesignal_api_key'] ?? null)) {
            unset($data['onesignal_api_key']);
        }
        if (blank($data['smtp_password'] ?? null)) {
            unset($data['smtp_password']);
        }

        $settings->update($data);

        return back()->with('success', 'Notification settings saved.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $data = $request->validate(['test_email' => ['required', 'email']]);

        $settings = NotificationSetting::current();
        if (! $settings->mailReady()) {
            return back()->with('error', 'SMTP is not configured/enabled yet — save your SMTP settings first.');
        }

        CustomerNotifier::applySmtpConfig($settings);

        try {
            Mail::mailer('smtp')->to($data['test_email'])->send(new CustomerNotificationMail(
                'BluePeak Fintech — Test Email',
                "This is a test email from your BluePeak Fintech admin panel.\n\nIf you received this, your SMTP settings are working correctly."
            ));

            return back()->with('success', "Test email sent to {$data['test_email']}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }
    }

    public function testPush(Request $request): RedirectResponse
    {
        $settings = NotificationSetting::current();
        if (! $settings->pushReady()) {
            return back()->with('error', 'OneSignal is not configured/enabled yet — save your OneSignal settings first.');
        }

        $result = PushNotificationService::sendToAll(
            'BluePeak Fintech — Test Push',
            'This is a test push notification from your admin panel.'
        );

        if ($result['success']) {
            return back()->with('success', 'Test push sent to all subscribed app users.');
        }

        return back()->with('error', 'Failed to send test push: '.$result['error']);
    }
}
