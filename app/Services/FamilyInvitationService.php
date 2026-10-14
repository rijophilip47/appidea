<?php

namespace App\Services;

use App\Mail\FamilyInvitationMail;
use App\Models\AuditEvent;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FamilyInvitationService
{
    /**
     * Create and send a new invitation.
     */
    public function createInvitation(Family $family, string $email, string $role, User $inviter): array
    {
        // 1. Check if user is already a member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $family->users()->where('users.id', $existingUser->id)->exists()) {
            throw new InvalidArgumentException("A user with email {$email} is already a member of this family workspace.");
        }

        // 2. Revoke any existing pending invitations for this email in this family
        FamilyInvitation::where('family_id', $family->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        // 3. Generate high-entropy token
        $plainToken = Str::random(40);
        $tokenHash = hash('sha256', $plainToken);

        $invitation = FamilyInvitation::create([
            'family_id' => $family->id,
            'email' => strtolower(trim($email)),
            'role' => in_array($role, ['admin', 'member'], true) ? $role : 'member',
            'token_hash' => $tokenHash,
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        // 4. Send email
        Mail::to($invitation->email)->send(new FamilyInvitationMail($invitation, $plainToken));

        // 5. Log audit event
        AuditEvent::log(
            $family->id,
            'invitation.sent',
            'FamilyInvitation',
            $invitation->id,
            ['email' => $invitation->email, 'role' => $invitation->role]
        );

        return [
            'invitation' => $invitation,
            'token' => $plainToken,
        ];
    }

    /**
     * Revoke an active invitation.
     */
    public function revokeInvitation(FamilyInvitation $invitation): void
    {
        $invitation->update(['revoked_at' => now()]);

        AuditEvent::log(
            $invitation->family_id,
            'invitation.revoked',
            'FamilyInvitation',
            $invitation->id,
            ['email' => $invitation->email]
        );
    }

    /**
     * Accept an invitation using the plain token.
     */
    public function acceptInvitation(string $plainToken, User $user): Family
    {
        $tokenHash = hash('sha256', $plainToken);

        $invitation = FamilyInvitation::with('family')
            ->where('token_hash', $tokenHash)
            ->firstOrFail();

        if (!$invitation->isValid()) {
            throw new InvalidArgumentException('This invitation is no longer valid or has expired.');
        }

        return DB::transaction(function () use ($invitation, $user) {
            // Attach user to family
            FamilyMember::updateOrCreate(
                [
                    'family_id' => $invitation->family_id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $invitation->role,
                    'joined_at' => now(),
                ]
            );

            // Mark invitation accepted
            $invitation->update(['accepted_at' => now()]);

            // Set as active family in session
            session(['active_family_id' => $invitation->family_id]);

            AuditEvent::log(
                $invitation->family_id,
                'invitation.accepted',
                'FamilyMember',
                $user->id,
                ['email' => $user->email, 'role' => $invitation->role]
            );

            return $invitation->family;
        });
    }
}
