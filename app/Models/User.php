<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function families(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function ownedFamilies(): HasMany
    {
        return $this->hasMany(Family::class, 'owner_user_id');
    }

    public function reminderPreferences(): HasMany
    {
        return $this->hasMany(ReminderPreference::class);
    }

    /**
     * Get the user's role in a specific family.
     */
    public function roleInFamily(int $familyId): ?string
    {
        $member = $this->familyMembers()->where('family_id', $familyId)->first();
        return $member ? $member->role : null;
    }

    /**
     * Check if user is owner or admin in a family.
     */
    public function canManageFamily(int $familyId): bool
    {
        $role = $this->roleInFamily($familyId);
        return in_array($role, ['owner', 'admin'], true);
    }

    /**
     * Check if user is owner of a family.
     */
    public function isOwnerOfFamily(int $familyId): bool
    {
        return $this->roleInFamily($familyId) === 'owner';
    }

    /**
     * Resolve the current active family for this user.
     */
    public function currentFamily(): ?Family
    {
        $familyId = session('active_family_id');

        if ($familyId) {
            $family = $this->families()->where('families.id', $familyId)->first();
            if ($family) {
                return $family;
            }
        }

        // Fallback to first available family
        $defaultFamily = $this->families()->first();
        if ($defaultFamily) {
            session(['active_family_id' => $defaultFamily->id]);
            return $defaultFamily;
        }

        return null;
    }
}
