<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'crm_user_id',
        'action',
        'entity_type',
        'entity_id',
        'changes',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(CrmUser::class, 'crm_user_id');
    }

    public static function record(
        ?CrmUser $user,
        string $action,
        ?Model $entity = null,
        ?array $changes = null,
    ): void {
        static::query()->create([
            'crm_user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id' => $entity?->getKey(),
            'changes' => $changes,
            'ip' => request()?->ip(),
        ]);
    }
}
