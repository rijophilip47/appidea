@extends('layouts.app')

@section('title', 'Privacy Notice - Family Document Expiry Tracker')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card p-4 p-md-5 bg-white border-0 shadow-sm">
                <h1 class="fw-bold mb-4">Privacy Notice</h1>
                <p class="text-muted">Last updated: 14 October 2026</p>

                <div class="alert alert-primary d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check-fill fs-4"></i>
                    <div>
                        <strong>Core Principle:</strong> Your family records are visible only to authorized family members. We do not sell your document data or use it for third-party advertising.
                    </div>
                </div>

                <h4 class="mt-4 fw-bold">1. Information We Collect</h4>
                <p class="text-secondary">
                    We collect only the metadata necessary to calculate expiry statuses and deliver timely reminders:
                </p>
                <ul class="text-secondary">
                    <li><strong>Account Information:</strong> Your name, email address, password hash, and configured time zone.</li>
                    <li><strong>Document Metadata:</strong> Document title, category, optional issuer/provider, reference number (optional), purchase/issue dates, expiry dates, and notes.</li>
                    <li><strong>Optional Attachments:</strong> If you choose to upload files (PDFs, images), they are stored privately on secure disks and served only to authorized members of your household.</li>
                    <li><strong>Reminder Logs:</strong> Delivery timestamps, recipient IDs, and delivery states to prevent duplicate emails.</li>
                </ul>

                <h4 class="mt-4 fw-bold">2. How Reminders Work & Email Privacy</h4>
                <p class="text-secondary">
                    Reminder emails sent by the service contain only the document title, category, due date, and a secure sign-in link. Sensitive account numbers, passports, and file scans are <strong>never</strong> transmitted in email bodies or attachments.
                </p>

                <h4 class="mt-4 fw-bold">3. Household Sharing & Workspace Ownership</h4>
                <p class="text-secondary">
                    Every document belongs to a family workspace. When you invite another member, they can see shared family documents. The workspace owner can manage members, export records to CSV, or request deletion of the workspace.
                </p>

                <h4 class="mt-4 fw-bold">4. Data Portability and Deletion</h4>
                <p class="text-secondary">
                    You have full control over your data. Workspace owners and administrators can export complete document metadata to CSV at any time. When a document is deleted, its metadata and attached files are permanently purged.
                </p>

                <h4 class="mt-4 fw-bold">5. Contact & Inquiries</h4>
                <p class="text-secondary">
                    For any questions regarding household privacy, data access requests, or security inquiries, please contact your workspace administrator or our support team.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

