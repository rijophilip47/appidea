<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Expiry Reminder</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; padding: 24px; line-height: 1.6; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { margin-bottom: 24px; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 13px; font-weight: 600; border-radius: 9999px; background-color: #fef3c7; color: #92400e; margin-bottom: 12px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 20px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; }
        .details-box { background-color: #f1f5f9; border-radius: 6px; padding: 16px; margin: 20px 0; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .label { font-weight: 600; color: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <span class="badge">Expiry Reminder</span>
            <h2 style="margin: 0 0 8px 0; color: #0f172a;">Hello {{ $recipient->name }},</h2>
            <p style="margin: 0; color: #475569;">
                Your family record "<strong>{{ $document->title }}</strong>" in <strong>{{ $document->family->name }}</strong> is scheduled to expire soon.
            </p>
        </div>

        <div class="details-box">
            <div style="margin-bottom: 8px;">
                <span class="label">Document:</span> {{ $document->title }}
            </div>
            <div style="margin-bottom: 8px;">
                <span class="label">Category:</span> {{ $document->category->name }}
            </div>
            @if($document->issuer)
            <div style="margin-bottom: 8px;">
                <span class="label">Issuer / Provider:</span> {{ $document->issuer }}
            </div>
            @endif
            <div>
                <span class="label">Expiry Date:</span> <strong>{{ $document->expiry_date->format('d M Y') }}</strong> 
                ({{ $offsetDays === 0 ? 'Today' : ($offsetDays === 1 ? 'Tomorrow' : 'in ' . $offsetDays . ' days') }})
            </div>
        </div>

        <p>
            Please sign in to your dashboard to review this record and mark it as renewed once you complete the renewal.
        </p>

        <div>
            <a href="{{ route('documents.show', $document) }}" class="btn">Sign In & View Record</a>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px 0;">
                <em>Privacy Note:</em> Sensitive identifiers, account numbers, and attachments are never included in email reminders to protect your household privacy.
            </p>
            <p style="margin: 0;">
                You received this because reminders are enabled for your family workspace. You can manage notification preferences in your family settings.
            </p>
        </div>
    </div>
</body>
</html>

