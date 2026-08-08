<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
        'email_enabled',
        'email_subject',
        'email_body',
        'push_enabled',
        'push_title',
        'push_body',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    /**
     * Replaces {{placeholder}} tokens in a template string with values from
     * $data. Unknown placeholders are left as-is rather than silently
     * blanked, so a typo'd token is obvious in the rendered output.
     */
    public static function render(?string $text, array $data): string
    {
        if (blank($text)) {
            return '';
        }

        $replacements = [];
        foreach ($data as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($text, $replacements);
    }
}
