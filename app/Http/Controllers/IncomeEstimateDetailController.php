<?php

namespace App\Http\Controllers;

use App\Models\IncomeEstimate;
use App\Models\IncomeEstimateDetail;
use Illuminate\Http\Request;

class IncomeEstimateDetailController extends Controller
{
    public function create(Request $request)
    {
        $estimate = IncomeEstimate::findOrFail($request->income_estimate_id);
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        return view('income-estimate-details.create', compact('estimate'));
    }

    public function store(Request $request)
    {
        $estimate = IncomeEstimate::findOrFail($request->income_estimate_id);
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        $data = $request->validate([
            'income_estimate_id' => 'required|exists:income_estimates,id',
            'estimate_date'      => 'required|date',
            'description'        => 'required|string|max:255',
            'qty'                => 'required|numeric|min:0.01',
        ]);

        $data['unit_price'] = $estimate->unit_price;
        $data['total']      = round($data['qty'] * $estimate->unit_price, 2);

        \DB::transaction(function () use ($data, $estimate) {
            IncomeEstimateDetail::create($data);
            $estimate->recalculateTotal();
        });

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Detail estimasi berhasil ditambahkan.');
    }

    public function edit(IncomeEstimateDetail $incomeEstimateDetail)
    {
        $estimate = $incomeEstimateDetail->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        return view('income-estimate-details.edit', compact('incomeEstimateDetail', 'estimate'));
    }

    public function update(Request $request, IncomeEstimateDetail $incomeEstimateDetail)
    {
        $estimate = $incomeEstimateDetail->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        $data = $request->validate([
            'estimate_date' => 'required|date',
            'description'   => 'required|string|max:255',
            'qty'           => 'required|numeric|min:0.01',
        ]);

        $data['unit_price'] = $estimate->unit_price;
        $data['total']      = round($data['qty'] * $estimate->unit_price, 2);

        \DB::transaction(function () use ($incomeEstimateDetail, $data, $estimate) {
            $incomeEstimateDetail->update($data);
            $estimate->recalculateTotal();
        });

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Detail estimasi berhasil diperbarui.');
    }

    public function destroy(IncomeEstimateDetail $incomeEstimateDetail)
    {
        $estimate = $incomeEstimateDetail->incomeEstimate;
        abort_unless(auth()->user()->canAccessOrganization($estimate->organization_id), 403);

        \DB::transaction(function () use ($incomeEstimateDetail, $estimate) {
            $incomeEstimateDetail->delete();
            $estimate->recalculateTotal();
        });

        return redirect()->route('income-estimates.show', $estimate)
            ->with('success', 'Detail estimasi berhasil dihapus.');
    }
}
