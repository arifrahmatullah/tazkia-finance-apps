<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class FundRequestBlockController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::with('fundRequestBlockedBy')
            ->when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('fund-request-blocks.index', compact('organizations'));
    }

    public function update(Request $request, Organization $organization)
    {
        abort_unless(auth()->user()->canAccessOrganization($organization->id), 403);

        $request->validate([
            'blocked' => 'required|boolean',
            'reason'  => 'nullable|string|max:255',
        ]);

        if ($request->boolean('blocked')) {
            $organization->update([
                'fund_request_blocked'      => true,
                'fund_request_block_reason' => $request->input('reason'),
                'fund_request_blocked_at'   => now(),
                'fund_request_blocked_by'   => auth()->id(),
            ]);
            $message = "Pengajuan dana untuk \"{$organization->name}\" berhasil ditutup sementara.";
        } else {
            $organization->update([
                'fund_request_blocked'      => false,
                'fund_request_block_reason' => null,
                'fund_request_blocked_at'   => null,
                'fund_request_blocked_by'   => null,
            ]);
            $message = "Pengajuan dana untuk \"{$organization->name}\" berhasil dibuka kembali.";
        }

        return redirect()->route('fund-request-blocks.index')->with('success', $message);
    }
}
