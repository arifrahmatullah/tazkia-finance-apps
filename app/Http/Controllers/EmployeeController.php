<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $orgIds = auth()->user()->organizationIds();

        $employees = Employee::with(['organization', 'activePosition.position.department'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderBy('organization_id')
            ->orderBy('name')
            ->get();

        return view('employees.index', compact('employees'));
    }

    private function allowedOrgs(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        $query  = Organization::where('is_active', true);
        return $orgIds !== null ? $query->whereIn('id', $orgIds) : $query;
    }

    public function create()
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('employees.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'nik'             => 'required|digits_between:1,16|unique:employees,nik',
            'name'            => 'required|string|max:100',
            'title'           => 'nullable|string|max:50',
            'gender'          => 'nullable|in:L,P',
            'birth_date'      => 'nullable|date',
            'nidn'            => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'nullable|string|max:20',
            'rfid'            => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = true;

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);

        $employee->load(['organization', 'positions.position.department']);

        $orgIds    = auth()->user()->organizationIds();
        $positions = Position::with('department')
            ->whereHas('department', function ($q) use ($employee, $orgIds) {
                $q->where('organization_id', $employee->organization_id);
                if ($orgIds !== null) {
                    $q->whereIn('organization_id', $orgIds);
                }
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employees.show', compact('employee', 'positions'));
    }

    public function edit(Employee $employee)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);

        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('employees.edit', compact('employee', 'organizations'));
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'nik'             => 'required|digits_between:1,16|unique:employees,nik,' . $employee->id,
            'name'            => 'required|string|max:100',
            'title'           => 'nullable|string|max:50',
            'gender'          => 'nullable|in:L,P',
            'birth_date'      => 'nullable|date',
            'nidn'            => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:100',
            'phone'           => 'nullable|string|max:20',
            'rfid'            => 'nullable|string|max:50',
            'is_active'       => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    public function assignPosition(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);

        $validated = $request->validate([
            'position_id' => 'required|exists:positions,id',
            'start_date'  => 'required|date',
            'notes'       => 'nullable|string|max:255',
        ]);

        // Nonaktifkan jabatan aktif sebelumnya
        $employee->positions()->where('is_active', true)->update([
            'is_active' => false,
            'end_date'  => $validated['start_date'],
        ]);

        $employee->positions()->create([
            'position_id' => $validated['position_id'],
            'start_date'  => $validated['start_date'],
            'end_date'    => null,
            'notes'       => $validated['notes'] ?? null,
            'is_active'   => true,
        ]);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Jabatan karyawan berhasil diperbarui.');
    }

    public function removePosition(Employee $employee, EmployeePosition $position)
    {
        abort_unless(auth()->user()->canAccessOrganization($employee->organization_id), 403);
        abort_unless($position->employee_id === $employee->id, 403);

        $position->delete();

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Riwayat jabatan berhasil dihapus.');
    }
}
