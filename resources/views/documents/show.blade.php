@extends('layouts.app')

@section('title', $document->title . ' - Details')

@section('content')
<div class="container py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Documents</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $document->title }}</li>
        </ol>
    </nav>

    @php
        $status = $document->computed_status;
    @endphp

    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h2 class="fw-bold mb-0 text-dark">{{ $document->title }}</h2>
                <span class="badge bg-{{ $status['badge'] }} badge-status">
                    <i class="bi {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
                </span>
                @if($document->archived_at)
                    <span class="badge bg-secondary badge-status"><i class="bi bi-archive me-1"></i>Archived</span>
                @endif
            </div>
            <p class="text-secondary mb-0">
                Category: <strong>{{ $document->category->name }}</strong>
                @if($document->issuer) • Provider: <strong>{{ $document->issuer }}</strong> @endif
                • Family: <strong>{{ $family->name }}</strong>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Mark Renewed Button (Triggers Modal) -->
            <button type="button" class="btn btn-success d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#renewModal">
                <i class="bi bi-arrow-repeat"></i>
                <span>Mark Renewed</span>
            </button>

            <!-- Edit Button -->
            <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="bi bi-pencil"></i>
                <span>Edit</span>
            </a>

            <!-- Archive / Unarchive Form -->
            <form action="{{ route('documents.archive', $document) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary d-flex align-items-center gap-1" title="{{ $document->archived_at ? 'Unarchive' : 'Move to archive' }}">
                    <i class="bi {{ $document->archived_at ? 'bi-box-arrow-up' : 'bi-archive' }}"></i>
                    <span>{{ $document->archived_at ? 'Unarchive' : 'Archive' }}</span>
                </button>
            </form>

            <!-- Delete Button (Policy controlled) -->
            @can('delete', $document)
                <button type="button" class="btn btn-outline-danger d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash"></i>
                    <span>Delete</span>
                </button>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details Column -->
        <div class="col-lg-8">
            <!-- Card 1: Key Metadata -->
            <div class="card mb-4 bg-white border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-card-text text-primary"></i>
                        <span>Document Information</span>
                    </h5>
                    <span class="text-muted small">ID: #{{ $document->id }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Document Title</span>
                            <span class="fw-bold fs-5">{{ $document->title }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Category</span>
                            <span class="badge bg-light text-dark border fs-6 mt-1">
                                <i class="bi {{ $document->category->icon ?? 'bi-file-earmark' }} me-1" style="color: {{ $document->category->color ?? '#0d6efd' }}"></i>
                                {{ $document->category->name }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Issuer / Provider</span>
                            <span class="fw-semibold">{{ $document->issuer ?: 'Not specified' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Reference / Policy / Serial No.</span>
                            <span class="fw-semibold font-monospace">{{ $document->reference_number ?: 'Not specified' }}</span>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <!-- Dates Section -->
                        <div class="col-sm-4">
                            <span class="text-secondary small d-block">Expiry / Renewal Date</span>
                            <span class="fw-bold text-danger fs-6">
                                {{ $document->expiry_date ? $document->expiry_date->format('d M Y') : 'No Expiry Date' }}
                            </span>
                            @if($document->expiry_date)
                                <div class="text-muted small">{{ $document->expiry_date->diffForHumans() }}</div>
                            @endif
                        </div>
                        <div class="col-sm-4">
                            <span class="text-secondary small d-block">Issue Date</span>
                            <span class="fw-semibold">
                                {{ $document->issue_date ? $document->issue_date->format('d M Y') : 'Not specified' }}
                            </span>
                        </div>
                        <div class="col-sm-4">
                            <span class="text-secondary small d-block">Purchase Date</span>
                            <span class="fw-semibold">
                                {{ $document->purchase_date ? $document->purchase_date->format('d M Y') : 'Not specified' }}
                            </span>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <!-- Responsibility & Ownership -->
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Responsible Household Contact</span>
                            <span class="fw-semibold d-flex align-items-center gap-1 mt-1">
                                <i class="bi bi-person-fill text-primary"></i>
                                {{ $document->responsibleUser ? $document->responsibleUser->name : 'Unassigned (Shared)' }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-secondary small d-block">Created By</span>
                            <span class="text-muted small">
                                {{ $document->creator ? $document->creator->name : 'System' }} on {{ $document->created_at->format('d M Y, h:i A') }}
                            </span>
                        </div>

                        @if($document->notes)
                            <div class="col-12 mt-3">
                                <span class="text-secondary small d-block mb-1">Notes & Instructions</span>
                                <div class="p-3 bg-light rounded-3 font-monospace small" style="white-space: pre-wrap;">{{ $document->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Attachments Section (FR-16) -->
            <div class="card mb-4 bg-white border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-paperclip text-primary"></i>
                        <span>Attachments & Scans ({{ $document->attachments->count() }})</span>
                    </h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-lock-fill me-1"></i>Private Storage
                    </span>
                </div>
                <div class="card-body p-3">
                    @if($document->attachments->isEmpty())
                        <p class="text-secondary small mb-0 py-2">
                            No files attached to this document. (You can attach a PDF, JPG, or PNG receipt by editing the record).
                        </p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($document->attachments as $attachment)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-check fs-4 text-primary"></i>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $attachment->original_name }}</div>
                                            <div class="text-muted small">
                                                {{ $attachment->formatted_size }} • Uploaded {{ $attachment->created_at->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('documents.attachments.download', [$document, $attachment]) }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                                            <i class="bi bi-download"></i>
                                            <span>Download</span>
                                        </a>
                                        @can('update', $document)
                                            <form action="{{ route('documents.attachments.destroy', [$document, $attachment]) }}" method="POST" onsubmit="return confirm('Remove this attachment permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete attachment">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Reminders & Audit Log -->
        <div class="col-lg-4">
            <!-- Scheduled / Sent Reminder Deliveries (FR-10, Section 11) -->
            <div class="card mb-4 bg-white border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-bell-fill text-warning"></i>
                        <span>Reminder Deliveries</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($document->reminderDeliveries as $delivery)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">
                                        {{ $delivery->offset_days }} days offset
                                    </span>
                                    <span class="badge bg-{{ $delivery->status === 'sent' ? 'success' : ($delivery->status === 'failed' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($delivery->status) }}
                                    </span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.775rem;">
                                    Recipient: {{ $delivery->recipient ? $delivery->recipient->name : 'Household' }}
                                </div>
                                @if($delivery->sent_at)
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        Delivered: {{ $delivery->sent_at->format('d M Y, h:i A') }}
                                    </div>
                                @endif
                                @if($delivery->failure_reason)
                                    <div class="text-danger mt-1" style="font-size: 0.75rem;">
                                        {{ $delivery->failure_reason }}
                                    </div>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-4">
                                <i class="bi bi-bell-slash fs-4 d-block mb-1 text-secondary"></i>
                                No reminder logs yet. Reminders are dispatched daily according to configured offsets.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Audit Trail (FR-18) -->
            <div class="card bg-white border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary"></i>
                        <span>Audit Trail</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($auditEvents as $event)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="text-dark">{{ ucwords(str_replace('.', ' ', $event->event_type)) }}</strong>
                                    <span class="text-muted" style="font-size: 0.725rem;">{{ $event->created_at->format('d M, h:i A') }}</span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.775rem;">
                                    By {{ $event->actor ? $event->actor->name : 'System' }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No audit history for this record.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Renew Document Modal (FR-11) -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.renew', $document) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="renewModalLabel">
                        <i class="bi bi-arrow-repeat text-success me-1"></i> Mark Document as Renewed
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Updating the expiry date will reset pending reminder schedules and record an audit event for future reference.
                    </p>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Expiry Date</label>
                        <div class="fw-bold">{{ $document->expiry_date ? $document->expiry_date->format('d M Y') : 'None' }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_expiry_date" class="form-label fw-semibold">
                            New Expiry / End Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" id="new_expiry_date" name="new_expiry_date" class="form-control" required value="{{ $document->expiry_date ? $document->expiry_date->copy()->addYear()->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label for="renewal_notes" class="form-label fw-semibold">Renewal Notes (Optional)</label>
                        <textarea id="renewal_notes" name="renewal_notes" class="form-control" rows="2" placeholder="e.g. Paid Rs. 4,200 premium online via NetBanking. Policy # unchanged."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2 me-1"></i> Save Renewal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal (FR-13) -->
@can('delete', $document)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.destroy', $document) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Delete Document Record
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to permanently delete <strong>"{{ $document->title }}"</strong>?</p>
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> This action cannot be undone. All associated file attachments and reminder delivery history will be permanently deleted from the system.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Confirm Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
