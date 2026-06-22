<?php

namespace App\Http\Controllers\Concerns;

use App\Services\AuditLogService;
use Illuminate\Http\Request;

trait AuditsActions
{
    protected function audit(
        string $action,
        string $module,
        ?int $recordId = null,
        ?string $description = null,
        array $metadata = [],
        ?Request $request = null
    ): void {
        $user = ($request ?? request())->user();

        app(AuditLogService::class)->log(
            $user,
            $action,
            $module,
            $recordId,
            $description,
            $metadata,
            $request ?? request()
        );
    }
}
