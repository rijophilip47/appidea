@extends('layouts.app')

@section('title', 'Invitation Expired or Invalid')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 text-center">
            <div class="card p-5 border-0 shadow-sm bg-white">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger p-3 rounded-circle mx-auto mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-x fs-2"></i>
                </div>
                <h4 class="fw-bold mb-2">Invitation Invalid or Expired</h4>
                <p class="text-secondary small mb-4">
                    This family invitation link has expired, was revoked by the workspace administrator, or has already been used.
                </p>
                <div class="d-grid gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
