<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.notifications.templates.index', [
            'title' => 'Notification Templates', 'active' => 'notifications',
            'templates' => NotificationTemplate::orderBy('name')->get(),
        ]);
    }

    public function edit(NotificationTemplate $notificationTemplate): View
    {
        return view('admin.notifications.templates.edit', [
            'title' => 'Edit Template', 'active' => 'notifications',
            'template' => $notificationTemplate,
        ]);
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'email_subject' => ['nullable', 'string', 'max:255'],
            'email_body' => ['nullable', 'string'],
            'push_enabled' => ['nullable', 'boolean'],
            'push_title' => ['nullable', 'string', 'max:255'],
            'push_body' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['email_enabled'] = $request->boolean('email_enabled');
        $data['push_enabled'] = $request->boolean('push_enabled');

        $notificationTemplate->update($data);

        return redirect()->route('admin.notification-manager.templates')->with('success', "Template \"{$notificationTemplate->name}\" saved.");
    }
}
