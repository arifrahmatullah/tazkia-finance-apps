<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $search = $request->input('search');

        $positions = Position::with([
                'department.organization',
                'employeePositions' => fn($q) => $q->where('is_active', true)->with('employee:id,name'),
            ])
            ->withCount(['employeePositions as active_employees_count' => fn($q) => $q->where('is_active', true)])
            ->when($orgIds !== null, fn($q) => $q->whereHas('department', fn($d) => $d->whereIn('organization_id', $orgIds)))
            ->when($search, fn($q) => $q->where(fn($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhereHas('department', fn($d) => $d->where('name', 'like', "%{$search}%"))
            ))
            ->orderBy('department_id')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('positions.index', compact('positions', 'search'));
    }

    private function allowedDepartments(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        $query  = Department::with('organization')->where('is_active', true);
        return $orgIds !== null
            ? $query->whereIn('organization_id', $orgIds)
            : $query;
    }

    private function deptBelongsToUser(Department $dept): bool
    {
        return auth()->user()->canAccessOrganization($dept->organization_id);
    }

    public function create()
    {
        $departments = $this->allowedDepartments()->orderBy('name')->get();
        return view('positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $dept = Department::findOrFail($request->department_id);
        abort_unless($this->deptBelongsToUser($dept), 403);

        $validated = $request->validate([
            'department_id'      => 'required|exists:departments,id',
            'code'               => 'required|string|max:50',
            'name'               => 'required|string|max:100',
            'description'        => 'nullable|string|max:255',
            'is_finance_related' => 'boolean',
            'can_create_program' => 'boolean',
        ]);

        $validated['is_finance_related'] = $request->boolean('is_finance_related');
        $validated['can_create_program'] = $request->boolean('can_create_program');
        $validated['is_active']          = true;
        $validated['code']               = strtoupper($validated['code']);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position)
    {
        abort_unless($this->deptBelongsToUser($position->department), 403);

        $departments = $this->allowedDepartments()->orderBy('name')->get();
        return view('positions.edit', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        abort_unless($this->deptBelongsToUser($position->department), 403);

        $newDept = Department::findOrFail($request->department_id);
        abort_unless($this->deptBelongsToUser($newDept), 403);

        $validated = $request->validate([
            'department_id'      => 'required|exists:departments,id',
            'code'               => 'required|string|max:50',
            'name'               => 'required|string|max:100',
            'description'        => 'nullable|string|max:255',
            'is_finance_related' => 'boolean',
            'can_create_program' => 'boolean',
            'is_active'          => 'boolean',
        ]);

        $validated['is_finance_related'] = $request->boolean('is_finance_related');
        $validated['can_create_program'] = $request->boolean('can_create_program');
        $validated['is_active']          = $request->boolean('is_active');
        $validated['code']               = strtoupper($validated['code']);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        abort_unless($this->deptBelongsToUser($position->department), 403);

        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}
