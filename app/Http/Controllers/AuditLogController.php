<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu.audit-logs'), 403);

        $query = AuditLog::query()->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model')) {
            $query->where('auditable_type', 'like', '%' . $request->model . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        $modelTypes = AuditLog::query()
            ->selectRaw('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(fn($t) => [
                'value' => class_basename($t),
                'label' => AuditLog::modelLabel($t),
            ])
            ->sortBy('label')
            ->values();

        return view('audit-logs.index', compact('logs', 'users', 'modelTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu.audit-logs'), 403);

        return view('audit-logs.show', compact('auditLog'));
    }
}
