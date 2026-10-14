<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyMemberController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $members = FamilyMember::with('user')
            ->where('family_id', $family->id)
            ->orderByRaw("CASE WHEN role = 'owner' THEN 1 WHEN role = 'admin' THEN 2 ELSE 3 END")
            ->get();

        $pendingInvitations = FamilyInvitation::with('inviter')
            ->where('family_id', $family->id)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        $isManager = $user->canManageFamily($family->id);

        return view('family.members', compact('family', 'members', 'pendingInvitations', 'isManager'));
    }

    public function switchFamily(Family $family): RedirectResponse
    {
        $user = auth()->user();

        $isMember = $user->families()->where('families.id', $family->id)->exists();
        if (!$isMember) {
            abort(403, 'You do not belong to this family workspace.');
        }

        session(['active_family_id' => $family->id]);

        return redirect()->route('dashboard')
            ->with('info', "Switched workspace to {$family->name}.");
    }

    public function updateRole(Request $request, FamilyMember $member): RedirectResponse
    {
        $user = auth()->user();
        $family = $member->family;

        if (!$user->canManageFamily($family->id)) {
            abort(403);
        }

        if ($member->role === 'owner') {
            return back()->with('error', 'The workspace owner role cannot be changed directly.');
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,member'],
        ]);

        $oldRole = $member->role;
        $member->update(['role' => $validated['role']]);

        AuditEvent::log(
            $family->id,
            'member.role_updated',
            'FamilyMember',
            $member->id,
            [
                'member_name' => $member->user->name,
                'old_role' => $oldRole,
                'new_role' => $member->role,
            ]
        );

        return back()->with('success', "Updated role for {$member->user->name} to " . ucfirst($member->role) . ".");
    }

    public function destroy(FamilyMember $member): RedirectResponse
    {
        $user = auth()->user();
        $family = $member->family;

        if (!$user->canManageFamily($family->id)) {
            abort(403);
        }

        if ($member->role === 'owner') {
            return back()->with('error', 'The family owner cannot be removed. Transfer ownership first.');
        }

        if ($member->user_id === $user->id) {
            return back()->with('error', 'You cannot remove yourself. To leave, another administrator can remove you.');
        }

        $memberName = $member->user->name;
        $member->delete();

        AuditEvent::log(
            $family->id,
            'member.removed',
            'FamilyMember',
            null,
            ['member_name' => $memberName]
        );

        return back()->with('success', "Member {$memberName} was removed from the family workspace.");
    }
}
