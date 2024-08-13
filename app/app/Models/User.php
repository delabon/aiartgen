<?php

declare(strict_types=1);

namespace App\Models;

use App\Mail\PasswordResetMail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
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
        ];
    }

    /**
     * @return HasMany<Art>
     */
    public function arts(): HasMany
    {
        return $this->hasMany(Art::class);
    }

    public static function findByEmail(string $email): ?self
    {
        return self::where('email', $email)->first();
    }

    public function getEmailForPasswordReset(): ?string
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token): void
    {
        Mail::to($this->email)
            ->queue(new PasswordResetMail($this, $token));
    }

    public function hasVerifiedEmail(): bool
    {
        return (bool)$this->email_verified_at;
    }

    public function markEmailAsVerified(): void
    {
        $this->email_verified_at = now();
    }

    public function getEmailForVerification(): ?string
    {
        return $this->email;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }
}
