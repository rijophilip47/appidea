<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Database\Seeders\DocumentCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected Family $familyA;
    protected User $userB;
    protected Family $familyB;
    protected DocumentCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DocumentCategorySeeder::class);
        $this->category = DocumentCategory::where('is_system', true)->first();

        // Family A
        $this->userA = User::factory()->create();
        $this->familyA = Family::create(['name' => 'Family A', 'owner_user_id' => $this->userA->id]);
        FamilyMember::create(['family_id' => $this->familyA->id, 'user_id' => $this->userA->id, 'role' => 'owner']);

        // Family B
        $this->userB = User::factory()->create();
        $this->familyB = Family::create(['name' => 'Family B', 'owner_user_id' => $this->userB->id]);
        FamilyMember::create(['family_id' => $this->familyB->id, 'user_id' => $this->userB->id, 'role' => 'owner']);
    }

    public function test_user_cannot_access_another_familys_document(): void
    {
        $docB = Document::create([
            'family_id' => $this->familyB->id,
            'category_id' => $this->category->id,
            'title' => 'Family B Secret Passport',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
        ]);

        // User A tries to view Family B's document
        $response = $this->actingAs($this->userA)
            ->withSession(['active_family_id' => $this->familyA->id])
            ->get(route('documents.show', $docB));

        $response->assertStatus(403);
    }

    public function test_user_cannot_download_another_familys_attachment(): void
    {
        Storage::fake('local');

        $docB = Document::create([
            'family_id' => $this->familyB->id,
            'category_id' => $this->category->id,
            'title' => 'Family B Property Deed',
        ]);

        $path = Storage::disk('local')->putFile('private/test', UploadedFile::fake()->create('deed.pdf', 100));

        $attachmentB = DocumentAttachment::create([
            'document_id' => $docB->id,
            'disk' => 'local',
            'storage_path' => $path,
            'original_name' => 'deed.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'uploaded_by' => $this->userB->id,
        ]);

        // User A tries to download Family B's attachment
        $response = $this->actingAs($this->userA)
            ->withSession(['active_family_id' => $this->familyA->id])
            ->get(route('documents.attachments.download', [$docB, $attachmentB]));

        $response->assertStatus(403);
    }

    public function test_csv_export_escapes_spreadsheet_formula_injection(): void
    {
        Document::create([
            'family_id' => $this->familyA->id,
            'category_id' => $this->category->id,
            'title' => '=cmd| /C calc!A0', // Malicious formula trigger
            'reference_number' => '+123456789', // Starts with +
            'notes' => '@SUM(1+1)', // Starts with @
            'expiry_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession(['active_family_id' => $this->familyA->id])
            ->post(route('exports.documents.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        // Verify malicious formula characters are escaped with a single quote prefix
        $this->assertStringContainsString("'=cmd| /C calc!A0", $csvContent);
        $this->assertStringContainsString("'+123456789", $csvContent);
        $this->assertStringContainsString("'@SUM(1+1)", $csvContent);
    }
}
