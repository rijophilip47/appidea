<?php

namespace Tests\Feature;

use App\Mail\DocumentExpiryReminderMail;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\ReminderDelivery;
use App\Models\ReminderPreference;
use App\Models\User;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Family $family;
    protected DocumentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentCategorySeeder::class);

        $this->user = User::factory()->create(['timezone' => 'Asia/Kolkata']);
        $this->family = Family::create([
            'name' => 'Reminder Test Family',
            'owner_user_id' => $this->user->id,
            'timezone' => 'Asia/Kolkata',
        ]);

        FamilyMember::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        ReminderPreference::create([
            'family_id' => $this->family->id,
            'user_id' => null,
            'default_offsets_json' => json_encode([30, 7, 1]),
            'enabled' => true,
            'send_time' => '09:00',
            'timezone' => 'Asia/Kolkata',
        ]);

        $this->category = DocumentCategory::where('is_system', true)->first();
    }

    public function test_reminder_command_dispatches_email_and_prevents_duplicates(): void
    {
        Mail::fake();

        // Create a document expiring exactly 7 days from today (matching offset 7)
        $doc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'created_by' => $this->user->id,
            'title' => 'Car Insurance',
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        // Run reminders command for the first time
        $this->artisan('reminders:send')
            ->assertSuccessful();

        // 1. Mail was sent once
        Mail::assertSent(DocumentExpiryReminderMail::class, 1);

        // 2. Reminder delivery recorded with status = 'sent'
        $this->assertDatabaseHas('reminder_deliveries', [
            'document_id' => $doc->id,
            'recipient_user_id' => $this->user->id,
            'offset_days' => 7,
            'status' => 'sent',
        ]);

        $delivery = ReminderDelivery::where('document_id', $doc->id)->first();
        $this->assertNotNull($delivery->idempotency_key);

        // 3. Run reminders command a second time on the same day: NO duplicate email sent!
        $this->artisan('reminders:send')
            ->assertSuccessful();

        Mail::assertSent(DocumentExpiryReminderMail::class, 1); // Still exactly 1!
    }

    public function test_user_can_disable_reminders_and_system_respects_preference(): void
    {
        Mail::fake();

        // Disable personal reminders for this user
        ReminderPreference::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'default_offsets_json' => json_encode([7]),
            'enabled' => false, // Disabled
            'send_time' => '09:00',
            'timezone' => 'Asia/Kolkata',
        ]);

        Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Car Insurance',
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $this->artisan('reminders:send')
            ->assertSuccessful();

        // No mail sent because user opted out
        Mail::assertNothingSent();
    }
}
