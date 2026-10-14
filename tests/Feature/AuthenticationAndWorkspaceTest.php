<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentCategorySeeder::class);
    }

    public function test_user_can_register_and_automatically_receives_family_workspace(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'family_name' => "John's Family",
            'timezone' => 'Asia/Kolkata',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'timezone' => 'Asia/Kolkata',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);

        // Check family created
        $this->assertDatabaseHas('families', [
            'name' => "John's Family",
            'owner_user_id' => $user->id,
            'timezone' => 'Asia/Kolkata',
        ]);

        $family = Family::where('owner_user_id', $user->id)->first();

        // Check owner role assigned
        $this->assertDatabaseHas('family_members', [
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        // Check default reminder preferences created
        $this->assertDatabaseHas('reminder_preferences', [
            'family_id' => $family->id,
            'user_id' => null,
            'enabled' => 1,
        ]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

