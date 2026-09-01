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

    // Form pembagian jadwal otomatis — 1 nominal total dipecah jadi beberapa baris bulanan
    public function createSplit(IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);

        $scheduledTotal = (float) $incomeEstimate->details()->sum('total');
        $remaining      = max(0, (float) $incomeEstimate->unit_price - $scheduledTotal);
        $defaultTotal   = $remaining > 0 ? $remaining : (float) $incomeEstimate->unit_price;

        $startMonth = $incomeEstimate->details()->max('estimate_date');
        $startMonth = $startMonth
            ? \Illuminate\Support\Carbon::parse($startMonth)->addMonthNoOverflow()->format('Y-m')
            : \Illuminate\Support\Carbon::parse($incomeEstimate->budgetPeriod->period_start)->format('Y-m');

        return view('income-estimate-details.split', [
            'estimate'       => $incomeEstimate,
            'scheduledTotal' => $scheduledTotal,
            'defaultTotal'   => $defaultTotal,
            'startMonth'     => $startMonth,
        ]);
    }

    public function storeSplit(Request $request, IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);

        $data = $request->validate([
            'total_amount'       => 'required|numeric|min:0.01',
            'start_month'        => 'required|date_format:Y-m',
            'month_count'        => 'required|integer|min:1|max:36',
            'mode'               => 'required|in:even,custom',
            'description_prefix' => 'nullable|string|max:100',
            'replace_existing'   => 'boolean',
            'amounts'            => 'required_if:mode,custom|array|size:' . (int) $request->input('month_count'),
            'amounts.*'          => 'numeric|min:0',
        ]);

        $count  = (int) $data['month_count'];
        $total  = round((float) $data['total_amount'], 2);
        $prefix = trim($data['description_prefix'] ?? '') ?: 'OPERASIONAL';
        $unitPrice = (float) $incomeEstimate->unit_price;

        if ($data['mode'] === 'custom') {
            $sum = round(array_sum(array_map('floatval', $data['amounts'])), 2);
            if (abs($sum - $total) > 0.01) {
                return back()->withInput()->withErrors([
                    'amounts' => 'Total nominal per bulan (Rp ' . number_format($sum, 0, ',', '.') . ') harus sama dengan total yang dibagi (Rp ' . number_format($total, 0, ',', '.') . ').',
                ]);
            }
        }

        // Nama bulan bahasa Indonesia dipakai eksplisit — APP_LOCALE aplikasi ini 'en',
        // jadi translatedFormat('F Y') akan menghasilkan nama bulan Inggris.
        $monthNamesId = [
            1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
            5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
        ];

        $start = \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $data['start_month'] . '-01');
        $rows  = [];
        $evenBase = $count > 0 ? round($total / $count, 2) : 0;
        $evenAccumulated = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $month   = $start->copy()->addMonthsNoOverflow($i);
            $lastDay = $month->copy()->endOfMonth();

            if ($data['mode'] === 'even') {
                $amount = ($i === $count - 1) ? round($total - $evenAccumulated, 2) : $evenBase;
                $evenAccumulated += $amount;
            } else {
                $amount = round((float) $data['amounts'][$i], 2);
            }

            $rows[] = [
                'income_estimate_id' => $incomeEstimate->id,
                'estimate_date'      => $lastDay->toDateString(),
                'description'        => $prefix . ' 01-' . $lastDay->day . ' ' . $monthNamesId[(int) $month->month] . ' ' . $month->year,
                'qty'                => $unitPrice > 0 ? round($amount / $unitPrice, 2) : 1,
                'unit_price'         => $unitPrice,
                'total'              => $amount,
            ];
        }

        \DB::transaction(function () use ($incomeEstimate, $rows, $request) {
            if ($request->boolean('replace_existing')) {
                $incomeEstimate->details()->delete();
            }
            foreach ($rows as $row) {
                IncomeEstimateDetail::create($row);
            }
            $incomeEstimate->recalculateTotal();
        });

        return redirect()->route('income-estimates.show', $incomeEstimate)
            ->with('success', count($rows) . ' jadwal berhasil dibuat.');
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
