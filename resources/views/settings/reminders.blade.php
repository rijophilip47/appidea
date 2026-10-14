@extends('layouts.app')

@section('title', 'Reminder Settings - ' . $family->name)

@section('content')
<div class="container py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reminder Preferences</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill text-warning"></i>
                <span>Reminder & Notification Settings</span>
            </h2>
            <p class="text-secondary mb-0">Configure automated email reminders before documents, warranties, and policies expire</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Section 1: Family Workspace Defaults (FR-08) -->
        <div class="col-lg-7">
            <div class="card bg-white border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-house-gear text-primary"></i>
                        <span>Household Reminder Defaults</span>
                    </h5>
                    @if($isManager)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Managed by Admin</span>
                    @else
                        <span class="badge bg-light text-secondary border">View Only</span>
                    @endif
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.reminders.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="target" value="family">

                        <!-- Enabled Toggle -->
                        <div class="form-check form-switch mb-4 p-3 bg-light rounded-3">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="family_enabled" name="enabled" value="1" {{ $familyPref->enabled ? 'checked' : '' }} {{ !$isManager ? 'disabled' : '' }}>
                            <label class="form-check-label fw-bold" for="family_enabled">
                                Enable Automated Email Reminders for this Household
                            </label>
                            <div class="form-text text-muted small ms-5">
                                When enabled, scheduled emails will notify members prior to expiry dates.
                            </div>
                        </div>

                        <!-- Offsets Presets -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Reminder Intervals (Offsets before expiry)
                            </label>
                            <div class="row g-2">
                                @php
                                    $currentOffsets = $familyPref->getOffsets();
                                    $availableOffsets = [
                                        60 => '60 Days Before',
                                        30 => '30 Days Before (1 Month)',
                                        15 => '15 Days Before',
                                        7 => '7 Days Before (1 Week)',
                                        3 => '3 Days Before',
                                        1 => '1 Day Before (Eve of expiry)',
                                        0 => 'On the Expiry Day',
                                    ];
                                @endphp

                                @foreach($availableOffsets as $days => $label)
                                    <div class="col-sm-6">
                                        <div class="form-check p-2 border rounded-3 bg-light-subtle">
                                            <input class="form-check-input ms-1" type="checkbox" name="offsets[]" value="{{ $days }}" id="offset_{{ $days }}" {{ in_array($days, $currentOffsets, true) ? 'checked' : '' }} {{ !$isManager ? 'disabled' : '' }}>
                                            <label class="form-check-label small fw-semibold ms-2" for="offset_{{ $days }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text text-muted small mt-2">
                                Deduplication keys protect each document from duplicate deliveries for the same offset.
                            </div>
                        </div>

                        <!-- Preferred Send Time & Timezone -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="send_time" class="form-label fw-semibold">Preferred Dispatch Time</label>
                                <select id="send_time" name="send_time" class="form-select" {{ !$isManager ? 'disabled' : '' }}>
                                    <option value="08:00" {{ $familyPref->send_time === '08:00' ? 'selected' : '' }}>08:00 AM</option>
                                    <option value="09:00" {{ $familyPref->send_time === '09:00' ? 'selected' : '' }}>09:00 AM (Recommended)</option>
                                    <option value="10:00" {{ $familyPref->send_time === '10:00' ? 'selected' : '' }}>10:00 AM</option>
                                    <option value="18:00" {{ $familyPref->send_time === '18:00' ? 'selected' : '' }}>06:00 PM</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="timezone" class="form-label fw-semibold">Family Time Zone</label>
                                <select id="timezone" name="timezone" class="form-select" {{ !$isManager ? 'disabled' : '' }}>
                                    @foreach($availableTimezones as $tzKey => $tzLabel)
                                        <option value="{{ $tzKey }}" {{ ($familyPref->timezone ?? 'Asia/Kolkata') === $tzKey ? 'selected' : '' }}>
                                            {{ $tzLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($isManager)
                            <div class="d-flex justify-content-end pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i> Save Household Defaults
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Section 2: Personal Member Override (FR-20) -->
        <div class="col-lg-5">
            <div class="card bg-white border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-person-gear text-primary"></i>
                        <span>Your Personal Notification Override</span>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-secondary small mb-3">
                        Want to silence or customize reminder emails just for your email address (<strong>{{ auth()->user()->email }}</strong>)?
                    </p>

                    <form method="POST" action="{{ route('settings.reminders.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="target" value="user">
                        <input type="hidden" name="send_time" value="{{ $userPref ? $userPref->send_time : $familyPref->send_time }}">
                        <input type="hidden" name="timezone" value="{{ $userPref ? $userPref->timezone : $familyPref->timezone }}">

                        @php
                            $userOffsets = $userPref ? $userPref->getOffsets() : $familyPref->getOffsets();
                            $userEnabled = $userPref ? $userPref->enabled : true;
                        @endphp

                        <!-- Personal Enabled Toggle -->
                        <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="user_enabled" name="enabled" value="1" {{ $userEnabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="user_enabled">
                                Receive Reminder Emails
                            </label>
                            <div class="form-text text-muted small ms-5">
                                Turn off to stop receiving automated reminders without affecting other household members.
                            </div>
                        </div>

                        <!-- Personal Offsets -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Your Chosen Offsets</label>
                            <div class="d-flex flex-column gap-2">
                                @foreach([30 => '30 Days Before', 7 => '7 Days Before', 1 => '1 Day Before', 0 => 'Day of Expiry'] as $d => $l)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="offsets[]" value="{{ $d }}" id="u_offset_{{ $d }}" {{ in_array($d, $userOffsets, true) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="u_offset_{{ $d }}">
                                            {{ $l }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" class="btn btn-outline-primary btn-sm px-3">
                                Save My Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Privacy Note Card -->
            <div class="card bg-light border-0 shadow-sm p-3">
                <div class="d-flex gap-2">
                    <i class="bi bi-shield-check text-success fs-4"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Email Privacy Guarantee</h6>
                        <p class="text-secondary small mb-0">
                            Reminder emails contain zero account numbers, sensitive IDs, or document scans. They only remind you of the title and due date so you can log in securely.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
