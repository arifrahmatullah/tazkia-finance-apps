<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            abort_unless($user->canAccessOrganization($request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        $settings = $query->orderBy('organization_id')->orderBy('requester_position_id')->orderBy('step')->get();
        $grouped  = $settings->groupBy(fn($s) => $s->organization_id . '_' . $s->requester_position_id);

        return view('approval-settings.index', compact('grouped', 'organizations'));
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
            'organization_id'                  => 'required|exists:organizations,id',
            'requester_position_id'            => 'required|exists:positions,id',
            'steps'                            => 'required|array|min:1|max:10',
            'steps.*.approver_position_id'     => 'required|exists:positions,id',
        ]);

        abort_unless($user->canAccessOrganization($request->organization_id), 403);

        $exists = ApprovalSetting::where('organization_id', $request->organization_id)
            ->where('requester_position_id', $request->requester_position_id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'requester_position_id' => 'Rantai approval untuk jabatan ini sudah ada. Gunakan "Edit Rantai" untuk mengubah.',
            ]);
        }

        DB::transaction(function () use ($request) {
            foreach ($request->steps as $i => $step) {
                ApprovalSetting::create([
                    'organization_id'       => $request->organization_id,
                    'requester_position_id' => $request->requester_position_id,
                    'approver_position_id'  => $step['approver_position_id'],
                    'step'                  => $i + 1,
                    'max_amount'            => null,
                    'is_active'             => true,
                ]);
            }
        });

        return redirect()->route('approval-settings.index', ['organization_id' => $request->organization_id])
            ->with('success', 'Rantai approval berhasil disimpan.');
    }

    public function editChain(Request $request)
    {
        $orgId = $request->organization_id;
        $posId = $request->requester_position_id;

        abort_unless(auth()->user()->canAccessOrganization($orgId), 403);

        $chain = ApprovalSetting::with(['requesterPosition', 'approverPosition', 'organization'])
            ->where('organization_id', $orgId)
            ->where('requester_position_id', $posId)
            ->orderBy('step')
            ->get();

        abort_if($chain->isEmpty(), 404);

        $positions = Position::where('is_active', true)->orderBy('name')->get();

        return view('approval-settings.edit-chain', compact('chain', 'positions'));
    }

    public function updateChain(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'organization_id'                  => 'required|exists:organizations,id',
            'requester_position_id'            => 'required|exists:positions,id',
            'steps'                            => 'required|array|min:1|max:10',
            'steps.*.approver_position_id'     => 'required|exists:positions,id',
        ]);

        $orgId = $request->organization_id;
        $posId = $request->requester_position_id;

        abort_unless($user->canAccessOrganization($orgId), 403);

        DB::transaction(function () use ($request, $orgId, $posId) {
            ApprovalSetting::where('organization_id', $orgId)
                ->where('requester_position_id', $posId)
                ->delete();

            foreach ($request->steps as $i => $step) {
                ApprovalSetting::create([
                    'organization_id'       => $orgId,
                    'requester_position_id' => $posId,
                    'approver_position_id'  => $step['approver_position_id'],
                    'step'                  => $i + 1,
                    'max_amount'            => null,
                    'is_active'             => true,
                ]);
            }
        });

        return redirect()->route('approval-settings.index', ['organization_id' => $orgId])
            ->with('success', 'Rantai approval berhasil diperbarui.');
    }

    public function destroy(ApprovalSetting $approvalSetting)
    {
        abort_unless(auth()->user()->canAccessOrganization($approvalSetting->organization_id), 403);
        $orgId = $approvalSetting->organization_id;
        $approvalSetting->delete();

        return redirect()->route('approval-settings.index', ['organization_id' => $orgId])
            ->with('success', 'Level approval berhasil dihapus.');
    }
}
