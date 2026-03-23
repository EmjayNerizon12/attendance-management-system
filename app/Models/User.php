<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->getAttribute('is_super_admin') ?? false)
            || $this->permissionRole() === 'super-admin';
    }

    public function permissionRole(): ?string
    {
        $employeeRole = $this->employee?->role;

        if ($employeeRole instanceof \BackedEnum) {
            return $employeeRole->value;
        }

        if (is_string($employeeRole) && $employeeRole !== '') {
            return $employeeRole;
        }

        $userRole = $this->getAttribute('role');

        return is_string($userRole) && $userRole !== '' ? $userRole : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
