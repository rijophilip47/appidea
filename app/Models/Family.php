<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_user_id',
        'timezone',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(FamilyInvitation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(DocumentCategory::class);
    }

    public function reminderPreferences(): HasMany
    {
        return $this->hasMany(ReminderPreference::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }

    /**
     * Get the default reminder preference for this family.
     */
    public function defaultReminderPreference(): ReminderPreference
    {
        return $this->reminderPreferences()
            ->whereNull('user_id')
            ->firstOrCreate([
                'family_id' => $this->id,
                'user_id' => null,
            ], [
                'default_offsets_json' => json_encode([30, 7, 1]),
                'enabled' => true,
                'send_time' => '09:00',
                'timezone' => $this->timezone ?? 'Asia/Kolkata',
            ]);
    }
}
