<?php

namespace Tests\Feature;

use App\Mail\FamilyInvitationMail;
use App\Models\Family;
use App\Models\FamilyInvitation;
use App\Models\FamilyMember;
use App\Models\User;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FamilySharingAndInvitationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Family $family;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentCategorySeeder::class);

        $this->owner = User::factory()->create();
        $this->family = Family::create(['name' => 'The Robinsons', 'owner_user_id' => $this->owner->id]);
        FamilyMember::create(['family_id' => $this->family->id, 'user_id' => $this->owner->id, 'role' => 'owner']);
    }

    public function test_owner_can_invite_member_via_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->owner)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('family.invitations.store'), [
                'email' => 'spouse@example.com',
                'role' => 'admin',
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('family_invitations', [
            'family_id' => $this->family->id,
            'email' => 'spouse@example.com',
            'role' => 'admin',
        ]);

        Mail::assertSent(FamilyInvitationMail::class, function ($mail) {
            return $mail->hasTo('spouse@example.com');
        });
    }

    public function test_invitee_can_accept_invitation_and_join_workspace(): void
    {
        $invitee = User::factory()->create(['email' => 'spouse@example.com']);
        $plainToken = 'test_plain_invitation_token_12345';
        $tokenHash = hash('sha256', $plainToken);

        $invitation = FamilyInvitation::create([
            'family_id' => $this->family->id,
            'email' => 'spouse@example.com',
            'role' => 'admin',
            'token_hash' => $tokenHash,
            'invited_by' => $this->owner->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($invitee)
            ->post(route('invitations.accept', ['token' => $plainToken]));

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('family_members', [
            'family_id' => $this->family->id,
            'user_id' => $invitee->id,
            'role' => 'admin',
        ]);

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }
}
