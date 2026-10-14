<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'category_id',
        'created_by',
        'responsible_user_id',
        'title',
        'issuer',
        'reference_number',
        'issue_date',
        'purchase_date',
        'expiry_date',
        'notes',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'purchase_date' => 'date',
            'expiry_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    public function reminderDeliveries(): HasMany
    {
        return $this->hasMany(ReminderDelivery::class);
    }

    /**
     * Scope active records (not archived).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope archived records.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Compute document status based on expiry_date and family timezone.
     */
    public function getComputedStatusAttribute(): array
    {
        if ($this->archived_at !== null) {
            return [
                'code' => 'archived',
                'label' => 'Archived',
                'badge' => 'secondary',
                'icon' => 'bi-archive',
                'days' => null,
            ];
        }

        if ($this->expiry_date === null) {
            return [
                'code' => 'no_expiry',
                'label' => 'No Expiry Date',
                'badge' => 'info',
                'icon' => 'bi-infinity',
                'days' => null,
            ];
        }

        $timezone = $this->family ? $this->family->timezone : 'Asia/Kolkata';
        $today = Carbon::now($timezone)->startOfDay();
        $expiry = Carbon::parse($this->expiry_date, $timezone)->startOfDay();

        $diffDays = (int) $today->diffInDays($expiry, false);

        if ($diffDays < 0) {
            return [
                'code' => 'expired',
                'label' => 'Expired ' . abs($diffDays) . ' days ago',
                'badge' => 'danger',
                'icon' => 'bi-exclamation-octagon-fill',
                'days' => $diffDays,
            ];
        }

        if ($diffDays === 0) {
            return [
                'code' => 'expires_today',
                'label' => 'Expires Today',
                'badge' => 'danger',
                'icon' => 'bi-exclamation-triangle-fill',
                'days' => 0,
            ];
        }

        if ($diffDays <= 30) {
            return [
                'code' => 'due_soon',
                'label' => 'Due in ' . $diffDays . ' days',
                'badge' => 'warning text-dark',
                'icon' => 'bi-clock-history',
                'days' => $diffDays,
            ];
        }

        return [
            'code' => 'current',
            'label' => 'Valid (' . $diffDays . ' days left)',
            'badge' => 'success',
            'icon' => 'bi-check-circle-fill',
            'days' => $diffDays,
        ];
    }
}
