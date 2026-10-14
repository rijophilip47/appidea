<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\ReminderDelivery;
use App\Models\User;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
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
            'name' => 'Test Family',
            'owner_user_id' => $this->user->id,
            'timezone' => 'Asia/Kolkata',
        ]);

        FamilyMember::create([
            'family_id' => $this->family->id,
            'user_id' => $this->user->id,
            'role' => 'owner',
        ]);

        $this->category = DocumentCategory::where('is_system', true)->first();
    }

    public function test_can_create_document_with_optional_file_attachment(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('policy.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('documents.store'), [
                'title' => 'Car Insurance',
                'category_id' => $this->category->id,
                'issuer' => 'ICICI Lombard',
                'reference_number' => 'POL-12345',
                'issue_date' => now()->subMonths(11)->format('Y-m-d'),
                'expiry_date' => now()->addDays(20)->format('Y-m-d'),
                'responsible_user_id' => $this->user->id,
                'notes' => 'Car renewal notes',
                'attachment' => $file,
            ]);

        $document = Document::where('title', 'Car Insurance')->first();
        $this->assertNotNull($document);

        $response->assertRedirect(route('documents.show', $document));

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'family_id' => $this->family->id,
            'title' => 'Car Insurance',
            'issuer' => 'ICICI Lombard',
        ]);

        $this->assertDatabaseHas('document_attachments', [
            'document_id' => $document->id,
            'original_name' => 'policy.pdf',
        ]);

        $attachment = DocumentAttachment::where('document_id', $document->id)->first();
        Storage::disk('local')->assertExists($attachment->storage_path);
    }

    public function test_document_computed_status_logic(): void
    {
        // 1. Expired
        $expiredDoc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Old Certificate',
            'expiry_date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        $this->assertEquals('expired', $expiredDoc->computed_status['code']);

        // 2. Due soon (e.g. 7 days away)
        $dueSoonDoc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Renew Soon',
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
        ]);
        $this->assertEquals('due_soon', $dueSoonDoc->computed_status['code']);

        // 3. Current / Valid (e.g. 120 days away)
        $currentDoc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Long Term Policy',
            'expiry_date' => now()->addDays(120)->format('Y-m-d'),
        ]);
        $this->assertEquals('current', $currentDoc->computed_status['code']);

        // 4. No expiry date
        $noExpiryDoc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Birth Certificate',
            'expiry_date' => null,
        ]);
        $this->assertEquals('no_expiry', $noExpiryDoc->computed_status['code']);
    }

    public function test_can_mark_document_as_renewed(): void
    {
        $doc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Vehicle Insurance',
            'expiry_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        // Create a pending reminder delivery
        ReminderDelivery::create([
            'document_id' => $doc->id,
            'recipient_user_id' => $this->user->id,
            'due_date_snapshot' => $doc->expiry_date,
            'offset_days' => 1,
            'scheduled_for' => now(),
            'status' => 'pending',
            'idempotency_key' => 'sample_key_1',
        ]);

        $newDate = now()->addYear()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('documents.renew', $doc), [
                'new_expiry_date' => $newDate,
                'renewal_notes' => 'Policy renewed via portal',
            ]);

        $response->assertRedirect(route('documents.show', $doc));

        $doc->refresh();
        $this->assertEquals($newDate, $doc->expiry_date->format('Y-m-d'));
        $this->assertStringContainsString('Policy renewed via portal', $doc->notes);

        // Verify pending delivery was cancelled
        $this->assertDatabaseHas('reminder_deliveries', [
            'document_id' => $doc->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_can_archive_and_unarchive_document(): void
    {
        $doc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Sold Car Insurance',
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $this->assertNull($doc->archived_at);

        // Archive
        $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('documents.archive', $doc));

        $doc->refresh();
        $this->assertNotNull($doc->archived_at);
        $this->assertEquals('archived', $doc->computed_status['code']);

        // Unarchive
        $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('documents.archive', $doc));

        $doc->refresh();
        $this->assertNull($doc->archived_at);
    }

    public function test_cannot_create_document_with_expiry_earlier_than_issue_date(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->post(route('documents.store'), [
                'title' => 'Invalid Date Policy',
                'category_id' => $this->category->id,
                'issue_date' => '2026-10-14',
                'expiry_date' => '2026-10-01', // Earlier than issue date
            ]);

        $response->assertSessionHasErrors(['expiry_date']);
    }

    public function test_can_delete_document_and_removes_physical_attachment(): void
    {
        Storage::fake('local');

        $doc = Document::create([
            'family_id' => $this->family->id,
            'category_id' => $this->category->id,
            'title' => 'Delete Me',
            'created_by' => $this->user->id,
        ]);

        $path = Storage::disk('local')->putFile('private/test', UploadedFile::fake()->create('doc.pdf', 50));

        $attachment = DocumentAttachment::create([
            'document_id' => $doc->id,
            'disk' => 'local',
            'storage_path' => $path,
            'original_name' => 'doc.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 500,
        ]);

        Storage::disk('local')->assertExists($path);

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_id' => $this->family->id])
            ->delete(route('documents.destroy', $doc));

        $response->assertRedirect(route('documents.index'));

        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        $this->assertDatabaseMissing('document_attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($path);
    }
}

