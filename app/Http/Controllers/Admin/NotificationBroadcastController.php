<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Services\PushNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationBroadcastController extends Controller
{
    public function create(): View
    {
        return view('admin.notifications.send', [
            'title' => 'Send Push Notification', 'active' => 'notifications',
            'customers' => Customer::with('user')->get(),
            'logs' => NotificationLog::with('customer.user')->latest()->take(50)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'in:all,customer'],
            'customer_id' => ['required_if:target,customer', 'nullable', 'exists:customers,id'],
            'push_title' => ['required', 'string', 'max:255'],
            'push_body' => ['required', 'string', 'max:255'],
        ]);

        if ($data['target'] === 'all') {
            $result = PushNotificationService::sendToAll($data['push_title'], $data['push_body']);
            NotificationLog::create([
                'template_key' => 'manual',
                'customer_id' => null,
                'title' => $data['push_title'],
                'channel' => 'push',
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'],
            ]);
        } else {
            $customer = Customer::with('user')->findOrFail($data['customer_id']);
            $result = PushNotificationService::sendToUser($customer->user_id, $data['push_title'], $data['push_body']);
            NotificationLog::create([
                'template_key' => 'manual',
                'customer_id' => $customer->id,
                'title' => $data['push_title'],
                'channel' => 'push',
                'status' => $result['success'] ? 'sent' : 'failed',
                'error' => $result['error'],
            ]);
        }

        if (! $result['success']) {
            return back()->with('error', 'Push failed to send: '.$result['error']);
        }

        return back()->with('success', 'Push notification sent.');
    }
}
