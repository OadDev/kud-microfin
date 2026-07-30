# BluePeak Fintech — Microfinance Management Backend

Laravel 11 backend for the BluePeak Fintech microfinance management app, rebuilding the
[`prototype/index.html`](prototype/index.html) frontend prototype as a real, database-backed
application (server-rendered Blade views, MySQL, session auth).

## What's implemented

- **Auth**: session-based login for Admin/Shop Owner (mobile or email + password), OTP login for
  Customers, public Shop Owner self-registration, role middleware (`role:admin,shop_owner,...`).
- **Admin**: dashboard, Shop Owner management (approve/reject/suspend), Customer list/detail,
  Active Loans, Payment Verification (approve/reject with reason), Payment Settings, Documents.
- **Shop Owner**: own dashboard, own Customers, Create Customer & Loan, Active Loans, EMI List,
  Documents — scoped so a shop owner only ever sees their own customers/loans.
- **Customer**: mobile-first panel (Home, Loan, Pay EMI, Documents, Profile) with OTP login,
  manual payment submission (UPI/Bank/QR + screenshot upload) and live status tracking.
- **Loans & EMIs**: loan calculation (Total Payable = Principal + Interest + Fee, EMI = Total ÷
  Count), EMI schedule generation (weekly/monthly), EMI status derived live from the due date
  (Upcoming/Due Today/Overdue) rather than a cron job.
- **Documents**: printable Welcome Letter / Loan Sanction Letter (Blade views with the BluePeak
  letterhead), signed-copy upload.

### Deliberately not real yet (matches product decisions made during scoping)

- **OTP is simulated**: a real 6-digit code is generated and stored server-side and must be
  entered correctly, but it is not sent via SMS — it's flashed back to the login page instead
  (`App\Models\OtpCode`, `CustomerAuthController`). Swap in a real provider (e.g. MSG91, Twilio)
  by replacing that flash with an actual send call.
- **No payment gateway**: all EMI payments are manual (UPI/QR/bank transfer + screenshot),
  verified by Admin. This is a permanent product decision, not a placeholder.
- **File storage** is local disk (`storage/app/public`, via `php artisan storage:link`) — swap the
  `public` disk for `s3` in `config/filesystems.php` if you later move to cloud storage.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd`, `zip`, `curl`, `fileinfo` extensions
- Composer
- MySQL 8 (or MariaDB 10.11+)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) —
create the database first, e.g.:

```sql
CREATE DATABASE bluepeak_fintech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run migrations and seed demo data:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Visit `http://localhost:8000`.

## Demo accounts (from the seeder)

All seeded accounts use the password **`password`**.

| Role | Mobile | Notes |
|---|---|---|
| Admin | `9999900000` | admin@bluepeakfintech.com |
| Shop Owner (approved) | `9876511223` | Amit Sharma — Sharma Finance Point |
| Shop Owner (pending) | `9812345670` | Priya Verma — awaiting approval |
| Customer | `9876543210` | Rajesh Kumar — has a mix of paid/pending/overdue EMIs |

The login page's "Quick Demo Access" buttons log straight into the first seeded Admin / Shop
Owner / Customer account. Customer OTP login works against any seeded customer mobile number —
the generated OTP is shown on screen since SMS isn't wired up yet.

## Notes for deployment

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_KEY` before going live.
- The demo-login shortcut (`POST /demo-login/{role}`) logs into the *first* seeded user of that
  role — remove that route/button (`AuthController::demoLogin`, `auth/login.blade.php`) before
  shipping to real users, or gate it behind an environment check.
- Bootstrap 5, Font Awesome and Chart.js are loaded from CDN (same as the frontend prototype) —
  no `npm install`/`npm run build` step is required to run the app.
