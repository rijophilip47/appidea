<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Family;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DocumentStatusService
{
    /**
     * Compute dashboard statistics for a family.
     */
    public function getFamilyStatistics(Family $family): array
    {
        $timezone = $family->timezone ?? 'Asia/Kolkata';
        $today = Carbon::now($timezone)->startOfDay();
        $endOfMonth = $today->copy()->endOfMonth();

        $activeDocuments = Document::with(['category', 'responsibleUser'])
            ->where('family_id', $family->id)
            ->active()
            ->get();

        $totalActive = $activeDocuments->count();
        $overdueCount = 0;
        $expiresTodayCount = 0;
        $dueSoonCount = 0;
        $currentCount = 0;
        $noExpiryCount = 0;
        $expiringThisMonthCount = 0;

        foreach ($activeDocuments as $doc) {
            if ($doc->expiry_date === null) {
                $noExpiryCount++;
                continue;
            }

            $expiry = Carbon::parse($doc->expiry_date, $timezone)->startOfDay();
            $diffDays = (int) $today->diffInDays($expiry, false);

            if ($diffDays < 0) {
                $overdueCount++;
            } elseif ($diffDays === 0) {
                $expiresTodayCount++;
                $dueSoonCount++;
                $expiringThisMonthCount++;
            } elseif ($diffDays <= 30) {
                $dueSoonCount++;
                if ($expiry->lessThanOrEqualTo($endOfMonth)) {
                    $expiringThisMonthCount++;
                }
            } else {
                $currentCount++;
                if ($expiry->lessThanOrEqualTo($endOfMonth)) {
                    $expiringThisMonthCount++;
                }
            }
        }

        return [
            'total_active' => $totalActive,
            'overdue' => $overdueCount,
            'expires_today' => $expiresTodayCount,
            'due_soon' => $dueSoonCount,
            'current' => $currentCount,
            'no_expiry' => $noExpiryCount,
            'expiring_this_month' => $expiringThisMonthCount,
        ];
    }
}
