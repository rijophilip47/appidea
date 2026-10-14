@extends('layouts.app')

@section('title', 'Dashboard - ' . $family->name)

@section('content')
<div class="container-fluid px-lg-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <span>{{ $family->name }}</span>
                <span class="badge bg-secondary-subtle text-secondary fs-6 fw-normal">
                    <i class="bi bi-clock me-1"></i>{{ $family->timezone ?? 'Asia/Kolkata' }}
                </span>
            </h2>
            <p class="text-secondary mb-0">
                Household document overview and upcoming renewal deadlines
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('documents.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add Document</span>
            </a>
            <a href="{{ route('settings.reminders') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-bell"></i>
                <span>Reminders</span>
            </a>
        </div>
    </div>

    <!-- Reminder Status Alert Banner -->
    @if($reminderPref->enabled)
        <div class="alert alert-info py-2 px-3 d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-info fs-5"></i>
                <span class="small">
                    <strong>Email reminders are active</strong>: alerts are scheduled at {{ implode(', ', $reminderPref->getOffsets()) }} days before expiry.
                </span>
            </div>
            <a href="{{ route('settings.reminders') }}" class="small text-decoration-none fw-semibold">Configure</a>
        </div>
    @else
        <div class="alert alert-warning py-2 px-3 d-flex align-items-center justify-content-between mb-4 border-0 shadow-sm rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                <span class="small">
                    <strong>Email reminders are currently paused</strong> for this household workspace.
                </span>
            </div>
            <a href="{{ route('settings.reminders') }}" class="btn btn-sm btn-warning text-dark py-0 fw-semibold">Turn On</a>
        </div>
    @endif

    <!-- 4 Summary KPI Cards (FR-15) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-semibold text-uppercase">Total Active</span>
                            <h3 class="fw-bold my-1 text-primary">{{ $stats['total_active'] }}</h3>
                            <a href="{{ route('documents.index') }}" class="text-decoration-none small text-muted">
                                View all records <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                            <i class="bi bi-files fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-semibold text-uppercase">Due Soon (&le;30d)</span>
                            <h3 class="fw-bold my-1 text-warning">{{ $stats['due_soon'] }}</h3>
                            <a href="{{ route('documents.index', ['status' => 'due_soon']) }}" class="text-decoration-none small text-warning fw-semibold">
                                Review items <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-semibold text-uppercase">Overdue / Expired</span>
                            <h3 class="fw-bold my-1 text-danger">{{ $stats['overdue'] }}</h3>
                            <a href="{{ route('documents.index', ['status' => 'expired']) }}" class="text-decoration-none small text-danger fw-semibold">
                                Action needed <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="bg-danger-subtle text-danger p-3 rounded-circle">
                            <i class="bi bi-exclamation-octagon fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary small fw-semibold text-uppercase">Expiring This Month</span>
                            <h3 class="fw-bold my-1 text-info">{{ $stats['expiring_this_month'] }}</h3>
                            <span class="small text-muted">{{ now()->format('F Y') }}</span>
                        </div>
                        <div class="bg-info-subtle text-info p-3 rounded-circle">
                            <i class="bi bi-calendar-event fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Templates (Section 6.1) -->
    <div class="card bg-white mb-4">
        <div class="card-body p-3 p-md-4">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill text-warning"></i>
                <span>Quick-Add Common Household Documents</span>
            </h6>
            <div class="row g-2">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'vehicle_insurance']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-car-front-fill text-primary fs-5"></i>
                        <span class="fw-semibold small">Car/Bike Insurance</span>
                        <span class="text-muted" style="font-size: 0.75rem;">Annual renewal</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'puc']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-wind text-success fs-5"></i>
                        <span class="fw-semibold small">PUC Certificate</span>
                        <span class="text-muted" style="font-size: 0.75rem;">6/12 Month check</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'appliance_warranty']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-tv-fill text-warning fs-5"></i>
                        <span class="fw-semibold small">Appliance Warranty</span>
                        <span class="text-muted" style="font-size: 0.75rem;">TV, Fridge, Phone</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'passport']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-passport-fill text-info fs-5"></i>
                        <span class="fw-semibold small">Passport</span>
                        <span class="text-muted" style="font-size: 0.75rem;">10-Year travel</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'health_insurance']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-shield-shaded text-danger fs-5"></i>
                        <span class="fw-semibold small">Health Insurance</span>
                        <span class="text-muted" style="font-size: 0.75rem;">Mediclaim policy</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('documents.create', ['template' => 'property_lease']) }}" class="btn btn-outline-light text-dark border w-100 p-2 text-start d-flex flex-column gap-1 h-100 rounded-3">
                        <i class="bi bi-house-door-fill text-secondary fs-5"></i>
                        <span class="fw-semibold small">House Lease</span>
                        <span class="text-muted" style="font-size: 0.75rem;">11-Month agreement</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upcoming Deadlines List (Section 6.2) -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-primary"></i>
                        <span>Upcoming Renewal Deadlines</span>
                    </h5>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">
                        View All Records
                    </a>
                </div>

                <div class="card-body p-0">
                    @if($upcomingDocuments->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="bg-light d-inline-flex p-3 rounded-circle text-muted mb-3">
                                <i class="bi bi-folder-plus fs-1"></i>
                            </div>
                            <h5>No upcoming document deadlines recorded yet</h5>
                            <p class="text-secondary small mb-3">
                                Start by adding your household's vehicle insurance, warranty cards, or policies.
                            </p>
                            <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Add First Document
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-secondary">
                                    <tr>
                                        <th>Document</th>
                                        <th>Category</th>
                                        <th>Expiry Date</th>
                                        <th>Status</th>
                                        <th>Responsible</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingDocuments as $doc)
                                        @php
                                            $status = $doc->computed_status;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('documents.show', $doc) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                                    {{ $doc->title }}
                                                </a>
                                                @if($doc->issuer)
                                                    <span class="text-muted small">{{ $doc->issuer }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi {{ $doc->category->icon ?? 'bi-file-earmark' }} me-1" style="color: {{ $doc->category->color ?? '#6c757d' }}"></i>
                                                    {{ $doc->category->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-nowrap">
                                                    {{ $doc->expiry_date ? $doc->expiry_date->format('d M Y') : 'None' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $status['badge'] }} badge-status text-nowrap">
                                                    <i class="bi {{ $status['icon'] }} me-1"></i>
                                                    {{ $status['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-secondary">
                                                    {{ $doc->responsibleUser ? $doc->responsibleUser->name : 'Unassigned' }}
                                                </span>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-light border" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side: Categories & Recent Activities -->
        <div class="col-lg-4">
            <!-- Categories Card -->
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-tags-fill text-primary"></i>
                        <span>Document Categories</span>
                    </h6>
                </div>
                <div class="list-group list-group-flush small">
                    @foreach($categories as $category)
                        <a href="{{ route('documents.index', ['category_id' => $category->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $category->icon ?? 'bi-folder' }}" style="color: {{ $category->color ?? '#0d6efd' }}"></i>
                                <span>{{ $category->name }}</span>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                {{ $category->documents()->where('family_id', $family->id)->active()->count() }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity Card (FR-18) -->
            <div class="card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-activity text-primary"></i>
                        <span>Recent Activity</span>
                    </h6>
                    <a href="{{ route('settings.security') }}" class="small text-decoration-none">Audit Log</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($recentActivities as $event)
                            <li class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold text-dark">{{ ucwords(str_replace('.', ' ', $event->event_type)) }}</span>
                                    <span class="text-muted" style="font-size: 0.725rem;">{{ $event->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-secondary" style="font-size: 0.775rem;">
                                    by {{ $event->actor ? $event->actor->name : 'System' }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-3">No recent events recorded.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
