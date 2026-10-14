# Family Document Expiry Tracker

A privacy-focused web application for households to record and track important documents, expiry dates, warranties, and renewal reminders.

Built with **Laravel 12**, **MySQL / SQLite**, **Blade**, and **Bootstrap 5.3** in accordance with the *Family Document Expiry Tracker Product Requirements, Technical Design & Implementation Plan (Version 1.0)*.

---

## Key Features

- **Household Workspaces**:
  - Each family operates in an isolated workspace.
  - Multi-user sharing with role-based access control (`Owner`, `Administrator`, `Member`).
  - Secure, time-limited, single-use email invitation tokens (hashed with SHA-256).

- **Document & Warranty Tracking**:
  - Comprehensive metadata: Title, Category, Issuer/Provider, Reference Number, Issue Date, Purchase Date, Expiry Date, Responsible Person, Notes.
  - Quick-add templates: Vehicle Insurance, PUC Certificate, Appliance Warranty, Passport, Health Insurance, House Lease.
  - Automatic computed statuses: `Expired`, `Expires Today`, `Due Soon (<= 30 days)`, `Valid / Current`, `No Expiry Date`, `Archived`.
  - Renewal workflow: Update expiry dates, track renewal notes, audit old dates, and auto-invalidate obsolete reminder schedules.

- **Private & Protected File Storage**:
  - Optional file attachments (PDF, JPG, PNG up to 10 MB).
  - Private disk storage: files are never placed in public directories and can only be downloaded via authorized endpoints after family membership verification.

- **Reliable Reminder Engine & Duplicate Prevention**:
  - Daily reminder processing via `php artisan reminders:send`.
  - Configurable reminder offsets (e.g. 60, 30, 15, 7, 3, 1, 0 days before deadline).
  - Unique SHA-256 idempotency key prevents duplicate sends.
  - Privacy-preserving emails: zero sensitive identifiers or scans are sent in email bodies.

- **Data Portability & Security**:
  - CSV metadata export with spreadsheet formula injection protection (automatic escaping of `=`, `+`, `-`, `@` triggers).
  - Full audit event trail (`audit_events`) logging security actions (invitations, role updates, renewals, deletions).
  - India-first timezone support (`Asia/Kolkata` default, configurable).

---

## Getting Started

### Prerequisites

- PHP 8.2+ with PDO, SQLite / MySQL, OpenSSL, and cURL extensions.
- Composer 2.x

### Installation

1. **Clone the repository and install dependencies**:
   ```bash
   composer install
   ```

2. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```
   *This seeds default categories and a demo household workspace.*

4. **Start the Local Development Server**:
   ```bash
   php artisan serve
   ```
   Open your browser at `http://127.0.0.1:8000`.

---

## Demo Accounts

The database seeder provisions a ready-to-test household workspace:

| Email | Password | Role | Family Workspace |
|---|---|---|---|
| `demo@example.com` | `password` | Owner | Philip Household |
| `sarah@example.com` | `password` | Administrator | Philip Household |

Or you can click **Create Account** on the landing page to register a brand new household.

---

## Automated Reminder Job

To manually run the daily reminder processing engine:
```bash
php artisan reminders:send
```

To run the scheduled queue in background:
```bash
php artisan schedule:work
```

---

## Running the Automated Test Suite

```bash
php artisan test
```

All 17 automated feature tests test:
- Registration and default workspace creation.
- Document CRUD, status calculation, renewal, and deletion.
- Cross-family data isolation and direct-object-reference protection.
- Authorized file downloads.
- Family invitations and role escalation defenses.
- Reminder idempotency and deduplication.
- CSV spreadsheet formula injection escaping.
