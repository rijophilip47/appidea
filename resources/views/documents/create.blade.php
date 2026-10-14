@extends('layouts.app')

@section('title', 'Add New Document - ' . $currentFamily->name)

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Documents</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Record</li>
                </ol>
            </nav>

            <div class="card bg-white shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Add Document Record</h4>
                        <p class="text-secondary small mb-0">Record document dates, warranties, and household renewal deadlines</p>
                    </div>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-house-door-fill text-primary me-1"></i>{{ $currentFamily->name }}
                    </span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Row 1: Title & Category -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label for="title" class="form-label fw-semibold">
                                    Document Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $prefill['title']) }}" required maxlength="120" placeholder="e.g. Maruti Swift Comprehensive Insurance">
                                <div class="form-text text-muted small">Descriptive title. Avoid placing full bank account or identity numbers in the title.</div>
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label for="category_id" class="form-label fw-semibold">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select a Category...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $prefill['category_id']) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 2: Issuer & Reference Number -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="issuer" class="form-label fw-semibold">Issuer / Provider / Brand</label>
                                <input type="text" id="issuer" name="issuer" class="form-control @error('issuer') is-invalid @enderror" value="{{ old('issuer', $prefill['issuer']) }}" maxlength="120" placeholder="e.g. HDFC ERGO, Samsung, Govt of India">
                                @error('issuer')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="reference_number" class="form-label fw-semibold">
                                    Reference / Policy / Serial No.
                                </label>
                                <input type="text" id="reference_number" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number') }}" maxlength="100" placeholder="Optional identifier">
                                <div class="form-text text-muted small">
                                    <i class="bi bi-shield-lock me-1 text-primary"></i>Privacy tip: Only record numbers you need to look up.
                                </div>
                                @error('reference_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 3: Dates -->
                        <div class="card bg-light border-0 p-3 mb-3 rounded-3">
                            <h6 class="fw-bold mb-3 text-secondary text-uppercase" style="font-size: 0.8rem;">
                                <i class="bi bi-calendar-range me-1"></i> Critical Dates & Deadlines
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="expiry_date" class="form-label fw-semibold text-danger">
                                        Expiry / Renewal / Warranty End Date
                                    </label>
                                    <input type="date" id="expiry_date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                                    <div class="form-text text-muted small">Leave blank if the record has no expiration.</div>
                                    @error('expiry_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="issue_date" class="form-label fw-semibold">Issue Date</label>
                                    <input type="date" id="issue_date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date') }}">
                                    @error('issue_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="purchase_date" class="form-label fw-semibold">Purchase Date</label>
                                    <input type="date" id="purchase_date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date') }}">
                                    @error('purchase_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Responsible Member -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="responsible_user_id" class="form-label fw-semibold">Responsible Household Member</label>
                                <select id="responsible_user_id" name="responsible_user_id" class="form-select @error('responsible_user_id') is-invalid @enderror">
                                    <option value="">Unassigned (Shared Household Responsibility)</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" {{ old('responsible_user_id', auth()->id()) == $m->id ? 'selected' : '' }}>
                                            {{ $m->name }} ({{ $m->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">The primary person accountable for tracking and renewing this document.</div>
                                @error('responsible_user_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Row 5: Optional Attachment -->
                            <div class="col-md-6">
                                <label for="attachment" class="form-label fw-semibold">Optional Attachment (Scan / Receipt)</label>
                                <input type="file" id="attachment" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text text-muted small">
                                    Allowed: PDF, JPG, PNG up to 10 MB. Stored privately on disk.
                                </div>
                                @error('attachment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 6: Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Notes & Instructions</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="3000" placeholder="Add renewal steps, contact phone number, warranty exclusions, agent name, or notes for the household...">{{ old('notes', $prefill['notes']) }}</textarea>
                            @error('notes')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Document Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
