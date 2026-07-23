<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\JournalTemplate;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JournalEntryApiController extends Controller
{
    // POST /api/journal-entries
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_code'       => 'required|string',
            'organization_code'   => 'required_without:organization_id|string',
            'organization_id'     => 'required_without:organization_code|uuid',
            'entry_date'          => 'required|date',
            'description'         => 'required|string|max:500',
            'external_reference'  => 'nullable|string|max:100',
            'attachment_url'      => 'nullable|string|max:255',
            'amounts'             => 'required|array|min:1',
            'amounts.*'           => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_code'    => '422',
                'response_message' => $validator->errors()->first(),
                'data'             => null,
            ], 422);
        }

        $organization = $request->filled('organization_id')
            ? Organization::find($request->organization_id)
            : Organization::where('code', $request->organization_code)->first();

        if (!$organization) {
            return response()->json([
                'response_code'    => '404',
                'response_message' => 'Organisasi tidak ditemukan.',
                'data'             => null,
            ], 404);
        }

        $template = JournalTemplate::with('details.account')
            ->where('code', $request->template_code)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json([
                'response_code'    => '404',
                'response_message' => "Template jurnal '{$request->template_code}' tidak ditemukan atau tidak aktif.",
                'data'             => null,
            ], 404);
        }

        if ($template->organization_id !== $organization->id) {
            return response()->json([
                'response_code'    => '403',
                'response_message' => 'Template tidak berlaku untuk organisasi ini.',
                'data'             => null,
            ], 403);
        }

        // Idempotensi — kembalikan jurnal yang sudah ada bila kombinasi ini pernah sukses
        if ($request->filled('external_reference')) {
            $existing = JournalEntry::with('lines.account')
                ->where('organization_id', $organization->id)
                ->where('external_reference', $request->external_reference)
                ->first();

            if ($existing) {
                return response()->json([
                    'response_code'    => '200',
                    'response_message' => 'Jurnal berhasil disimpan',
                    'data'             => $this->transform($existing),
                ]);
            }
        }

        $details = $template->details->sortBy('sequence')->values();
        $amounts = array_values($request->input('amounts'));

        if (count($amounts) !== $details->count()) {
            return response()->json([
                'response_code'    => '422',
                'response_message' => "Jumlah nominal harus sama dengan jumlah baris template ({$details->count()} baris).",
                'data'             => null,
            ], 422);
        }

        foreach ($amounts as $amount) {
            if ($amount < 0) {
                return response()->json([
                    'response_code'    => '422',
                    'response_message' => 'Nominal tidak boleh negatif.',
                    'data'             => null,
                ], 422);
            }
        }

        if (array_sum($amounts) <= 0) {
            return response()->json([
                'response_code'    => '422',
                'response_message' => 'Isi minimal satu baris dengan nominal lebih dari 0.',
                'data'             => null,
            ], 422);
        }

        foreach ($details as $i => $detail) {
            if (!$detail->account || !$detail->account->is_active) {
                $code = $detail->account->code ?? '?';
                return response()->json([
                    'response_code'    => '422',
                    'response_message' => "Akun {$code} pada baris {$detail->sequence} sudah tidak aktif, hubungi tim akunting.",
                    'data'             => null,
                ], 422);
            }
        }

        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $lines = [];

        foreach ($details as $i => $detail) {
            $amount = (float) $amounts[$i];
            $debit  = $detail->isDebit() ? $amount : 0;
            $credit = $detail->isCredit() ? $amount : 0;
            $totalDebit  += $debit;
            $totalCredit += $credit;

            $lines[] = [
                'account_id'  => $detail->account_id,
                'description' => $detail->description,
                'debit'       => $debit,
                'credit'      => $credit,
                'sort_order'  => $detail->sequence ?? $i,
            ];
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'response_code'    => '400',
                'response_message' => 'Jurnal tidak balance (selisih Rp ' .
                    number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . ').',
                'data'             => null,
            ], 400);
        }

        $entry = DB::transaction(function () use ($request, $organization, $lines) {
            $reference = JournalEntry::generateReference($organization->id, $request->entry_date);

            $entry = JournalEntry::create([
                'organization_id'     => $organization->id,
                'entry_date'          => $request->entry_date,
                'reference'           => $reference,
                'description'         => $request->description,
                'status'              => 'posted',
                'source_type'         => 'api',
                'external_reference'  => $request->external_reference,
                'attachment_url'      => $request->attachment_url,
                'posted_at'           => now(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create(array_merge($line, ['journal_entry_id' => $entry->id]));
            }

            return $entry;
        });

        $entry->load('lines.account');

        return response()->json([
            'response_code'    => '200',
            'response_message' => 'Jurnal berhasil disimpan',
            'data'             => $this->transform($entry),
        ]);
    }

    private function transform(JournalEntry $entry): array
    {
        return [
            'journal_entry_id' => $entry->id,
            'reference'        => $entry->reference,
            'status'           => $entry->status,
            'entry_date'       => $entry->entry_date->toDateString(),
            'total_debit'      => (float) $entry->lines->sum('debit'),
            'total_credit'     => (float) $entry->lines->sum('credit'),
            'lines'            => $entry->lines->sortBy('sort_order')->values()->map(fn($l) => [
                'sequence'      => $l->sort_order,
                'account_code'  => $l->account->code ?? null,
                'account_name'  => $l->account->name ?? null,
                'debit'         => (float) $l->debit,
                'credit'        => (float) $l->credit,
            ]),
        ];
    }
}
