@extends('layouts.app')

@section('title', 'Edit Document: ' . $document->title)

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}" class="text-decoration-none">Documents</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('documents.show', $document) }}" class="text-decoration-none">{{ $document->title }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>

            <div class="card bg-white shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Edit Document Record</h4>
                        <p class="text-secondary small mb-0">Update metadata, dates, or add attachments for {{ $document->title }}</p>
                    </div>
                    <span class="badge bg-light text-dark border">
                        ID: #{{ $document->id }}
                    </span>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Row 1: Title & Category -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label for="title" class="form-label fw-semibold">
                                    Document Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $document->title) }}" required maxlength="120">
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label for="category_id" class="form-label fw-semibold">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $document->category_id) == $category->id ? 'selected' : '' }}>
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
                                <input type="text" id="issuer" name="issuer" class="form-control @error('issuer') is-invalid @enderror" value="{{ old('issuer', $document->issuer) }}" maxlength="120">
                                @error('issuer')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="reference_number" class="form-label fw-semibold">
                                    Reference / Policy / Serial No.
                                </label>
                                <input type="text" id="reference_number" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $document->reference_number) }}" maxlength="100">
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
                                    <input type="date" id="expiry_date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                                    <div class="form-text text-muted small">Changing the date cancels old pending reminder schedules.</div>
                                    @error('expiry_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="issue_date" class="form-label fw-semibold">Issue Date</label>
                                    <input type="date" id="issue_date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror" value="{{ old('issue_date', $document->issue_date?->format('Y-m-d')) }}">
                                    @error('issue_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="purchase_date" class="form-label fw-semibold">Purchase Date</label>
                                    <input type="date" id="purchase_date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', $document->purchase_date?->format('Y-m-d')) }}">
                                    @error('purchase_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Responsible Member & Attachments -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="responsible_user_id" class="form-label fw-semibold">Responsible Household Member</label>
                                <select id="responsible_user_id" name="responsible_user_id" class="form-select @error('responsible_user_id') is-invalid @enderror">
                                    <option value="">Unassigned (Shared Household Responsibility)</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}" {{ old('responsible_user_id', $document->responsible_user_id) == $m->id ? 'selected' : '' }}>
                                            {{ $m->name }} ({{ $m->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsible_user_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="attachment" class="form-label fw-semibold">Attach Additional File (Scan / Receipt)</label>
                                <input type="file" id="attachment" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text text-muted small">
                                    PDF, JPG, PNG up to 10 MB.
                                </div>
                                @error('attachment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Row 5: Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Notes & Instructions</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="3000">{{ old('notes', $document->notes) }}</textarea>
                            @error('notes')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-check2 me-1"></i> Update Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
