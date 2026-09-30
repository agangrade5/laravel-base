<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Notifications\CustomResetPasswordNotification;

#[Fillable(['name', 'email', 'country_iso', 'country_code', 'phone_number', 'timezone', 'password', 'image', 'is_active'])]
#[Hidden(['password', 'google2fa_secret', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
            'is_active' => 'boolean',
            'google2fa_secret'  => 'encrypted', // stored encrypted at rest
            'google2fa_enabled' => 'boolean',
            'google2fa_enabled_at' => 'datetime',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(
            new CustomResetPasswordNotification($token)
        );
    }

    /**
     * Get user-specific settings.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}

