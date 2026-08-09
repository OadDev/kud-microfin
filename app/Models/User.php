<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'role',
        'status',
        'pin_hash',
        'pin_enabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_hash',
        'biometric_token_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin_enabled_at' => 'datetime',
        ];
    }

    public function shopOwner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ShopOwner::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Shown as the account subtitle in biometric/passkey prompts. Prefer
     * mobile over email since it's the identifier customers actually log
     * in with (email is optional for a customer).
     */
    public function getPasskeyUsername(): string
    {
        return $this->mobile ?? $this->email ?? (string) $this->getAuthIdentifier();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isShopOwner(): bool
    {
        return $this->role === 'shop_owner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function hasPinEnabled(): bool
    {
        return filled($this->pin_hash) && filled($this->pin_enabled_at);
    }

    public function setPin(string $pin): void
    {
        $this->forceFill([
            'pin_hash' => Hash::make($pin),
            'pin_enabled_at' => now(),
        ])->save();
    }

    public function verifyPin(string $pin): bool
    {
        return $this->hasPinEnabled() && Hash::check($pin, $this->pin_hash);
    }

    /**
     * Issues a new quick-login device token (plaintext returned once, only
     * the hash is persisted) and invalidates any previous device's token.
     */
    public function issueQuickLoginToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->forceFill(['quick_login_token_hash' => hash('sha256', $token)])->save();

        return $token;
    }

    public function revokeQuickLogin(): void
    {
        $this->forceFill([
            'quick_login_token_hash' => null,
            'pin_hash' => null,
            'pin_enabled_at' => null,
        ])->save();
        $this->passkeys()->delete();
    }

    /**
     * Native-biometric app login (Android BiometricPrompt / iOS
     * LocalAuthentication via a Capacitor plugin) -- separate from the
     * browser-based WebAuthn passkeys above, which don't work reliably
     * inside the app's embedded WebView. The token here is stored by the
     * native plugin in OS-level secure storage (Android Keystore / iOS
     * Keychain) gated behind a real biometric prompt, so retrieving it at
     * all already proves the device owner just authenticated -- this
     * server-side check only needs to confirm it's the right token.
     */
    public function hasBiometricEnabled(): bool
    {
        return filled($this->biometric_token_hash);
    }

    public function issueBiometricToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->forceFill(['biometric_token_hash' => hash('sha256', $token)])->save();

        return $token;
    }

    public function verifyBiometricToken(string $token): bool
    {
        return $this->hasBiometricEnabled() && hash_equals($this->biometric_token_hash, hash('sha256', $token));
    }

    public function revokeBiometric(): void
    {
        $this->forceFill(['biometric_token_hash' => null])->save();
    }
}
