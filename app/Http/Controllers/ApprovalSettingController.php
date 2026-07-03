<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;

class ApprovalSettingController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $query = ApprovalSetting::with(['organization', 'requesterPosition', 'approverPosition'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds));

        if ($request->filled('organization_id')) {
            abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        $settings = $query->orderBy('organization_id')->orderBy('requester_position_id')->orderBy('step')->get();

        $grouped = $settings->groupBy(fn($s) => $s->organization_id . '_' . $s->requester_position_id);

        return view('approval-settings.index', compact('settings', 'grouped', 'organizations'));
    }

    public function create()
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $positions = Position::where('is_active', true)->orderBy('name')->get();

        return view('approval-settings.create', compact('organizations', 'positions'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'organization_id'       => 'required|integer|exists:organizations,id',
            'requester_position_id' => 'required|integer|exists:positions,id',
            'approver_position_id'  => 'required|integer|exists:positions,id',
            'step'                  => 'required|integer|min:1|max:10',
            'max_amount'            => 'nullable|numeric|min:0',
            'is_active'             => 'boolean',
        ]);

        abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);

        $exists = ApprovalSetting::where('organization_id', $request->organization_id)
            ->where('requester_position_id', $request->requester_position_id)
            ->where('step', $request->step)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['step' => 'Kombinasi organisasi, jabatan pengaju, dan urutan langkah sudah ada.']);
        }

        ApprovalSetting::create([
            'organization_id'       => $request->organization_id,
            'requester_position_id' => $request->requester_position_id,
            'approver_position_id'  => $request->approver_position_id,
            'step'                  => $request->step,
            'max_amount'            => $request->max_amount ?: null,
            'is_active'             => $request->boolean('is_active', true),
        ]);

        return redirect()->route('approval-settings.index', ['organization_id' => $request->organization_id])
            ->with('success', 'Setting approval berhasil ditambahkan.');
    }

    public function edit(ApprovalSetting $approvalSetting)
    {
        abort_unless(auth()->user()->canAccessOrganization($approvalSetting->organization_id), 403);

        $positions = Position::where('is_active', true)->orderBy('name')->get();

        return view('approval-settings.edit', compact('approvalSetting', 'positions'));
    }

    public function update(Request $request, ApprovalSetting $approvalSetting)
    {
        abort_unless(auth()->user()->canAccessOrganization($approvalSetting->organization_id), 403);

        $request->validate([
            'approver_position_id' => 'required|integer|exists:positions,id',
            'step'                 => 'required|integer|min:1|max:10',
            'max_amount'           => 'nullable|numeric|min:0',
            'is_active'            => 'boolean',
        ]);

        $exists = ApprovalSetting::where('organization_id', $approvalSetting->organization_id)
            ->where('requester_position_id', $approvalSetting->requester_position_id)
            ->where('step', $request->step)
            ->where('id', '!=', $approvalSetting->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['step' => 'Urutan langkah ini sudah digunakan untuk jabatan yang sama.']);
        }

        $approvalSetting->update([
            'approver_position_id' => $request->approver_position_id,
            'step'                 => $request->step,
            'max_amount'           => $request->max_amount ?: null,
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('approval-settings.index', ['organization_id' => $approvalSetting->organization_id])
            ->with('success', 'Setting approval berhasil diperbarui.');
    }

    public function destroy(ApprovalSetting $approvalSetting)
    {
        abort_unless(auth()->user()->canAccessOrganization($approvalSetting->organization_id), 403);
        $orgId = $approvalSetting->organization_id;
        $approvalSetting->delete();

        return redirect()->route('approval-settings.index', ['organization_id' => $orgId])
            ->with('success', 'Setting approval berhasil dihapus.');
    }
}
