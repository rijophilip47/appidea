<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentAttachment;
use App\Models\DocumentCategory;
use App\Services\ReminderScheduleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        protected ReminderScheduleService $reminderService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();
        $timezone = $family->timezone ?? 'Asia/Kolkata';
        $today = Carbon::now($timezone)->startOfDay();

        $query = Document::with(['category', 'responsibleUser', 'attachments'])
            ->where('family_id', $family->id);

        // Filter: Tab / Status
        $statusFilter = $request->query('status', 'all');

        if ($statusFilter === 'archived') {
            $query->archived();
        } else {
            $query->active();

            if ($statusFilter === 'due_soon') {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $today->toDateString())
                    ->whereDate('expiry_date', '<=', $today->copy()->addDays(30)->toDateString());
            } elseif ($statusFilter === 'expired') {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', $today->toDateString());
            } elseif ($statusFilter === 'current') {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>', $today->copy()->addDays(30)->toDateString());
            } elseif ($statusFilter === 'no_expiry') {
                $query->whereNull('expiry_date');
            }
        }

        // Filter: Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter: Responsible Member
        if ($request->filled('responsible_user_id')) {
            $query->where('responsible_user_id', $request->query('responsible_user_id'));
        }

        // Filter: Search term
        if ($request->filled('q')) {
            $searchTerm = '%' . trim($request->query('q')) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('issuer', 'like', $searchTerm)
                  ->orWhere('reference_number', 'like', $searchTerm)
                  ->orWhere('notes', 'like', $searchTerm);
            });
        }

        // Sorting
        $sort = $request->query('sort', 'expiry_asc');
        switch ($sort) {
            case 'expiry_desc':
                $query->orderByRaw('expiry_date IS NULL, expiry_date DESC');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'recently_updated':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'expiry_asc':
            default:
                $query->orderByRaw('expiry_date IS NULL, expiry_date ASC');
                break;
        }

        $documents = $query->paginate(15)->withQueryString();

        $categories = DocumentCategory::availableForFamily($family->id)->get();
        $members = $family->users()->get();

        return view('documents.index', compact(
            'documents',
            'categories',
            'members',
            'statusFilter',
            'sort'
        ));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $categories = DocumentCategory::availableForFamily($family->id)->get();
        $members = $family->users()->get();

        // Handle quick-add templates (Section 6.1 & 6.2)
        $template = $request->query('template');
        $prefill = [
            'title' => '',
            'category_id' => '',
            'issuer' => '',
            'notes' => '',
        ];

        if ($template === 'vehicle_insurance') {
            $prefill['title'] = 'Comprehensive Vehicle Insurance';
            $cat = $categories->firstWhere('name', 'Vehicle');
            $prefill['category_id'] = $cat?->id;
            $prefill['notes'] = 'Vehicle registration number: ';
        } elseif ($template === 'puc') {
            $prefill['title'] = 'Pollution Under Control (PUC) Certificate';
            $cat = $categories->firstWhere('name', 'Vehicle');
            $prefill['category_id'] = $cat?->id;
        } elseif ($template === 'appliance_warranty') {
            $prefill['title'] = 'Appliance Extended Warranty';
            $cat = $categories->firstWhere('name', 'Appliance Warranty');
            $prefill['category_id'] = $cat?->id;
            $prefill['notes'] = 'Brand / Model: ' . "\n" . 'Serial number: ';
        } elseif ($template === 'passport') {
            $prefill['title'] = 'Passport Renewal';
            $cat = $categories->firstWhere('name', 'Identity & Travel');
            $prefill['category_id'] = $cat?->id;
        } elseif ($template === 'driving_licence') {
            $prefill['title'] = 'Driving Licence';
            $cat = $categories->firstWhere('name', 'Identity & Travel');
            $prefill['category_id'] = $cat?->id;
        } elseif ($template === 'health_insurance') {
            $prefill['title'] = 'Family Health Insurance Policy';
            $cat = $categories->firstWhere('name', 'Insurance');
            $prefill['category_id'] = $cat?->id;
        } elseif ($template === 'property_lease') {
            $prefill['title'] = 'House Lease Agreement';
            $cat = $categories->firstWhere('name', 'Property & Housing');
            $prefill['category_id'] = $cat?->id;
        }

        return view('documents.create', compact('categories', 'members', 'prefill', 'template'));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $family = $user->currentFamily();

        $validated = $request->validated();

        // Verify responsible person belongs to family if given
        if (!empty($validated['responsible_user_id'])) {
            $belongs = $family->users()->where('users.id', $validated['responsible_user_id'])->exists();
            if (!$belongs) {
                $validated['responsible_user_id'] = null;
            }
        }

        $document = DB::transaction(function () use ($validated, $family, $user, $request) {
            $doc = Document::create([
                'family_id' => $family->id,
                'category_id' => $validated['category_id'],
                'created_by' => $user->id,
                'responsible_user_id' => $validated['responsible_user_id'] ?? null,
                'title' => $validated['title'],
                'issuer' => $validated['issuer'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Handle optional file attachment safely (FR-16)
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
                $sizeBytes = $file->getSize();

                // Generate safe random path inside private directory
                $storedPath = $file->store("private/attachments/{$family->id}/{$doc->id}", 'local');

                DocumentAttachment::create([
                    'document_id' => $doc->id,
                    'disk' => 'local',
                    'storage_path' => $storedPath,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size_bytes' => $sizeBytes,
                    'uploaded_by' => $user->id,
                ]);
            }

            AuditEvent::log(
                $family->id,
                'document.created',
                'Document',
                $doc->id,
                ['title' => $doc->title, 'expiry_date' => $doc->expiry_date?->toDateString()]
            );

            return $doc;
        });

        return redirect()->route('documents.show', $document)
            ->with('success', "Document record '{$document->title}' was added successfully!");
    }

    public function show(Document $document): View
    {
        $this->authorize('view', $document);

        $document->load(['category', 'creator', 'responsibleUser', 'attachments.uploader', 'reminderDeliveries.recipient']);
        $family = $document->family;

        $auditEvents = AuditEvent::with('actor')
            ->where('family_id', $family->id)
            ->where('entity_type', 'Document')
            ->where('entity_id', $document->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('documents.show', compact('document', 'family', 'auditEvents'));
    }

    public function edit(Document $document): View
    {
        $this->authorize('update', $document);

        $family = $document->family;
        $categories = DocumentCategory::availableForFamily($family->id)->get();
        $members = $family->users()->get();

        return view('documents.edit', compact('document', 'categories', 'members'));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $user = auth()->user();
        $family = $document->family;
        $validated = $request->validated();

        $oldExpiry = $document->expiry_date ? $document->expiry_date->toDateString() : null;
        $newExpiry = !empty($validated['expiry_date']) ? Carbon::parse($validated['expiry_date'])->toDateString() : null;

        // Verify responsible person belongs to family
        if (!empty($validated['responsible_user_id'])) {
            $belongs = $family->users()->where('users.id', $validated['responsible_user_id'])->exists();
            if (!$belongs) {
                $validated['responsible_user_id'] = null;
            }
        }

        DB::transaction(function () use ($document, $validated, $oldExpiry, $newExpiry, $request, $family, $user) {
            $document->update([
                'category_id' => $validated['category_id'],
                'responsible_user_id' => $validated['responsible_user_id'] ?? null,
                'title' => $validated['title'],
                'issuer' => $validated['issuer'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // If expiry date changed, invalidate obsolete pending reminders
            if ($oldExpiry !== $newExpiry) {
                $this->reminderService->invalidateObsoleteReminders($document);
            }

            // Handle optional new file attachment
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $storedPath = $file->store("private/attachments/{$family->id}/{$document->id}", 'local');

                DocumentAttachment::create([
                    'document_id' => $document->id,
                    'disk' => 'local',
                    'storage_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'uploaded_by' => $user->id,
                ]);
            }

            AuditEvent::log(
                $family->id,
                'document.updated',
                'Document',
                $document->id,
                [
                    'title' => $document->title,
                    'expiry_changed' => ($oldExpiry !== $newExpiry),
                    'new_expiry' => $newExpiry,
                ]
            );
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully!');
    }

    public function renew(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('renew', $document);

        $validated = $request->validate([
            'new_expiry_date' => ['required', 'date'],
            'renewal_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldExpiry = $document->expiry_date ? $document->expiry_date->format('d M Y') : 'None';
        $newExpiryFormatted = Carbon::parse($validated['new_expiry_date'])->format('d M Y');

        DB::transaction(function () use ($document, $validated, $oldExpiry, $newExpiryFormatted) {
            $notesAddition = "\n\n[" . now()->format('Y-m-d') . " Renewed]: Previous expiry was " . $oldExpiry;
            if (!empty($validated['renewal_notes'])) {
                $notesAddition .= " - " . trim($validated['renewal_notes']);
            }

            $document->update([
                'expiry_date' => $validated['new_expiry_date'],
                'notes' => ($document->notes ? $document->notes : '') . $notesAddition,
                'archived_at' => null, // ensure active
            ]);

            // Invalidate obsolete reminders for the old date
            $this->reminderService->invalidateObsoleteReminders($document);

            AuditEvent::log(
                $document->family_id,
                'document.renewed',
                'Document',
                $document->id,
                [
                    'title' => $document->title,
                    'old_expiry' => $oldExpiry,
                    'new_expiry' => $newExpiryFormatted,
                ]
            );
        });

        return redirect()->route('documents.show', $document)
            ->with('success', "Document '{$document->title}' marked as renewed until {$newExpiryFormatted}!");
    }

    public function archive(Document $document): RedirectResponse
    {
        $this->authorize('archive', $document);

        $isArchived = $document->archived_at !== null;

        if ($isArchived) {
            $document->update(['archived_at' => null]);
            $message = "Document '{$document->title}' has been unarchived and returned to your active dashboard.";
            $eventType = 'document.unarchived';
        } else {
            $document->update(['archived_at' => now()]);
            // Cancel pending reminders for archived documents
            $this->reminderService->invalidateObsoleteReminders($document);
            $message = "Document '{$document->title}' has been moved to archives.";
            $eventType = 'document.archived';
        }

        AuditEvent::log(
            $document->family_id,
            $eventType,
            'Document',
            $document->id,
            ['title' => $document->title]
        );

        return back()->with('success', $message);
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $familyId = $document->family_id;
        $title = $document->title;

        DB::transaction(function () use ($document, $familyId, $title) {
            // Remove attachments from disk
            foreach ($document->attachments as $attachment) {
                if (Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
                    Storage::disk($attachment->disk)->delete($attachment->storage_path);
                }
            }

            $this->reminderService->invalidateObsoleteReminders($document);
            $document->delete();

            AuditEvent::log(
                $familyId,
                'document.deleted',
                'Document',
                null,
                ['title' => $title]
            );
        });

        return redirect()->route('documents.index')
            ->with('success', "Document record '{$title}' and its associated files have been deleted.");
    }
}

