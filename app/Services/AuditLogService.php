<?php

namespace App\Services;

use App\Models\tbl_audit_log;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(
        ?User $user,
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $description = null,
        array $metadata = [],
        ?Request $request = null
    ): tbl_audit_log {
        $request = $request ?? request();

        return tbl_audit_log::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
