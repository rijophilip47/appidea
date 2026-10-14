@extends('layouts.app')

@section('title', 'Documents - ' . $currentFamily->name)

@section('content')
<div class="container-fluid px-lg-4">
    <!-- Header with Add Document & Export -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-folder2-open text-primary"></i>
                <span>Household Documents</span>
            </h2>
            <p class="text-secondary mb-0">Manage, search, and monitor renewal deadlines for {{ $currentFamily->name }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->canManageFamily($currentFamily->id))
                <form action="{{ route('exports.documents.csv') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary d-flex align-items-center gap-2" title="Export to CSV with formula injection protection">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        <span>Export CSV</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('documents.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>Add Document</span>
            </a>
        </div>
    </div>

    <!-- Status Tabs (FR-07, FR-12) -->
    <ul class="nav nav-pills mb-3 border-bottom pb-2 gap-1 flex-nowrap overflow-x-auto">
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'all' ? 'active' : '' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'all'])) }}">
                All Active
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'due_soon' ? 'active bg-warning text-dark' : 'text-warning-emphasis' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'due_soon'])) }}">
                <i class="bi bi-clock-history me-1"></i> Due Soon (&le;30d)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'expired' ? 'active bg-danger' : 'text-danger' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'expired'])) }}">
                <i class="bi bi-exclamation-octagon me-1"></i> Overdue / Expired
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'current' ? 'active bg-success' : 'text-success' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'current'])) }}">
                <i class="bi bi-check-circle me-1"></i> Valid / Current
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'no_expiry' ? 'active bg-info text-dark' : 'text-info-emphasis' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'no_expiry'])) }}">
                <i class="bi bi-infinity me-1"></i> No Expiry Date
            </a>
        </li>
        <li class="nav-item ms-auto">
            <a class="nav-link py-1 px-3 {{ $statusFilter === 'archived' ? 'active bg-secondary' : 'text-secondary' }}" href="{{ route('documents.index', array_merge(request()->query(), ['status' => 'archived'])) }}">
                <i class="bi bi-archive me-1"></i> Archives
            </a>
        </li>
    </ul>

    <!-- Filter & Search Toolbar -->
    <div class="card mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ $statusFilter }}">

                <!-- Search Input -->
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search title, provider, reference..." value="{{ request('q') }}">
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Responsible Person Filter -->
                <div class="col-6 col-md-3">
                    <select name="responsible_user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Responsible Contacts</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}" {{ request('responsible_user_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="col-12 col-md-2 d-flex gap-1">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="expiry_asc" {{ request('sort') == 'expiry_asc' ? 'selected' : '' }}>Expiry: Soonest</option>
                        <option value="expiry_desc" {{ request('sort') == 'expiry_desc' ? 'selected' : '' }}>Expiry: Furthest</option>
                        <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Title: A to Z</option>
                        <option value="recently_updated" {{ request('sort') == 'recently_updated' ? 'selected' : '' }}>Recently Updated</option>
                    </select>

                    @if(request('q') || request('category_id') || request('responsible_user_id') || request('sort'))
                        <a href="{{ route('documents.index', ['status' => $statusFilter]) }}" class="btn btn-sm btn-outline-danger" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table / Cards -->
    <div class="card bg-white">
        <div class="card-body p-0">
            @if($documents->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="bg-light d-inline-flex p-3 rounded-circle text-muted mb-3">
                        <i class="bi bi-search fs-1"></i>
                    </div>
                    <h5>No document records found</h5>
                    <p class="text-secondary small mb-3">
                        @if(request('q') || request('category_id') || request('status') !== 'all')
                            Try adjusting your filters or search terms.
                        @else
                            You haven't added any documents to this workspace yet.
                        @endif
                    </p>
                    <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add Document Record
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th style="width: 30%;">Title & Provider</th>
                                <th>Category</th>
                                <th>Expiry / Renewal Date</th>
                                <th>Status</th>
                                <th>Responsible</th>
                                <th>Attachment</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $status = $doc->computed_status;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('documents.show', $doc) }}" class="fw-bold text-decoration-none text-dark d-block">
                                            {{ $doc->title }}
                                        </a>
                                        <div class="text-muted small">
                                            @if($doc->issuer)
                                                <span>{{ $doc->issuer }}</span>
                                            @endif
                                            @if($doc->reference_number)
                                                <span class="ms-1 badge bg-light text-secondary border">Ref: {{ $doc->reference_number }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi {{ $doc->category->icon ?? 'bi-file-earmark' }} me-1" style="color: {{ $doc->category->color ?? '#6c757d' }}"></i>
                                            {{ $doc->category->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($doc->expiry_date)
                                            <div class="fw-semibold text-nowrap">
                                                {{ $doc->expiry_date->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                {{ $doc->expiry_date->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-muted small">None</span>
                                        @endif
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
                                    <td>
                                        @if($doc->attachments->isNotEmpty())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="{{ $doc->attachments->first()->original_name }}">
                                                <i class="bi bi-paperclip me-1"></i>{{ $doc->attachments->count() }} file(s)
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-outline-secondary" title="View details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('documents.edit', $doc) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($documents->hasPages())
                    <div class="card-footer bg-white py-3 border-top">
                        {{ $documents->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
