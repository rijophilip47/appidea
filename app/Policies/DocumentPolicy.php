<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\Family;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->families()->where('families.id', $document->family_id)->exists();
    }

    /**
     * Determine whether the user can create documents in the family.
     */
    public function create(User $user, Family $family): bool
    {
        return $user->families()->where('families.id', $family->id)->exists();
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->families()->where('families.id', $document->family_id)->exists();
    }

    /**
     * Determine whether the user can renew the document.
     */
    public function renew(User $user, Document $document): bool
    {
        return $user->families()->where('families.id', $document->family_id)->exists();
    }

    /**
     * Determine whether the user can archive the document.
     */
    public function archive(User $user, Document $document): bool
    {
        return $user->families()->where('families.id', $document->family_id)->exists();
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        $role = $user->roleInFamily($document->family_id);
        if (in_array($role, ['owner', 'admin'], true)) {
            return true;
        }

        // Creator can delete if they are a current member
        return $document->created_by === $user->id;
    }

    /**
     * Determine whether the user can download attachments of the document.
     */
    public function downloadAttachment(User $user, Document $document): bool
    {
        return $user->families()->where('families.id', $document->family_id)->exists();
    }
}

