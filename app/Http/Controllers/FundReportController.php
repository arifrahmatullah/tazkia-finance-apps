<?php

namespace App\Http\Controllers;

use App\Models\FundReport;
use App\Models\FundReportFile;
use App\Models\FundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FundReportController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $employee = $user->employee;

        // Pengajuan sudah cair tapi belum ada laporan yang approved/waiting
        $pendingFundRequests = collect();
        if ($employee) {
            $reportedIds = FundReport::where('reported_by', $user->id)
                ->whereIn('status', ['waiting', 'approved'])
                ->pluck('fund_request_id');

            $pendingFundRequests = FundRequest::with(['department', 'budgetProgram'])
                ->where('requester_id', $employee->id)
                ->whereNotNull('disbursed_at')
                ->whereNotIn('id', $reportedIds)
                ->latest('disbursed_at')
                ->get();
        }

        $reports = FundReport::with(['fundRequest', 'files'])
            ->where('reported_by', $user->id)
            ->latest()
            ->paginate(15);

        return view('fund-reports.index', compact('reports', 'pendingFundRequests'));
    }

    public function create(Request $request)
    {
        $fundRequest = FundRequest::with(['department', 'budgetProgram', 'fundReports'])
            ->findOrFail($request->query('fund_request'));

        $user = Auth::user();

        if ($fundRequest->requester_id !== $user->employee?->id) {
            abort(403, 'Hanya pengaju yang bisa membuat laporan.');
        }

        if (!$fundRequest->isDisbursed()) {
            return redirect()->route('fund-requests.index')
                ->with('error', 'Dana belum dicairkan, belum bisa membuat laporan.');
        }

        return view('fund-reports.create', compact('fundRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fund_request_id' => 'required|uuid|exists:fund_requests,id',
            'report_date'     => 'required|date',
            'description'     => 'required|string|max:2000',
            'amount_used'     => 'required|numeric|min:0',
            'files'           => 'required|array|min:1',
            'files.*'         => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ], [
            'files.required'  => 'Minimal 1 file bukti pengeluaran wajib dilampirkan.',
            'files.*.max'     => 'Ukuran file maksimal 10 MB.',
        ]);

        $user = Auth::user();
        $fundRequest = FundRequest::findOrFail($validated['fund_request_id']);

        if ($fundRequest->requester_id !== $user->employee?->id) {
            abort(403);
        }

        if (!$fundRequest->isDisbursed()) {
            return back()->with('error', 'Dana belum dicairkan.');
        }

        if ($validated['amount_used'] > (float) $fundRequest->amount) {
            return back()->withInput()->withErrors([
                'amount_used' => 'Total penggunaan dana tidak boleh melebihi dana yang diajukan (Rp ' . number_format($fundRequest->amount, 0, ',', '.') . ').',
            ]);
        }

        $report = FundReport::create([
            'fund_request_id' => $fundRequest->id,
            'reported_by'     => $user->id,
            'report_date'     => $validated['report_date'],
            'description'     => $validated['description'],
            'amount_used'     => $validated['amount_used'],
            'status'          => 'waiting',
        ]);

        foreach ($request->file('files', []) as $file) {
            $path = $file->store("fund-reports/{$report->id}", 'public');
            FundReportFile::create([
                'fund_report_id' => $report->id,
                'uploaded_by'    => $user->id,
                'file_path'      => $path,
                'file_name'      => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
                'file_size'      => $file->getSize(),
            ]);
        }

        return redirect()->route('fund-reports.show', $report)
            ->with('success', 'Laporan penggunaan dana berhasil dikirim.');
    }

    public function show(FundReport $fundReport)
    {
        $user = Auth::user();

        $canView = $fundReport->reported_by === $user->id
            || $user->hasPermission('menu.pencairan-dana');

        if (!$canView) abort(403);

        $fundReport->load(['fundRequest.department', 'fundRequest.budgetProgram', 'files.uploader', 'reporter', 'reviewer']);

        return view('fund-reports.show', compact('fundReport'));
    }

    public function deleteFile(FundReportFile $fundReportFile)
    {
        $user = Auth::user();
        $report = $fundReportFile->fundReport;

        if ($report->reported_by !== $user->id) abort(403);
        if (!$report->isRejected() && !$report->isWaiting()) {
            return back()->with('error', 'File tidak bisa dihapus setelah laporan disetujui.');
        }

        Storage::disk('public')->delete($fundReportFile->file_path);
        $fundReportFile->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }
}
