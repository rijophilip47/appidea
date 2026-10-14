@extends('layouts.app')

@section('title', 'Family Document Expiry Tracker - Never Miss an Important Renewal')

@section('content')
<div class="container py-4">
    <!-- Hero Section -->
    <div class="row align-items-center py-5">
        <div class="col-lg-7">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3 fw-semibold">
                <i class="bi bi-shield-lock-fill me-1"></i> Privacy-First Household Organizer
            </span>
            <h1 class="display-4 fw-bold text-dark lh-sm mb-3">
                Track household documents, warranties, and renewals in one trusted place.
            </h1>
            <p class="lead text-secondary mb-4">
                Vehicle insurance, PUC certificates, appliance warranties, passports, and policies—organized together for the whole family, with timely email reminders before deadlines expire.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 py-2 shadow-sm">
                    <i class="bi bi-rocket-takeoff me-2"></i>Create Family Workspace
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4 py-2">
                    Sign In
                </a>
            </div>
            <div class="d-flex align-items-center gap-4 mt-4 text-secondary small">
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> No credit card needed</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> Private family space</span>
                <span><i class="bi bi-check-circle-fill text-success me-1"></i> India timezone aware</span>
            </div>
        </div>

        <div class="col-lg-5 mt-5 mt-lg-0">
            <div class="card border-0 shadow-lg p-3 bg-white rounded-4">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pb-2">
                    <div class="fw-bold"><i class="bi bi-house-door-fill text-primary me-2"></i>Philip Household</div>
                    <span class="badge bg-warning text-dark"><i class="bi bi-bell-fill me-1"></i>3 Reminders Due</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush rounded-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary p-2 rounded-3 fs-5">
                                    <i class="bi bi-car-front-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Car Comprehensive Insurance</div>
                                    <div class="text-muted small">Vehicle • HDFC ERGO</div>
                                </div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Due in 3 days</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning p-2 rounded-3 fs-5">
                                    <i class="bi bi-tv-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Samsung Refrigerator Warranty</div>
                                    <div class="text-muted small">Appliance Warranty • 2 Years</div>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-dark">Due in 14 days</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info-subtle text-info p-2 rounded-3 fs-5">
                                    <i class="bi bi-passport-fill"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Passport (Philip)</div>
                                    <div class="text-muted small">Identity & Travel</div>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Valid</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Principles & Features -->
    <div class="row g-4 py-5 border-top">
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 bg-white shadow-sm">
                <div class="feature-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-shaded fs-4"></i>
                </div>
                <h5 class="fw-bold">Privacy by Default</h5>
                <p class="text-secondary mb-0">
                    Your records are visible solely to your authorized family members. No third-party data selling, no advertising, and attachments stay in protected private storage.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 bg-white shadow-sm">
                <div class="feature-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-bell-fill fs-4"></i>
                </div>
                <h5 class="fw-bold">Reliable Reminders</h5>
                <p class="text-secondary mb-0">
                    Configurable presets (30, 7, and 1 day offsets) with deduplication and idempotency keys to ensure you never miss a deadline or receive spam.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 p-4 border-0 bg-white shadow-sm">
                <div class="feature-icon bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <h5 class="fw-bold">Household Collaboration</h5>
                <p class="text-secondary mb-0">
                    Invite family members with roles (Owner, Administrator, Member) and assign responsible contacts so everyone knows who is taking care of renewals.
                </p>
            </div>
        </div>
    </div>

    <!-- Honest Status Disclaimer Banner -->
    <div class="card bg-light border p-4 rounded-4 my-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-primary me-2"></i>Honest Status Guarantee</h5>
                <p class="text-secondary mb-0 small">
                    This application helps you stay organized and reminds you of deadlines. It does not auto-renew policies, connect to government portals without consent, or replace legal certificates.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('register') }}" class="btn btn-primary">Start Your Family Space</a>
            </div>
        </div>
    </div>
</div>
@endsection
