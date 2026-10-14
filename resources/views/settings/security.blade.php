@extends('layouts.app')

@section('title', 'Security & Audit Log - ' . $family->name)

@section('content')
<div class="container py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Security & Settings</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i>
                <span>Security, Privacy & Workspace Settings</span>
            </h2>
            <p class="text-secondary mb-0">Manage password, workspace details, CSV data exports, and audit history</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Card 1: Workspace Management -->
        <div class="col-md-6">
            <div class="card bg-white border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-house-door text-primary"></i>
                        <span>Family Workspace Details</span>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.family.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="family_name" class="form-label fw-semibold">Workspace Name</label>
                            <input type="text" id="family_name" name="name" class="form-control" value="{{ old('name', $family->name) }}" required maxlength="120" {{ !auth()->user()->canManageFamily($family->id) ? 'disabled' : '' }}>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Workspace Owner</label>
                            <div class="fw-semibold">{{ $family->owner ? $family->owner->name : 'N/A' }} ({{ $family->owner ? $family->owner->email : '' }})</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Configured Timezone</label>
                            <div class="fw-semibold">{{ $family->timezone ?? 'Asia/Kolkata' }}</div>
                        </div>

                        @if(auth()->user()->canManageFamily($family->id))
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-check2 me-1"></i> Update Workspace Name
                            </button>
                        @endif
                    </form>

                    <!-- CSV Export Button (FR-17) -->
                    <div class="pt-4 mt-4 border-top">
                        <h6 class="fw-bold mb-2">Household Data Export</h6>
                        <p class="text-secondary small mb-3">
                            Download a comprehensive CSV file of all document records, dates, and metadata. Protected against formula injection triggers.
                        </p>
                        @if(auth()->user()->canManageFamily($family->id))
                            <form action="{{ route('exports.documents.csv') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                                    <span>Download CSV Export</span>
                                </button>
                            </form>
                        @else
                            <div class="text-muted small">Only workspace administrators can request full CSV exports.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Password & Authentication -->
        <div class="col-md-6">
            <div class="card bg-white border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-key text-primary"></i>
                        <span>Change Password</span>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="••••••••">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Re-type new password">
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-shield-check me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Audit Event Trail (FR-18) -->
    <div class="card bg-white border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-journal-text text-primary"></i>
                <span>Household Activity & Audit Trail</span>
            </h5>
            <span class="badge bg-light text-secondary border">Total Events: {{ $auditLogs->total() }}</span>
        </div>
        <div class="card-body p-0">
            @if($auditLogs->isEmpty())
                <div class="text-center py-4 text-muted small">No audit log entries recorded yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor</th>
                                <th>Event Type</th>
                                <th>Target Entity</th>
                                <th>Metadata</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td class="text-nowrap text-secondary">
                                        {{ $log->created_at->format('d M Y, h:i A') }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->actor ? $log->actor->name : 'System' }}</div>
                                        <div class="text-muted" style="font-size: 0.725rem;">{{ $log->actor ? $log->actor->email : '' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $log->event_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-secondary">{{ $log->entity_type }} @if($log->entity_id)#{{ $log->entity_id }}@endif</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted" style="font-size: 0.75rem;">
                                            {{ $log->minimal_metadata_json ?: '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($auditLogs->hasPages())
                    <div class="card-footer bg-white py-3 border-top">
                        {{ $auditLogs->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
