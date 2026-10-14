<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteFamilyMemberRequest;
use App\Models\FamilyInvitation;
use App\Services\FamilyInvitationService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyInvitationController extends Controller
{
    public function __construct(
        protected FamilyInvitationService $invitationService
    ) {}

    public function store(InviteFamilyMemberRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        if (!$user->canManageFamily($family->id)) {
            abort(403, 'Only family owners and administrators can invite new members.');
        }

        try {
            $this->invitationService->createInvitation(
                $family,
                $request->validated('email'),
                $request->validated('role'),
                $user
            );

            return back()->with('success', "An invitation link has been sent to {$request->validated('email')}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revoke(FamilyInvitation $invitation): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->canManageFamily($invitation->family_id)) {
            abort(403);
        }

        $this->invitationService->revokeInvitation($invitation);

        return back()->with('success', "Invitation for {$invitation->email} has been revoked.");
    }

    public function show(string $token): View|RedirectResponse
    {
        $tokenHash = hash('sha256', $token);

        $invitation = FamilyInvitation::with(['family', 'inviter'])
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$invitation || !$invitation->isValid()) {
            return view('family.invitation_invalid');
        }

        return view('family.invitation_show', [
            'invitation' => $invitation,
            'plainToken' => $token,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login', ['invitation' => $token])
                ->with('info', 'Please sign in or create an account to accept the invitation.');
        }

        $user = auth()->user();

        try {
            $family = $this->invitationService->acceptInvitation($token, $user);

            return redirect()->route('dashboard')
                ->with('success', "You have joined the '{$family->name}' family workspace as " . ucfirst($user->roleInFamily($family->id)) . "!");
        } catch (Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }
    }
}
