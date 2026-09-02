<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes, Auditable;

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

    private bool $_activeRoleResolved = false;
    private ?Role $_activeRole        = null;

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
        return $this->activeRole()?->slug === 'superadmin';
    }

    public function hasRole(string $slug): bool
    {
        return $this->activeRole()?->slug === $slug;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $role = $this->activeRole();
        if (!$role) {
            return false;
        }

        $role->loadMissing('permissions');

        return $role->permissions->contains('slug', $slug);
    }

    public function hasRoleInOrganization(string $slug, string $organizationId): bool
    {
        return $this->organizationRoles()
            ->whereHas('role', fn($q) => $q->where('slug', $slug))
            ->where(fn($q) => $q->where('organization_id', $organizationId)->orWhereNull('organization_id'))
            ->exists();
    }

    /**
     * Semua role unik milik user: role utama (role_id) + semua role dari
     * penugasan per-organisasi (user_organization_roles).
     */
    public function availableRoles(): \Illuminate\Support\Collection
    {
        $roleIds = $this->organizationRoles()->pluck('role_id');

        if ($this->role_id) {
            $roleIds->push($this->role_id);
        }

        $roleIds = $roleIds->unique()->filter()->values();

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return Role::whereIn('id', $roleIds)->orderBy('name')->get();
    }

    public function hasMultipleRoles(): bool
    {
        return $this->availableRoles()->count() > 1;
    }

    /**
     * Role yang sedang aktif untuk sesi saat ini. Kalau user cuma punya
     * satu role dipakai otomatis; kalau lebih dari satu diambil dari
     * pilihan role di sesi (login / ganti role).
     */
    public function activeRole(): ?Role
    {
        if ($this->_activeRoleResolved) {
            return $this->_activeRole;
        }

        $roles = $this->availableRoles();

        if ($roles->count() <= 1) {
            $active = $roles->first() ?? $this->role;
        } else {
            $sessionRoleId = session('active_role_id');
            $active = $sessionRoleId ? $roles->firstWhere('id', $sessionRoleId) : null;
            $active = $active ?? $this->role ?? $roles->first();
        }

        $this->_activeRoleResolved = true;
        $this->_activeRole         = $active;

        return $active;
    }

    /**
     * Kembalikan array organization_id milik user untuk role yang sedang
     * aktif. Superadmin mengembalikan null (berarti tidak ada filter / lihat semua).
     */
    public function organizationIds(): ?array
    {
        $active = $this->activeRole();
        if (!$active) {
            return null;
        }

        $orgIds = $this->organizationRoles()
            ->where('role_id', $active->id)
            ->whereNotNull('organization_id')
            ->pluck('organization_id')->unique()->values()->toArray();

        if (!empty($orgIds)) {
            return $orgIds;
        }

        // Superadmin tanpa organisasi spesifik = akses semua organisasi.
        // Kalau superadmin di-scope ke satu/lebih organisasi (lewat baris di atas), dia dibatasi ke situ saja.
        if ($active->slug === 'superadmin') {
            return null;
        }

        // Fallback: gunakan organisasi dari data karyawan yang terhubung
        $this->loadMissing('employee');
        $empOrgId = $this->employee?->organization_id;

        return $empOrgId ? [$empOrgId] : null;
    }

    /**
     * Cek apakah user boleh mengakses organisasi tertentu.
     * Superadmin selalu boleh.
     */
    public function canAccessOrganization(string $orgId): bool
    {
        $ids = $this->organizationIds();
        return $ids === null || in_array($orgId, $ids);
    }
}
