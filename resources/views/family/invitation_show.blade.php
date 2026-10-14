@extends('layouts.app')

@section('title', 'Join ' . $invitation->family->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card p-4 p-md-5 border-0 shadow-sm bg-white text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary p-3 rounded-circle mx-auto mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-envelope-open-fill fs-2"></i>
                </div>

                <h3 class="fw-bold mb-2">You're Invited!</h3>
                <p class="text-secondary mb-4">
                    <strong>{{ $invitation->inviter ? $invitation->inviter->name : 'A family member' }}</strong> has invited you to join the 
                    <span class="text-primary fw-bold">{{ $invitation->family->name }}</span> workspace on Family Document Expiry Tracker.
                </p>

                <div class="p-3 bg-light rounded-3 text-start mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Invited Email:</span>
                        <strong>{{ $invitation->email }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Assigned Role:</span>
                        <span class="badge bg-primary">{{ ucfirst($invitation->role) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary small">Invitation Valid Until:</span>
                        <span class="text-danger small">{{ $invitation->expires_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>

                @auth
                    <form action="{{ route('invitations.accept', ['token' => $plainToken]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Accept Invitation & Join Workspace
                        </button>
                    </form>
                @else
                    <div class="d-grid gap-2">
                        <a href="{{ route('register', ['invitation' => $plainToken]) }}" class="btn btn-primary py-2 fw-semibold">
                            Create an Account to Accept
                        </a>
                        <a href="{{ route('login', ['invitation' => $plainToken]) }}" class="btn btn-outline-secondary py-2">
                            Already have an account? Sign In
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

