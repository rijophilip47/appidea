<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    /**
     * Determine whether the user can view the family.
     */
    public function view(User $user, Family $family): bool
    {
        return $user->families()->where('families.id', $family->id)->exists();
    }

    /**
     * Determine whether the user can update the family settings.
     */
    public function update(User $user, Family $family): bool
    {
        return $user->canManageFamily($family->id);
    }

    /**
     * Determine whether the user can invite or manage members.
     */
    public function manageMembers(User $user, Family $family): bool
    {
        return $user->canManageFamily($family->id);
    }

    /**
     * Determine whether the user can export family data.
     */
    public function export(User $user, Family $family): bool
    {
        return $user->canManageFamily($family->id);
    }

    /**
     * Determine whether the user can delete the entire family workspace.
     */
    public function delete(User $user, Family $family): bool
    {
        return $user->isOwnerOfFamily($family->id);
    }
}
