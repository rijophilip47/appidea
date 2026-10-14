<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Family Workspace Invitation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 24px; line-height: 1.6; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .btn { display: inline-block; background-color: #0d6efd; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin: 0 0 16px 0; color: #0f172a;">You've been invited!</h2>
        <p>
            <strong>{{ $invitation->inviter->name }}</strong> has invited you to join their family workspace: 
            <strong>{{ $invitation->family->name }}</strong> on Family Document Expiry Tracker.
        </p>
        <p>
            Role assigned: <strong>{{ ucfirst($invitation->role) }}</strong>
        </p>
        <p>
            As a family member, you will be able to collaborate on household documents, track warranties, and receive timely expiry alerts.
        </p>

        <div>
            <a href="{{ route('invitations.show', ['token' => $plainToken]) }}" class="btn">Accept Invitation</a>
        </div>

        <div class="footer">
            <p>This invitation will expire on {{ $invitation->expires_at->format('d M Y, h:i A') }}. If you were not expecting this invitation, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>

