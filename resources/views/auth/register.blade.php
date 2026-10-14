@extends('layouts.app')

@section('title', 'Create Account - Family Document Expiry Tracker')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card p-4 p-md-5 border-0 shadow-sm bg-white">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary p-3 rounded-circle mb-3">
                        <i class="bi bi-house-door-fill fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Create Household Workspace</h3>
                    <p class="text-secondary small">Start tracking expiry dates, warranties, and shared reminders</p>
                </div>

                @if(!empty($invitationToken))
                    <div class="alert alert-info py-2 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Registering with your invitation token will automatically connect you to the inviting family.</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    @if(!empty($invitationToken))
                        <input type="hidden" name="invitation_token" value="{{ $invitationToken }}">
                    @endif

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold">Your Full Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Philip Matthew">
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="philip@example.com">
                            <div class="form-text text-muted small">Reminders will be delivered to this verified email address.</div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="family_name" class="form-label fw-semibold">Family Workspace Name</label>
                            <input id="family_name" type="text" class="form-control @error('family_name') is-invalid @enderror" name="family_name" value="{{ old('family_name') }}" placeholder="e.g. Philip Household">
                            <div class="form-text text-muted small">Optional. Defaults to "[Your Name]'s Household".</div>
                            @error('family_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="timezone" class="form-label fw-semibold">Time Zone</label>
                            <select id="timezone" name="timezone" class="form-select @error('timezone') is-invalid @enderror">
                                <option value="Asia/Kolkata" {{ old('timezone', 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' }}>India Standard Time (IST - Asia/Kolkata)</option>
                                <option value="Asia/Dubai" {{ old('timezone') === 'Asia/Dubai' ? 'selected' : '' }}>Gulf Standard Time (Dubai)</option>
                                <option value="Asia/Singapore" {{ old('timezone') === 'Asia/Singapore' ? 'selected' : '' }}>Singapore Time</option>
                                <option value="Europe/London" {{ old('timezone') === 'Europe/London' ? 'selected' : '' }}>London / GMT</option>
                                <option value="America/New_York" {{ old('timezone') === 'America/New_York' ? 'selected' : '' }}>Eastern Time (New York)</option>
                                <option value="UTC" {{ old('timezone') === 'UTC' ? 'selected' : '' }}>UTC</option>
                            </select>
                            <div class="form-text text-muted small">Daily reminder emails are dispatched according to your local time zone.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required placeholder="Minimum 8 characters">
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required placeholder="Re-type password">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            Create Workspace & Sign In <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <span class="text-secondary small">Already have an account?</span>
                    <a href="{{ route('login') }}" class="text-decoration-none small fw-semibold ms-1">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

