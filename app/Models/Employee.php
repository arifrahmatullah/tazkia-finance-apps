<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'user_id', 'organization_id', 'nik', 'name', 'title',
        'gender', 'birth_date', 'nidn', 'email', 'phone', 'rfid', 'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function positions()
    {
        return $this->hasMany(EmployeePosition::class);
    }

    // Karyawan bisa punya lebih dari satu jabatan aktif sekaligus — dipakai untuk konteks tunggal
    // (mis. pengajuan dana), ambil yang paling baru mulai berlakunya.
    public function activePosition()
    {
        return $this->hasOne(EmployeePosition::class)->where('is_active', true)->latest('start_date');
    }

    public function activePositions()
    {
        return $this->hasMany(EmployeePosition::class)->where('is_active', true)->orderBy('start_date');
    }

    // Kumpulan department_id dari semua jabatan aktif — dipakai untuk scoping akses
    // staf yang memegang lebih dari satu jabatan sekaligus (mis. di dept berbeda) --
    // ditambah departemen lain yang sengaja dibuka untuk jabatan itu lewat
    // PositionDepartmentView (mis. Wakil Rektor melihat departemen di bawah bidangnya).
    public function activeDepartmentIds(): array
    {
        $positions = $this->activePositions()->with('position.viewableDepartments')->get();

        $ownDeptIds   = $positions->pluck('position.department_id');
        $extraDeptIds = $positions->flatMap(fn($ep) => $ep->position?->viewableDepartments->pluck('id') ?? collect());

        return $ownDeptIds->merge($extraDeptIds)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    // Kumpulan position_id dari SEMUA jabatan aktif -- dipakai untuk approval, karena staf yang
    // memegang lebih dari satu jabatan sekaligus bisa jadi approver untuk pengajuan manapun yang
    // menunjuk salah satu jabatannya, bukan cuma satu jabatan "utama" (activePosition() tunggal).
    public function activePositionIds(): array
    {
        return $this->activePositions()
            ->pluck('position_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    // Data lama punya ID integer, bukan UUID — skip validasi format UUID
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    // Route yang pakai withTrashed() (contoh: restore karyawan) lewat resolveSoftDeletableRouteBinding(),
    // yang panggil resolveRouteBindingQuery() langsung -- bukan resolveRouteBinding() di atas. Tanpa override
    // ini, validasi format UUID dari trait HasUuids tetap jalan dan bikin ID lama (integer) selalu 404.
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return $query->where($field ?? $this->getRouteKeyName(), $value);
    }
}
