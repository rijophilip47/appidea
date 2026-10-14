@extends('layouts.app')

@section('title', 'Household Members - ' . $family->name)

@section('content')
<div class="container py-3">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Household Members</li>
        </ol>
    </nav>

    <!-- Header & Invite Action -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i>
                <span>Household Members</span>
            </h2>
            <p class="text-secondary mb-0">Collaborate with family members to keep documents and renewal dates up to date</p>
        </div>

        @if($isManager)
            <div>
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Invite Family Member</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Active Members Table (FR-14) -->
    <div class="card bg-white border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Active Members ({{ $members->count() }})</h5>
            <span class="badge bg-light text-secondary border">Workspace: {{ $family->name }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-secondary">
                        <tr>
                            <th>Member</th>
                            <th>Role</th>
                            <th>Joined On</th>
                            <th class="text-end">Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($m->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                {{ $m->user->name }}
                                                @if($m->user->id === auth()->id())
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle ms-1">You</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small">{{ $m->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($m->isOwner())
                                        <span class="badge bg-primary badge-status"><i class="bi bi-star-fill me-1"></i>Owner</span>
                                    @elseif($m->isAdmin())
                                        <span class="badge bg-success badge-status"><i class="bi bi-shield-lock me-1"></i>Administrator</span>
                                    @else
                                        <span class="badge bg-secondary badge-status"><i class="bi bi-person me-1"></i>Member</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small text-secondary">{{ $m->joined_at ? $m->joined_at->format('d M Y') : 'Founding member' }}</span>
                                </td>
                                <td class="text-end">
                                    @if($isManager && !$m->isOwner() && $m->user_id !== auth()->id())
                                        <div class="d-inline-flex gap-1">
                                            <!-- Change Role Form -->
                                            <form action="{{ route('family.members.updateRole', $m) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="role" value="{{ $m->role === 'admin' ? 'member' : 'admin' }}">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $m->role === 'admin' ? 'Demote to Member' : 'Promote to Admin' }}">
                                                    {{ $m->role === 'admin' ? 'Make Member' : 'Make Admin' }}
                                                </button>
                                            </form>

                                            <!-- Remove Member Form -->
                                            <form action="{{ route('family.members.destroy', $m) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove {{ $m->user->name }} from this family workspace? (Shared records will NOT be deleted).');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove member">
                                                    <i class="bi bi-person-x"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Invitations Section (Section 3.2 & FR-14) -->
    <div class="card bg-white border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-envelope-paper me-1 text-primary"></i> Pending Invitations ({{ $pendingInvitations->count() }})
            </h5>
            <span class="text-muted small">Invitations expire after 7 days</span>
        </div>
        <div class="card-body p-0">
            @if($pendingInvitations->isEmpty())
                <div class="text-center py-4 text-muted small">
                    No pending invitations. Click "Invite Family Member" above to add someone.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th>Invited Email</th>
                                <th>Role</th>
                                <th>Invited By</th>
                                <th>Expires On</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingInvitations as $invitation)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $invitation->email }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ ucfirst($invitation->role) }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-secondary">{{ $invitation->inviter ? $invitation->inviter->name : 'Admin' }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-danger">{{ $invitation->expires_at->format('d M Y, h:i A') }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($isManager)
                                            <form action="{{ route('family.invitations.revoke', $invitation) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke this invitation?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-circle me-1"></i> Revoke
                                                </button>
                                            </form>
                                        @endif
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

<!-- Invite Member Modal -->
@if($isManager)
<div class="modal fade" id="inviteModal" tabindex="-1" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('family.invitations.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="inviteModalLabel">
                        <i class="bi bi-person-plus text-primary me-1"></i> Invite to {{ $family->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Enter the invitee's email address. We'll generate a secure, single-use invitation token valid for 7 days.
                    </p>

                    <div class="mb-3">
                        <label for="invite_email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="invite_email" name="email" class="form-control" required placeholder="spouse@example.com">
                    </div>

                    <div class="mb-3">
                        <label for="invite_role" class="form-label fw-semibold">Assigned Role</label>
                        <select id="invite_role" name="role" class="form-select">
                            <option value="member" selected>Member (Can view, add, and renew records)</option>
                            <option value="admin">Administrator (Can also manage members and invitations)</option>
                        </select>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        The invitee can sign in or register to accept. Shared records remain protected within the household.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Send Invitation
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
