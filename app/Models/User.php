<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

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
            'password'          => 'hashed',
        ];
    }

    // === HELPERS ===

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeknisi(): bool
    {
        return $this->role === 'teknisi';
    }

    /**
     * Format nama lengkap dengan role untuk description activity_log.
     * Contoh: "Admin Budi" atau "Teknisi Sari"
     */
    public function getRoleDisplayName(): string
    {
        return ucfirst($this->role) . ' ' . $this->name;
    }

    // === RELASI ===

    public function tiangDibuat(): HasMany
    {
        return $this->hasMany(TiangTelekomunikasi::class, 'created_by');
    }

    public function tiangDiupdate(): HasMany
    {
        return $this->hasMany(TiangTelekomunikasi::class, 'updated_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'inspected_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function importHistories(): HasMany
    {
        return $this->hasMany(ImportHistory::class, 'uploaded_by');
    }
}
