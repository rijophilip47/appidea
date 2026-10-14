<?php

namespace App\Services;

use App\Mail\DocumentExpiryReminderMail;
use App\Models\Document;
use App\Models\Family;
use App\Models\ReminderDelivery;
use App\Models\ReminderPreference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReminderScheduleService
{
    /**
     * Invalidate obsolete pending reminders when a document's expiry date changes,
     * or when the document is renewed, archived, or deleted.
     */
    public function invalidateObsoleteReminders(Document $document): int
    {
        return ReminderDelivery::where('document_id', $document->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'failure_reason' => 'Document date changed or record was archived/renewed',
            ]);
    }

    /**
     * Process reminders across all families.
     */
    public function processAllDueReminders(): array
    {
        $families = Family::with(['reminderPreferences', 'users'])->get();
        $totalSent = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($families as $family) {
            $result = $this->processRemindersForFamily($family);
            $totalSent += $result['sent'];
            $totalFailed += $result['failed'];
            $totalSkipped += $result['skipped'];
        }

        return [
            'sent' => $totalSent,
            'failed' => $totalFailed,
            'skipped' => $totalSkipped,
        ];
    }

    /**
     * Process due reminders for a single family.
     */
    public function processRemindersForFamily(Family $family): array
    {
        $timezone = $family->timezone ?? 'Asia/Kolkata';
        $today = Carbon::now($timezone)->startOfDay();

        $familyPref = $family->defaultReminderPreference();
        if (!$familyPref->enabled) {
            return ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $defaultOffsets = $familyPref->getOffsets();

        // Get all active documents with expiry dates
        $documents = Document::with(['category', 'family'])
            ->where('family_id', $family->id)
            ->active()
            ->whereNotNull('expiry_date')
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        // Active members in the family
        $members = $family->users()->get();

        foreach ($documents as $doc) {
            $expiry = Carbon::parse($doc->expiry_date, $timezone)->startOfDay();
            $diffDays = (int) $today->diffInDays($expiry, false);

            // Check if diffDays matches any of the configured offsets
            foreach ($defaultOffsets as $offset) {
                if ($diffDays === (int) $offset) {
                    foreach ($members as $user) {
                        // Check if user has an override preference
                        $userPref = ReminderPreference::where('family_id', $family->id)
                            ->where('user_id', $user->id)
                            ->first();

                        if ($userPref && !$userPref->enabled) {
                            $skippedCount++;
                            continue;
                        }

                        $idempotencyKey = hash(
                            'sha256',
                            "{$doc->id}_{$user->id}_{$doc->expiry_date->toDateString()}_{$offset}_expiry"
                        );

                        // Find or create delivery
                        $delivery = ReminderDelivery::firstOrCreate(
                            ['idempotency_key' => $idempotencyKey],
                            [
                                'document_id' => $doc->id,
                                'recipient_user_id' => $user->id,
                                'due_date_snapshot' => $doc->expiry_date,
                                'offset_days' => $offset,
                                'scheduled_for' => now(),
                                'status' => 'pending',
                            ]
                        );

                        // If already sent or cancelled, skip
                        if ($delivery->status === 'sent') {
                            $skippedCount++;
                            continue;
                        }

                        if ($delivery->status === 'cancelled') {
                            $skippedCount++;
                            continue;
                        }

                        // Send the email
                        try {
                            Mail::to($user->email)->send(
                                new DocumentExpiryReminderMail($doc, $user, $offset)
                            );

                            $delivery->update([
                                'status' => 'sent',
                                'sent_at' => now(),
                                'failure_reason' => null,
                            ]);

                            $sentCount++;
                        } catch (Throwable $e) {
                            Log::error("Failed to deliver reminder for doc #{$doc->id} to {$user->email}: " . $e->getMessage());

                            $delivery->update([
                                'status' => 'failed',
                                'failure_reason' => $e->getMessage(),
                            ]);

                            $failedCount++;
                        }
                    }
                }
            }
        }

        return [
            'sent' => $sentCount,
            'failed' => $failedCount,
            'skipped' => $skippedCount,
        ];
    }
}
