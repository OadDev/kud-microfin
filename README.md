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
- **File storage** is local disk (`public/uploads` in dev, see "Deployment" below for why
  production doesn't use the usual `storage/app/public` symlink) — swap the `public` disk for
  `s3` in `config/filesystems.php` if you later move to cloud storage.

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
php artisan serve
```

Visit `http://localhost:8000`. (No `storage:link` needed — see "Deployment" for why uploads use
`public/uploads` instead of the usual Laravel symlink.)

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

## Deployment (Hostinger, GitHub Actions)

This repo ships a GitHub Actions workflow (`.github/workflows/deploy.yml`) that deploys straight
into a Hostinger hPanel `public_html` folder over SSH on every push. It was built for exactly
this layout choice — **the whole Laravel app deployed flat into `public_html`** (not just
`public/`) — which is simpler to set up than the "app outside the web root" alternative, at the
cost of relying on `.htaccess` rules to keep `app/`, `vendor/`, `.env` etc. from being served
directly. Those rules are already in place (see "How the flat layout is hardened" below); you
don't need to do anything extra for them to take effect, as long as Apache/LiteSpeed on your plan
honors `.htaccess` (Hostinger's shared/business hosting does by default).

### 1. GitHub secrets and variables to add

Go to **GitHub → this repo → Settings → Secrets and variables → Actions** and add these yourself
— pull the real values from **Hostinger hPanel → Advanced → SSH Access** for this hosting
account. Nothing here is something Claude can generate or already knows; there is nothing to
paste into chat.

**Secrets** (Secrets tab):

| Secret name | Value |
|---|---|
| `HOSTINGER_SSH_HOST` | The SSH host shown in hPanel (often `srv###.hostinger.com` or an IP) |
| `HOSTINGER_SSH_PORT` | The SSH port shown in hPanel — **commonly `65002` on Hostinger shared/business plans, not `22`** — double-check yours |
| `HOSTINGER_SSH_USERNAME` | Your SSH username (often the same as your hosting account username, e.g. `u761085554`) |
| `HOSTINGER_SSH_PASSWORD` | Your SSH password from hPanel |

**Variables** (Variables tab — not secret, just config):

| Variable name | Value |
|---|---|
| `HOSTINGER_TARGET_PATH` | `/home/u761085554/domains/bluepeakfintech.com/public_html` |

Password-based SSH auth works fine for this workflow, but if hPanel ever lets you switch to a
public/private keypair instead, that's more secure for something stored long-term in CI — a
leaked key can be revoked without changing your hosting account password.

### 2. First deploy

Push to `main` (or the branch listed in the workflow's `on: push: branches:` — update that list
once your git flow settles) or trigger the workflow manually from the **Actions** tab
("Deploy to Hostinger" → **Run workflow**). The workflow:

1. Installs PHP deps (`composer install --no-dev`).
2. Builds a `deploy/` folder: merges `public/`'s contents up to the root (so `public_html` itself
   becomes the doc root — see below), patches `index.php`'s paths to match, and adds a deny-all
   `.htaccess` to `vendor/` (not in git, so it needs one added at build time).
3. `rsync`s that over SSH into `HOSTINGER_TARGET_PATH`, **never touching** `.env`, `uploads/`, or
   `storage/{app,framework,logs}` — those persist across deploys.
4. Runs `scripts/remote-setup.sh` on the server: creates the writable runtime directories,
   bootstraps `.env` from `.env.production.example` **only if `.env` doesn't exist yet** (i.e.
   only on the very first deploy), generates a fresh `APP_KEY` if it's blank, and clears cached
   config/routes/views.

At this point the app is deployed but has **no database configured yet**. Visit your domain —
you'll land on **`/install`**, a setup wizard that checks PHP requirements, asks for your MySQL
connection details (create the database in hPanel first) and creates the real Admin account you
log in with. There's a checkbox to also load the same sample demo data used locally, useful for
a walkthrough with the client before real data goes in — leave it unchecked for a clean start.

`/install` locks itself after a successful run (`storage/app/installed.lock`) and won't accept
being run again; visiting it afterward just redirects to the login page.

### 3. Every deploy after that

Just push. The workflow re-syncs code and clears caches; `.env`, uploads, and the installed
lock stay untouched, so nothing about the running site resets.

### How the flat layout is hardened

Because `app/`, `vendor/`, `config/`, `.env` etc. all sit in `public_html` next to `index.php`
instead of one level above it, there's a real risk of them being served as static files if
nothing stops it. Two things handle that:

- `public/.htaccess` (Laravel's own rewrite rules, which becomes the doc-root `.htaccess` after
  the merge step) has added `FilesMatch` rules denying any dotfile (`.env`, `.gitignore`, ...)
  and `composer.json`/`composer.lock`/`artisan`/`phpunit.xml`.
- `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `tests/`
  each carry their own deny-all `.htaccess` (`Require all denied`); `vendor/`'s copy is added at
  deploy time since that folder isn't in git.
- Uploaded files (payment screenshots, QR codes) deliberately do **not** use Laravel's usual
  `storage/app/public` + `public/storage` symlink — in this flat layout that symlink's name
  would collide with the real `storage/` folder sitting right next to it. They go straight into
  `public/uploads` (`config/filesystems.php`, with a layout-aware path override in
  `AppServiceProvider` so this still works correctly in normal, non-flattened local dev too).

If you'd rather have `app/`, `vendor/` etc. outside the web root entirely (the more conventional,
slightly more secure setup), that's a valid alternative — it just needs `public_html` pointed at
a `public/` folder living outside it (via hPanel's document root setting, if your plan exposes
one) instead of this merge-and-harden approach. That's a bigger change to the workflow than is
worth making speculatively; ask if you want it switched over.

### Other things worth knowing before going live

- The demo-login shortcut (`POST /demo-login/{role}`) logs into the *first* seeded user of that
  role. It's harmless once you're not running the dev seeder in production (there's nothing for
  it to log into), but if you ever do run `DemoDataSeeder` on a public instance, remove that
  route/button (`AuthController::demoLogin`, `auth/login.blade.php`) or gate it behind an
  environment check first.
- Bootstrap 5, Font Awesome and Chart.js load from CDN (same as the frontend prototype) — no
  `npm install`/`npm run build` step anywhere in this deploy.
- Customer OTPs still aren't sent by SMS in production either (see "Deliberately not real yet"
  above) — they're only ever shown on the login page itself, which is fine for a soft launch but
  worth fixing before real customers rely on it.
