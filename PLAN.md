# Expense Tracker - Implementation Plan

A Laravel-based daily expense tracking application built with a scalable, maintainable architecture, automated workflows, and advanced features that go beyond the baseline hiring challenge requirements.

---

## 1. Goals & Guiding Principles

- Deliver every requirement from the hiring challenge (auth, expense CRUD, reporting) with production-grade quality.
- Minimize manual effort: one-command setup, automated migrations, seeders, tests, and CI.
- Keep the architecture clean: thin controllers, service/repository layers, form requests for validation, policies for authorization, API resources for response shaping.
- Make the codebase easy to extend: predefined categories as a real table (not hard-coded enums), event-driven side effects, queueable jobs for heavy work.
- Ship with documentation, tests, and a Docker-based runtime so a reviewer can `git clone && make up` and be running in under two minutes.

---

## 2. Technology Stack

- **Framework:** Laravel 11 (PHP 8.3)
- **Database:** MySQL 8 (primary), SQLite for fast local tests
- **Cache & Queue:** Redis (sessions, cache, queued jobs, rate limiting)
- **Frontend:** Laravel Blade + Livewire 3 + Tailwind CSS + Alpine.js (server-rendered, reactive, no SPA overhead). A REST API layer is exposed in parallel for future mobile clients.
- **Authentication:** Laravel Breeze (web) + Laravel Sanctum (API tokens)
- **Authorization:** Laravel Policies + Gates
- **Testing:** Pest PHP (feature + unit), Laravel Dusk (browser smoke), PHPStan level 8, Laravel Pint (formatting)
- **Tooling:** Make, GitHub Actions CI, Composer scripts. **Docker + Laravel Sail are only configured if the user explicitly requests Docker setup** — by default the app runs against a locally installed PHP/MySQL/Redis stack or via `php artisan serve`.
- **Observability:** Laravel Telescope (local), Sentry (optional, behind env flag)

---

## 3. Minimal Manual Effort Workflow

> **Note on Docker:** Docker / Laravel Sail setup is **only** performed when the user explicitly requests it. The default workflow below assumes a standard local PHP toolchain (PHP 8.3, Composer, MySQL or SQLite, Node). A separate Docker section is included further down for reviewers who specifically ask for a containerized setup.

### Default one-command bootstrap (no Docker)

```
git clone <repo>
cd expense-tracker
make setup     # cp .env.example .env, composer install, npm ci,
               # key:generate, migrate --seed, npm run build
make serve     # php artisan serve + queue:work + schedule:work (concurrently)
```

A `Makefile` wraps the common commands so a reviewer doesn't need to memorize artisan syntax:

```
make setup     # install dependencies, configure env, migrate, seed, build assets
make serve     # run the app locally (php artisan serve)
make test      # run pest + phpstan + pint --test
make fresh     # migrate:fresh --seed (reset local data)
make queue     # start the queue worker
make schedule  # start the scheduler
```

### What happens automatically on `make setup`

1. `.env` is copied from `.env.example` if missing.
2. `composer install` and `npm ci` pull dependencies.
3. `php artisan key:generate` runs if `APP_KEY` is blank.
4. SQLite is the default `DB_CONNECTION` for zero-config local runs; MySQL can be selected by editing `.env`.
5. `php artisan migrate --seed` creates schema and seeds categories + a demo user.
6. `npm run build` compiles frontend assets via Vite.
7. The app is reachable at `http://localhost:8000` after `make serve`.

### Optional: Docker / Sail setup (only when explicitly requested)

If — and only if — the reviewer explicitly asks for a Docker-based workflow, the project adds:

- `docker-compose.yml` and a `docker/` folder with PHP-FPM, Nginx, MySQL, Redis, and Mailpit services.
- Laravel Sail (`composer require laravel/sail --dev`) for ergonomic container commands.
- Extended Makefile targets (`make up`, `make down`, `make shell`, `make logs`) that proxy to Sail.
- A documented switch in `.env` (`DB_HOST=mysql`, `REDIS_HOST=redis`) so the same codebase runs in both modes.

This keeps the default project lightweight and avoids forcing Docker on reviewers who already have a local PHP environment.

### Seeded demo data

- 12 predefined categories (Food, Transportation, Entertainment, Utilities, Rent, Healthcare, Shopping, Travel, Education, Subscriptions, Savings, Other).
- A demo user: `demo@example.com` / `password` with ~90 days of synthetic expenses for instant report exploration.

---

## 4. Database Schema

All tables use UUID primary keys (better for distributed systems, harder to enumerate) and standard `created_at` / `updated_at` timestamps. Soft deletes on user-owned data so nothing is lost to accidental deletion.

| Table | Key Columns | Notes |
|---|---|---|
| `users` | `id` (uuid), `name`, `email` (unique), `password`, `email_verified_at`, `timezone`, `currency` | Email verification required. Per-user timezone so dates display correctly. |
| `categories` | `id` (uuid), `name`, `slug` (unique), `icon`, `color`, `is_system` | Seeded with defaults. `is_system` categories cannot be deleted. Future-ready for per-user custom categories. |
| `expenses` | `id` (uuid), `user_id` (fk), `category_id` (fk), `amount` (decimal 12,2), `currency` (char 3), `description`, `occurred_at` (timestamp) | `decimal(12,2)` not float — floats cause rounding bugs in money math. Indexed on `(user_id, occurred_at)` and `(user_id, category_id, occurred_at)` for fast reports. |
| `budgets` *(advanced)* | `id`, `user_id`, `category_id` (nullable), `amount`, `period` (monthly/weekly), `starts_on` | Optional per-category or overall budgets. |
| `recurring_expenses` *(advanced)* | `id`, `user_id`, `category_id`, `amount`, `description`, `cadence` (daily/weekly/monthly), `next_run_at` | Generates expenses on schedule. |
| `personal_access_tokens` | Sanctum default | API tokens for mobile/CLI. |
| `jobs`, `failed_jobs`, `cache`, `sessions` | Laravel defaults | Redis-backed in production. |

Indices are added explicitly in migrations rather than relying on the framework defaults, because reports are the hot path.

---

## 5. Application Architecture

A layered architecture keeps controllers thin and business logic testable.

```
HTTP Request
    ↓
Route → Middleware (auth, throttle, verified)
    ↓
FormRequest (validation + authorization)
    ↓
Controller (orchestration only, ~10 lines)
    ↓
Service / Action class (business logic)
    ↓
Repository / Eloquent model (persistence)
    ↓
Events → Listeners / Queued Jobs (side effects: cache bust, notifications)
    ↓
API Resource / Blade view (response shaping)
```

### Key conventions

- **Single-action invokable controllers** for non-CRUD endpoints (e.g. `GenerateMonthlyReportController`).
- **Form Requests** for every write endpoint — no inline `$request->validate()` calls.
- **Policies** for every model — controllers call `$this->authorize('update', $expense)`.
- **API Resources** for every JSON response — no leaking Eloquent internals.
- **Service classes** for non-trivial business logic (e.g. `ExpenseReportService`, `RecurringExpenseGenerator`).
- **Events + Listeners** for cross-cutting effects (`ExpenseCreated` → invalidate report cache, check budget thresholds).
- **Strict types** (`declare(strict_types=1);`) and **readonly DTOs** for transferring data between layers.

---

## 6. Feature Implementation

### 6.1 User Management

- Laravel Breeze for the web stack (register, login, logout, password reset, email verification).
- Sanctum for token-based API auth.
- Rate limiting on login (5 attempts / minute / IP) via `RateLimiter::for('login')`.
- Password rules: min 12 chars, mixed case, number, symbol, breached-password check via `Password::min(12)->uncompromised()`.
- Per-user timezone and currency captured at registration.
- Audit log of login events (advanced).

### 6.2 Expense Management

- **Create / Read / Update / Delete** via `ExpensesController` (resource controller).
- **Validation** in `StoreExpenseRequest` / `UpdateExpenseRequest`:
  - `amount`: required, numeric, `>0`, `<=99999999.99`
  - `description`: required, string, max 500
  - `category_id`: required, exists, must be active
  - `occurred_at`: required, date, not in the future
- **Authorization** via `ExpensePolicy` — users only see/edit/delete their own expenses.
- **Filters** on the index page: date range, category, amount range, full-text search on description.
- **Bulk actions:** delete multiple, recategorize multiple.
- **CSV import / export** for bulk entry.

### 6.3 Reporting

Three required reports plus extras, all served from `ExpenseReportService`:

1. **Total expenses per category for a specific month** — grouped query, returned as labeled dataset for a donut chart.
2. **Average daily expenses for a specific month** — `SUM(amount) / DAY(LAST_DAY(month))`, with a 30-day trailing average for context.
3. **Total expenses per category for a specific user** (lifetime) — for the dashboard overview.
4. **Bonus reports:**
   - Month-over-month spend trend (line chart, last 12 months).
   - Top 5 categories this month vs. previous month.
   - Day-of-week spending heatmap.
   - Budget vs. actual (if budgets enabled).

All report queries are **cached** with a key derived from `user_id + report_type + period` and invalidated by the `ExpenseCreated` / `ExpenseUpdated` / `ExpenseDeleted` events.

---

## 7. Code Optimization

Concrete optimizations and the reasoning behind each:

- **Eager loading** (`with('category')`) on expense lists to avoid N+1.
- **Composite indexes** on `expenses(user_id, occurred_at)` and `expenses(user_id, category_id)` — every report query filters by user first.
- **Aggregate at the database**, not in PHP. `SELECT category_id, SUM(amount) ... GROUP BY category_id` is orders of magnitude faster than fetching all rows and summing in a loop.
- **Cache report results** in Redis with a 1-hour TTL plus event-driven invalidation. Reports are expensive and read 100× more often than written.
- **Decimal arithmetic** via `brick/money` for monetary calculations — avoids float rounding errors.
- **Paginate** all list endpoints (cursor pagination for the API).
- **Queue heavy work** — CSV imports, recurring expense generation, and PDF report exports run on the queue, not in the request cycle.
- **Database transactions** around any multi-statement write.
- **Lazy collections** for CSV import so large files don't blow memory.

---

## 8. Testing Strategy

Aim for >85% line coverage with a layered pyramid.

- **Unit tests** for services, actions, and value objects (`ExpenseReportServiceTest`, `MoneyTest`).
- **Feature tests** for HTTP endpoints — auth flow, expense CRUD, report endpoints, authorization edge cases (User A cannot see User B's expenses).
- **Database tests** using SQLite in-memory for speed, RefreshDatabase trait.
- **Browser smoke tests** with Dusk for the critical happy path (register → add expense → view report).
- **Static analysis:** PHPStan level 8 in CI.
- **Style:** Laravel Pint in CI.
- **Mutation testing** (optional, advanced): Infection PHP to validate test quality.

### Test plan (articulated for the reviewer)

| Area | What we verify |
|---|---|
| Auth | Register validation, login throttling, password rules, logout invalidates session, email verification gate |
| Expense CRUD | Validation rules, ownership enforcement, soft delete, edit history |
| Reports | Correct aggregation across edge cases (empty month, single expense, leap year February, timezone boundaries) |
| API | Token auth, resource shape, pagination, rate limits |
| Authorization | User B receives 403/404 (not 200) for User A's resources |
| Performance | Reports return in <100ms with 10k expenses (benchmarked) |

---

## 9. Advanced Features (Beyond Baseline)

These ship as part of the submission to demonstrate scope of thinking, not as a separate phase.

1. **Budgets & alerts.** Users set monthly budgets per category. When spend hits 80% / 100%, a queued job sends an email + in-app notification.
2. **Recurring expenses.** Define a template (rent, subscriptions) with a cadence. A scheduled command (`expenses:generate-recurring`) runs daily and creates the expenses.
3. **Multi-currency support.** Expenses stored in their native currency; reports converted to the user's display currency using cached exchange rates (free tier of an FX API, fallback to stored rates).
4. **CSV import / export.** Drag-and-drop CSV upload, dry-run preview, queued processing, downloadable error report.
5. **Receipt attachments.** Optional image upload per expense, stored on S3 (or local disk in dev), OCR'd via a queued job to auto-fill amount and merchant (Tesseract or AWS Textract behind a strategy interface).
6. **REST API + Sanctum tokens.** Versioned (`/api/v1`) for future mobile clients. OpenAPI spec auto-generated via Scribe.
7. **Dashboard widgets** built with Livewire — real-time reactivity without writing JavaScript.
8. **Dark mode** and **PWA** install prompt.
9. **Data export** — full account export as JSON / CSV (GDPR-friendly).
10. **Two-factor authentication** via Fortify.

---

## 10. CI / CD

GitHub Actions pipeline runs on every push and PR:

1. Checkout, set up PHP 8.3, cache Composer.
2. `composer install --no-interaction --prefer-dist`
3. Copy `.env.testing`, generate key.
4. `php artisan migrate --env=testing`
5. `vendor/bin/pint --test` (style)
6. `vendor/bin/phpstan analyse` (static analysis)
7. `vendor/bin/pest --coverage --min=85` (tests with coverage gate)
8. On `main`: build Docker image, push to registry, deploy to staging (optional).

---

## 11. Documentation Deliverables

- `README.md` — project overview, one-command setup, demo credentials, feature tour with screenshots, troubleshooting.
- `docs/architecture.md` — layer diagram, request lifecycle, design decisions and trade-offs.
- `docs/api.md` — auto-generated OpenAPI / Scribe output.
- `docs/testing.md` — how to run tests, coverage report location, test plan.
- `docs/deployment.md` — how to deploy to a fresh VPS or Forge.
- Inline PHPDoc on every public method; complex queries explained with a comment.

---

## 12. Implementation Timeline

A realistic order of operations if I were starting this Monday:

| Phase | Scope | Effort |
|---|---|---|
| 1. Foundation | Repo, Breeze, Pint, PHPStan, Pest, CI skeleton (Docker/Sail added only if explicitly requested) | 0.5 day |
| 2. Schema & models | Migrations, factories, seeders, policies | 0.5 day |
| 3. Expense CRUD | Controller, FormRequests, Livewire views, tests | 1 day |
| 4. Reporting | Service, cache layer, Chart.js views, tests | 1 day |
| 5. API layer | Sanctum, resources, route group, Scribe docs | 0.5 day |
| 6. Advanced features | Budgets, recurring, CSV import, multi-currency | 1.5 days |
| 7. Polish | Dashboard UX, dark mode, README, screenshots | 0.5 day |
| 8. Hardening | Load test reports, fix N+1s, security review | 0.5 day |

**Total: ~6 working days** for the full advanced submission. The baseline (auth + expense CRUD + three reports + tests + Docker setup) is comfortably done in 2 days.

---

## 13. What "Done" Looks Like

- `make setup && make serve` boots the entire app on a clean machine with a local PHP toolchain in under two minutes (or `make up` if Docker setup was explicitly requested).
- `make test` passes green, with >85% coverage and zero PHPStan errors.
- A reviewer can register, add expenses, and view all three required reports plus the advanced dashboard within five minutes of cloning.
- The README answers every question a reviewer would ask before they ask it.
- Every business rule is enforced in code, validated by a test, and documented in a comment or PHPDoc.