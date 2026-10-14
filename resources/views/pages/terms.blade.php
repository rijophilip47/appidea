@extends('layouts.app')

@section('title', 'Terms of Use - Family Document Expiry Tracker')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card p-4 p-md-5 bg-white border-0 shadow-sm">
                <h1 class="fw-bold mb-4">Terms of Use</h1>
                <p class="text-muted">Effective Date: 14 October 2026</p>

                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                    <div>
                        <strong>Important Disclaimer:</strong> Family Document Expiry Tracker is a reminder tool for households. It does not renew policies, certificates, or licences, and does not guarantee that third parties or authorities will accept your documents.
                    </div>
                </div>

                <h4 class="mt-4 fw-bold">1. Purpose and Honest Status</h4>
                <p class="text-secondary">
                    The service allows household members to log document expiry dates and receive automated reminders. You remain solely responsible for renewing policies, paying premiums, inspecting equipment, and complying with statutory deadlines.
                </p>

                <h4 class="mt-4 fw-bold">2. User Accounts and Family Workspaces</h4>
                <p class="text-secondary">
                    You must provide accurate information when registering. You are responsible for safeguarding your login credentials. Family owners and administrators are responsible for managing access to their shared workspace and inviting only trusted members.
                </p>

                <h4 class="mt-4 fw-bold">3. Acceptable Use and Content</h4>
                <p class="text-secondary">
                    You agree not to upload unlawful material, malware, or unauthorized copies of third-party documents. Only upload documents belonging to your household.
                </p>

                <h4 class="mt-4 fw-bold">4. Limitation of Liability</h4>
                <p class="text-secondary">
                    While we strive for high email delivery rates and reminder reliability, we are not liable for missed deadlines, expired insurance, penalties, or damages arising from network interruptions or third-party email provider outages. Always keep independent track of critical legal obligations.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
