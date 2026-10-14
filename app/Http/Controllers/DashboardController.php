<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\DocumentStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DocumentStatusService $statusService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $stats = $this->statusService->getFamilyStatistics($family);
        $timezone = $family->timezone ?? 'Asia/Kolkata';

        // Upcoming documents sorted by expiry date ascending
        $upcomingDocuments = Document::with(['category', 'responsibleUser'])
            ->where('family_id', $family->id)
            ->active()
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->take(10)
            ->get();

        // Recently created or modified records
        $recentDocuments = Document::with(['category', 'responsibleUser'])
            ->where('family_id', $family->id)
            ->active()
            ->orderBy('updated_at', 'desc')
            ->take(6)
            ->get();

        // System and family categories
        $categories = DocumentCategory::availableForFamily($family->id)->get();

        // Recent audit events for transparency
        $recentActivities = AuditEvent::with('actor')
            ->where('family_id', $family->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Family reminder preferences
        $reminderPref = $family->defaultReminderPreference();

        return view('dashboard.index', compact(
            'family',
            'stats',
            'upcomingDocuments',
            'recentDocuments',
            'categories',
            'recentActivities',
            'reminderPref'
        ));
    }
}

