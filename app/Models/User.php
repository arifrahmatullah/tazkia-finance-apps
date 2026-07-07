<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function organizationRoles()
    {
        return $this->hasMany(UserOrganizationRole::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'superadmin';
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $this->loadMissing('role.permissions');

        return $this->role?->permissions->contains('slug', $slug) ?? false;
    }

    public function hasRoleInOrganization(string $slug, string $organizationId): bool
    {
        return $this->organizationRoles()
            ->whereHas('role', fn($q) => $q->where('slug', $slug))
            ->where(fn($q) => $q->where('organization_id', $organizationId)->orWhereNull('organization_id'))
            ->exists();
    }

    /**
     * Kembalikan array organization_id milik user.
     * Superadmin mengembalikan null (berarti tidak ada filter / lihat semua).
     */
    public function organizationIds(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        return $this->organizationRoles()
            ->whereNotNull('organization_id')
            ->pluck('organization_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Cek apakah user boleh mengakses organisasi tertentu.
     * Superadmin selalu boleh.
     */
    public function canAccessOrganization(string $orgId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $ids = $this->organizationIds();
        return $ids !== null && in_array($orgId, $ids);
    }
}
