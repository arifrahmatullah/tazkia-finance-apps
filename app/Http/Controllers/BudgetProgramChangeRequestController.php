<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgramChangeApproval;
use App\Services\BudgetProgramChangeService;
use Illuminate\Http\Request;

class BudgetProgramChangeRequestController extends Controller
{
    public function index(Request $request)
    {
        $service = app(BudgetProgramChangeService::class);
        $pending = $service->pendingFor($request->user());

        return view('budget-program-change-requests.index', compact('pending'));
    }

    public function approve(Request $request, BudgetProgramChangeApproval $approval)
    {
        $service = app(BudgetProgramChangeService::class);
        abort_unless($service->canApprove($approval, $request->user()), 403, 'Anda tidak berhak menyetujui langkah ini.');

        $service->approve($approval, $request->user(), $request->input('notes'));

        return redirect()->route('budget-program-change-requests.index')
            ->with('success', 'Perubahan berhasil disetujui.');
    }

    public function reject(Request $request, BudgetProgramChangeApproval $approval)
    {
        $service = app(BudgetProgramChangeService::class);
        abort_unless($service->canApprove($approval, $request->user()), 403, 'Anda tidak berhak menolak langkah ini.');

        $request->validate(['notes' => 'required|string|max:500']);

        $service->reject($approval, $request->user(), $request->input('notes'));

        return redirect()->route('budget-program-change-requests.index')
            ->with('success', 'Perubahan ditolak.');
    }
}
