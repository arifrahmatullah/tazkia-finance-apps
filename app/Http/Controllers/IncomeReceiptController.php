<?php

namespace App\Http\Controllers;

use App\Models\IncomeEstimate;
use App\Models\IncomeReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class IncomeReceiptController extends Controller
{
    public function create(Request $request)
    {
        $estimate = IncomeEstimate::findOrFail($request->income_estimate_id);
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        return view('income-receipts.create', compact('estimate'));
    }

    public function store(Request $request)
    {
        $estimate = IncomeEstimate::findOrFail($request->income_estimate_id);
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        $data = $request->validate([
            'income_estimate_id' => 'required|exists:income_estimates,id',
            'receipt_date'       => 'required|date',
            'description'        => 'required|string|max:255',
            'qty'                => 'required|numeric|min:0.01',
            'proof'              => ['nullable', (new File())->extensions(['pdf', 'jpg', 'jpeg', 'png'])->max(10240)],
        ], [
            'proof.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $data['unit_price'] = $estimate->unit_price;
        $data['total']      = round($data['qty'] * $estimate->unit_price, 2);
        $data['recorded_by'] = auth()->id();

        \DB::transaction(function () use ($request, $data, $estimate) {
            $receipt = IncomeReceipt::create($data);

            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $receipt->update([
                    'proof_path' => $file->storeAs("income-receipts/{$receipt->id}", Str::random(40) . '.' . $file->getClientOriginalExtension(), 'public'),
                    'proof_name' => $file->getClientOriginalName(),
                ]);
            }
        });

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Realisasi penerimaan berhasil dicatat.');
    }

    public function edit(IncomeReceipt $incomeReceipt)
    {
        $estimate = $incomeReceipt->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        return view('income-receipts.edit', compact('incomeReceipt', 'estimate'));
    }

    public function update(Request $request, IncomeReceipt $incomeReceipt)
    {
        $estimate = $incomeReceipt->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        $data = $request->validate([
            'receipt_date' => 'required|date',
            'description'  => 'required|string|max:255',
            'qty'          => 'required|numeric|min:0.01',
            'proof'        => ['nullable', (new File())->extensions(['pdf', 'jpg', 'jpeg', 'png'])->max(10240)],
        ], [
            'proof.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $data['unit_price'] = $estimate->unit_price;
        $data['total']      = round($data['qty'] * $estimate->unit_price, 2);

        \DB::transaction(function () use ($request, $incomeReceipt, $data) {
            if ($request->hasFile('proof')) {
                if ($incomeReceipt->proof_path) {
                    Storage::disk('public')->delete($incomeReceipt->proof_path);
                }
                $file = $request->file('proof');
                $data['proof_path'] = $file->storeAs("income-receipts/{$incomeReceipt->id}", Str::random(40) . '.' . $file->getClientOriginalExtension(), 'public');
                $data['proof_name'] = $file->getClientOriginalName();
            }

            $incomeReceipt->update($data);
        });

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Realisasi penerimaan berhasil diperbarui.');
    }

    public function destroy(IncomeReceipt $incomeReceipt)
    {
        $estimate = $incomeReceipt->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        if ($incomeReceipt->proof_path) {
            Storage::disk('public')->delete($incomeReceipt->proof_path);
        }
        $incomeReceipt->delete();

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Realisasi penerimaan berhasil dihapus.');
    }
}
