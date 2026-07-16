<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalTemplate;
use Illuminate\Http\Request;

class JournalTemplateApiController extends Controller
{
    // GET /api/journal-templates
    // Filter opsional: organization_id, organization_code, category, search, include_inactive=1
    public function index(Request $request)
    {
        $templates = JournalTemplate::with(['organization:id,code,name', 'details.account:id,code,name,account_type,normal_balance'])
            ->when(!$request->boolean('include_inactive'), fn($q) => $q->where('is_active', true))
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('organization_code'), function ($q) use ($request) {
                $q->whereHas('organization', fn($sq) => $sq->where('code', $request->organization_code));
            })
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->category))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('code', 'like', $s)->orWhere('name', 'like', $s));
            })
            ->orderBy('code')
            ->get();

        return response()->json([
            'response_code'    => '200',
            'response_message' => 'Success',
            'data'             => $templates->map(fn($t) => $this->transform($t)),
        ]);
    }

    // GET /api/journal-templates/{journalTemplate}
    public function show(JournalTemplate $journalTemplate)
    {
        $journalTemplate->load(['organization:id,code,name', 'details.account:id,code,name,account_type,normal_balance']);

        return response()->json([
            'response_code'    => '200',
            'response_message' => 'Success',
            'data'             => $this->transform($journalTemplate),
        ]);
    }

    private function transform(JournalTemplate $t): array
    {
        return [
            'id'           => $t->id,
            'code'         => $t->code,
            'name'         => $t->name,
            'category'     => $t->category,
            'is_active'    => $t->is_active,
            'organization' => $t->organization ? [
                'id'   => $t->organization->id,
                'code' => $t->organization->code,
                'name' => $t->organization->name,
            ] : null,
            'details' => $t->details->map(fn($d) => [
                'sequence'     => $d->sequence,
                'balance_type' => $d->balance_type,
                'description'  => $d->description,
                'account'      => $d->account ? [
                    'id'             => $d->account->id,
                    'code'           => $d->account->code,
                    'name'           => $d->account->name,
                    'account_type'   => $d->account->account_type,
                    'normal_balance' => $d->account->normal_balance,
                ] : null,
            ])->values(),
            'created_at' => $t->created_at?->toIso8601String(),
            'updated_at' => $t->updated_at?->toIso8601String(),
        ];
    }
}
