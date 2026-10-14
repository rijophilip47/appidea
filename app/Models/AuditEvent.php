<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'family_id',
        'actor_user_id',
        'event_type',
        'entity_type',
        'entity_id',
        'minimal_metadata_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /**
     * Helper to log audit events securely without sensitive payloads.
     */
    public static function log(int $familyId, string $eventType, string $entityType, ?int $entityId = null, array $metadata = []): self
    {
        return self::create([
            'family_id' => $familyId,
            'actor_user_id' => auth()->id(),
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'minimal_metadata_json' => json_encode($metadata),
            'created_at' => now(),
        ]);
    }
}
