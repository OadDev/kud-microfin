<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'onesignal_enabled',
        'onesignal_app_id',
        'onesignal_api_key',
        'smtp_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'emi_reminder_days_before',
    ];

    protected function casts(): array
    {
        return [
            'onesignal_enabled' => 'boolean',
            'onesignal_api_key' => 'encrypted',
            'smtp_enabled' => 'boolean',
            'smtp_port' => 'integer',
            'smtp_password' => 'encrypted',
        ];
    }

    /**
     * There is only ever one settings row (Admin-configured notification
     * channels), mirroring PaymentSetting::current().
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function pushReady(): bool
    {
        return $this->onesignal_enabled && filled($this->onesignal_app_id) && filled($this->onesignal_api_key);
    }

    public function mailReady(): bool
    {
        return $this->smtp_enabled && filled($this->smtp_host) && filled($this->smtp_from_address);
    }
}
