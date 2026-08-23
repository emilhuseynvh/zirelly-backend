<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Crm\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'crm_user_id' => ['sometimes', 'integer'],
            'action' => ['sometimes', 'string', 'max:50'],
            'entity_type' => ['sometimes', 'string', 'max:50'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:100'],
            'dir' => ['sometimes', Rule::in(['asc', 'desc'])],
        ]);

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('crm_user_id'), fn ($q) => $q->where('crm_user_id', $request->input('crm_user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('entity_type'), fn ($q) => $q->where('entity_type', $request->input('entity_type')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->orderBy('id', $request->input('dir', 'desc'))
            ->paginate($request->integer('per_page', 30));

        return AuditLogResource::collection($logs);
    }
}
