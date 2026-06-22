<?php

namespace App\Http\Controllers;

use App\Models\tbl_audit_log;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'module' => 'nullable|string|max:50',
            'action' => 'nullable|string|max:20',
            'user_id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'paginate' => 'nullable|integer|min:1|max:100',
        ]);

        $paginate = $validated['paginate'] ?? 25;
        $query = tbl_audit_log::query()->orderByDesc('audit_log_id');

        if (!empty($validated['module'])) {
            $query->where('module', $validated['module']);
        }

        if (!empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereBetween('created_at', [
                $validated['from_date'] . ' 00:00:00',
                $validated['to_date'] . ' 23:59:59',
            ]);
        }

        return response()->json($query->paginate($paginate));
    }
}
