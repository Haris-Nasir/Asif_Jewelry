<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function workers()
    {
        $workers = User::where('role', 'worker')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'permissions']);

        return response()->json([
            'data' => $workers,
            'permission_labels' => config('permissions.labels', []),
        ]);
    }

    public function updatePermissions(Request $request, int $userId)
    {
        $worker = User::where('id', $userId)->where('role', 'worker')->firstOrFail();

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => ['string', Rule::in(array_keys(config('permissions.labels', [])))],
        ]);

        $worker->permissions = array_values(array_unique($validated['permissions']));
        $worker->save();

        app(AuditLogService::class)->log(
            $request->user(),
            'update',
            'user_permissions',
            $worker->id,
            'Updated worker permissions for ' . $worker->name,
            ['permissions' => $worker->permissions]
        );

        return response()->json([
            'status' => 1,
            'message' => 'Worker permissions updated successfully.',
            'data' => $worker,
        ]);
    }

    public function storeWorker(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in(array_keys(config('permissions.labels', [])))],
        ]);

        $worker = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'] ?? 'password'),
            'role' => 'worker',
            'permissions' => $validated['permissions'] ?? config('permissions.worker_defaults', []),
        ]);

        app(AuditLogService::class)->log(
            $request->user(),
            'create',
            'user',
            $worker->id,
            'Created worker account: ' . $worker->email
        );

        return response()->json([
            'status' => 1,
            'message' => 'Worker created successfully.',
            'data' => $worker,
        ]);
    }
}
