<?php

namespace Database\Seeders;

use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\ReminderPreference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoHouseholdSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create primary household owner
        $philip = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Philip Matthew',
                'password' => Hash::make('password'),
                'timezone' => 'Asia/Kolkata',
                'email_verified_at' => now(),
            ]
        );

        // 2. Create second household member
        $sarah = User::firstOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'name' => 'Sarah Matthew',
                'password' => Hash::make('password'),
                'timezone' => 'Asia/Kolkata',
                'email_verified_at' => now(),
            ]
        );

        // 3. Create family workspace
        $family = Family::firstOrCreate(
            ['name' => 'Philip Household'],
            [
                'owner_user_id' => $philip->id,
                'timezone' => 'Asia/Kolkata',
            ]
        );

        // 4. Attach members
        FamilyMember::firstOrCreate(
            ['family_id' => $family->id, 'user_id' => $philip->id],
            ['role' => 'owner', 'joined_at' => now()->subMonths(6)]
        );

        FamilyMember::firstOrCreate(
            ['family_id' => $family->id, 'user_id' => $sarah->id],
            ['role' => 'admin', 'joined_at' => now()->subMonths(5)]
        );

        // 5. Default reminder preference
        ReminderPreference::firstOrCreate(
            ['family_id' => $family->id, 'user_id' => null],
            [
                'default_offsets_json' => json_encode([30, 7, 1]),
                'enabled' => true,
                'send_time' => '09:00',
                'timezone' => 'Asia/Kolkata',
            ]
        );

        // 6. Categories lookup
        $vehicle = DocumentCategory::where('slug', 'vehicle')->first();
        $insurance = DocumentCategory::where('slug', 'insurance')->first();
        $travel = DocumentCategory::where('slug', 'identity-travel')->first();
        $warranty = DocumentCategory::where('slug', 'appliance-warranty')->first();
        $property = DocumentCategory::where('slug', 'property-housing')->first();

        // 7. Seed sample documents
        $documents = [
            [
                'title' => 'Maruti Suzuki Swift Comprehensive Insurance',
                'category_id' => $vehicle?->id,
                'issuer' => 'HDFC ERGO General Insurance',
                'reference_number' => 'POL-2025-KL07-8821',
                'issue_date' => now()->subYear()->addDays(5)->format('Y-m-d'),
                'expiry_date' => now()->addDays(5)->format('Y-m-d'), // Due in 5 days
                'responsible_user_id' => $philip->id,
                'notes' => 'Zero depreciation cover included. Contact local agent Manoj: +91 98765 43210 for renewal inspection.',
            ],
            [
                'title' => 'Vehicle Emission (PUC) Certificate - Car',
                'category_id' => $vehicle?->id,
                'issuer' => 'Kerala Motor Vehicles Dept',
                'reference_number' => 'KL-07-2026-PUC-9102',
                'issue_date' => now()->subMonths(6)->subDays(4)->format('Y-m-d'),
                'expiry_date' => now()->subDays(4)->format('Y-m-d'), // Expired 4 days ago
                'responsible_user_id' => $philip->id,
                'notes' => 'Expired testing certificate. Need to visit nearby petrol pump testing booth immediately.',
            ],
            [
                'title' => 'LG 55-inch 4K OLED TV Extended Warranty',
                'category_id' => $warranty?->id,
                'issuer' => 'LG Electronics India / Reliance Digital',
                'reference_number' => 'WRN-LG-2024-99881',
                'purchase_date' => now()->subYears(2)->addDays(22)->format('Y-m-d'),
                'expiry_date' => now()->addDays(22)->format('Y-m-d'), // Due in 22 days
                'responsible_user_id' => $sarah->id,
                'notes' => 'Invoice stored in home office filing cabinet folder 3. Extended panel protection expires this month.',
            ],
            [
                'title' => 'Indian Passport - Philip Matthew',
                'category_id' => $travel?->id,
                'issuer' => 'Passport Seva Kendra, Cochin',
                'reference_number' => 'Z9102834',
                'issue_date' => now()->subYears(5)->format('Y-m-d'),
                'expiry_date' => now()->addYears(5)->format('Y-m-d'), // Valid
                'responsible_user_id' => $philip->id,
                'notes' => 'Standard 36-page booklet. Keep checked for 6-month validity rule before international bookings.',
            ],
            [
                'title' => 'Star Health Comprehensive Family Mediclaim',
                'category_id' => $insurance?->id,
                'issuer' => 'Star Health & Allied Insurance',
                'reference_number' => 'SH-FL-2025-77610',
                'issue_date' => now()->subMonths(10)->format('Y-m-d'),
                'expiry_date' => now()->addDays(50)->format('Y-m-d'), // Current
                'responsible_user_id' => $sarah->id,
                'notes' => 'Floater policy covering Philip, Sarah, and kids. Sum insured Rs. 15,00,000. Cashless TPA card in glovebox.',
            ],
            [
                'title' => 'Apartment Lease & Security Deposit Agreement',
                'category_id' => $property?->id,
                'issuer' => 'Skyline Builders / Landlord',
                'reference_number' => 'LEASE-2026-FLAT4B',
                'issue_date' => now()->subMonths(5)->format('Y-m-d'),
                'expiry_date' => now()->addMonths(6)->format('Y-m-d'), // Current
                'responsible_user_id' => $philip->id,
                'notes' => '11-Month registered lease agreement. 2 months notice required prior to renewal or vacating.',
            ],
            [
                'title' => 'Government Birth Certificate (Original)',
                'category_id' => $travel?->id,
                'issuer' => 'Municipal Corporation of Greater Cochin',
                'reference_number' => 'MC-BC-1994-00192',
                'issue_date' => '1994-08-20',
                'expiry_date' => null, // No expiry
                'responsible_user_id' => null,
                'notes' => 'Permanent vital record. Original laminated certificate kept in household bank safe deposit locker.',
            ],
        ];

        foreach ($documents as $docData) {
            $doc = Document::firstOrCreate(
                [
                    'family_id' => $family->id,
                    'title' => $docData['title'],
                ],
                array_merge($docData, [
                    'family_id' => $family->id,
                    'created_by' => $philip->id,
                ])
            );

            AuditEvent::firstOrCreate([
                'family_id' => $family->id,
                'actor_user_id' => $philip->id,
                'event_type' => 'document.created',
                'entity_type' => 'Document',
                'entity_id' => $doc->id,
            ], [
                'minimal_metadata_json' => json_encode(['title' => $doc->title]),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
