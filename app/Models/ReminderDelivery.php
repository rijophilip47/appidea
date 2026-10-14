<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'recipient_user_id',
        'due_date_snapshot',
        'offset_days',
        'scheduled_for',
        'status',
        'sent_at',
        'provider_message_id',
        'failure_reason',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'due_date_snapshot' => 'date',
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}

