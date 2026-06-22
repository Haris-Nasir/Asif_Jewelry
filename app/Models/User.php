<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions' => 'array',
    ];

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->role !== 'worker') {
            return false;
        }

        return in_array($permission, $this->resolvedPermissions(), true);
    }

    public function resolvedPermissions(): array
    {
        if ($this->role === 'admin') {
            return array_keys(config('permissions.labels', []));
        }

        if ($this->role !== 'worker') {
            return [];
        }

        if (is_array($this->permissions) && count($this->permissions) > 0) {
            return $this->permissions;
        }

        return config('permissions.worker_defaults', []);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    public function isInvestor(): bool
    {
        return $this->role === 'investor';
    }
}
