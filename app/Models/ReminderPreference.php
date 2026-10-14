<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'user_id',
        'default_offsets_json',
        'enabled',
        'send_time',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get offsets as an array of integers (e.g. [30, 7, 1]).
     */
    public function getOffsets(): array
    {
        $decoded = json_decode($this->default_offsets_json, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }
        return [30, 7, 1];
    }
}
