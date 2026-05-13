# Claude.md — Engineering Charter

**Project:** Expense Tracker (Laravel 11 + PHP 8.3, MySQL 8)
**Mission:** Ship a production-ready application in **one calendar day** without compromising on security, validation, testing, or architectural integrity.

This document is the single source of truth for how every line of code in this project is written. If a rule here conflicts with a habit, the rule wins. If a shortcut conflicts with a rule, the shortcut loses.

---

## 0. The One-Day Contract

Shipping in a day means **ruthless prioritization**, not skipped quality. Every rule below is mandatory; every "nice-to-have" from the plan is explicitly deferred.

**In scope today (must ship):**
1. Auth (register, login, logout, password reset) via Laravel Breeze.
2. Expense CRUD with FormRequest validation, Policy authorization, and pagination.
3. Three required reports: monthly per-category totals, monthly daily-average, lifetime per-category totals.
4. REST API (`/api/v1`) with Sanctum, API Resources, and rate limiting.
5. Repository pattern for every data access path.
6. Tests for every endpoint + every repository method (Pest, RefreshDatabase, SQLite).
7. README + `.env.example` + seeders with demo data.
8. Production-grade security (see Section 11).

**Explicitly deferred (not today):**
- Budgets, recurring expenses, multi-currency, CSV import/export, receipts/OCR, 2FA, dark mode, PWA, Scribe API docs, Telescope.
- Dusk browser tests (Pest feature tests cover the same surfaces faster).
- Docker / Sail — **not configured by default.** Added only on explicit request.

**Time budget (8 productive hours, single-day execution):**

| Block | Hour | Deliverable |
|---|---|---|
| 1. Foundation | 0:00–0:45 | Laravel 11 install, Breeze, Sanctum, Pint, PHPStan, Pest, MySQL connection verified |
| 2. Schema + models | 0:45–1:45 | Migrations, models, factories, seeders, policies, enum |
| 3. Repository + service layer | 1:45–2:45 | Interfaces, Eloquent implementations, service provider binding |
| 4. Expense CRUD (API + web) | 2:45–4:30 | Controllers, FormRequests, API Resources, Blade/Livewire views |
| 5. Reports | 4:30–5:30 | `ExpenseReportService`, three report endpoints, cache layer |
| 6. Tests | 5:30–6:45 | Auth, CRUD, reports, authorization, repository tests — all green |
| 7. Hardening + README | 6:45–7:45 | Security headers, rate limits, README, demo seeder, screenshot, final test run |
| 8. Buffer / polish | 7:45–8:00 | Final `make test`, commit, push |

---

## 1. Tech Stack (Locked)

- **PHP 8.3+** (latest stable). `declare(strict_types=1);` at the top of every file.
- **Laravel 11.x** (latest stable). Streamlined skeleton — middleware in `bootstrap/app.php`, no `Kernel.php`.
- **Database: MySQL 8.x.** This is non-negotiable. SQLite is allowed **only** for the test suite (`phpunit.xml`).
- **Auth:** Laravel Breeze (Blade stack) + Laravel Sanctum (API tokens).
- **Frontend:** Blade + minimal Alpine.js for the web layer (skip Livewire today — adds setup time without earning its keep in 8 hours). API consumers use JSON.
- **Cache / Queue / Session:** `database` driver today (zero-infra). Redis is a one-line `.env` swap when needed.
- **Testing:** Pest PHP. Static analysis: Larastan level 6 (level 8 is a stretch goal — start at 6, raise if time allows). Style: Laravel Pint.
- **Docker:** **Not included by default.** Only added when the user explicitly requests Docker setup. The default workflow uses a local PHP 8.3 + MySQL 8 toolchain via `php artisan serve`.

---

## 2. Folder Structure

```
app/
├── Enums/                          # ReportPeriod, etc. PHP 8.1 backed enums.
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/                 # Versioned API controllers
│   │   └── Web/                    # Blade controllers
│   ├── Middleware/
│   ├── Requests/                   # FormRequest per write endpoint
│   │   ├── Auth/
│   │   └── Expense/
│   ├── Resources/                  # API Resources
│   └── Filters/                    # Optional: query filter objects
├── Models/                         # Eloquent models only — no business logic
├── Policies/                       # One per model
├── Providers/
│   └── RepositoryServiceProvider.php
├── Repositories/
│   ├── Contracts/                  # Interfaces
│   │   ├── ExpenseRepositoryInterface.php
│   │   └── CategoryRepositoryInterface.php
│   └── Eloquent/                   # Eloquent implementations
│       ├── ExpenseRepository.php
│       └── CategoryRepository.php
├── Rules/                          # Custom validation rules
├── Services/                       # Business logic
│   └── ExpenseReportService.php
└── Support/
    └── DataTransferObjects/        # Readonly DTOs

database/
├── factories/
├── migrations/
└── seeders/

routes/
├── api.php                         # /api/v1/*
├── web.php
└── console.php

tests/
├── Feature/
│   ├── Api/
│   └── Web/
└── Unit/
```

**Hard rules:**
- Models contain relationships, casts, scopes, accessors/mutators — **never business logic**.
- Controllers are thin: validate (FormRequest) → authorize (Policy) → delegate (Repository/Service) → return Resource. Target: under 15 lines per action.
- Services orchestrate; Repositories persist; Resources shape; Requests validate; Policies authorize. Each layer has exactly one job.

---

## 3. Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Classes | `PascalCase`, singular | `ExpenseRepository`, `User` |
| Interfaces | `PascalCase` + `Interface` suffix | `ExpenseRepositoryInterface` |
| Methods / variables | `camelCase` | `getMonthlyTotal()`, `$totalAmount` |
| Constants | `SCREAMING_SNAKE_CASE` | `MAX_AMOUNT` |
| Enum cases | `PascalCase` | `ReportPeriod::Monthly` |
| DB tables | `snake_case`, plural | `expenses`, `categories` |
| DB columns | `snake_case` | `user_id`, `occurred_at` |
| Foreign keys | `{singular}_id` | `user_id`, `category_id` |
| Pivot tables | alphabetical singular_singular | `category_user` |
| Migrations | timestamp + descriptive snake_case | `2026_05_12_000000_create_expenses_table.php` |
| Route URIs | `kebab-case`, plural | `/api/v1/expenses`, `/api/v1/expense-categories` |
| Route names | `dot.notation` | `expenses.index`, `expenses.store` |
| Blade views | `kebab-case`, dot-nested | `expenses.partials.list-row` |
| Env vars | `SCREAMING_SNAKE_CASE` | `DB_CONNECTION` |
| Tests (Pest) | descriptive sentence | `it('lists only the user\'s own expenses')` |

---

## 4. SOLID Principles (Non-Negotiable)

- **S — Single Responsibility.** One class, one reason to change. A controller does not query the DB. A model does not send email. A service does not format JSON.
- **O — Open/Closed.** Add behavior via new classes, not by editing existing ones. New report type → new service strategy, not an `if` branch.
- **L — Liskov Substitution.** Any repository implementation must be a drop-in replacement for its interface. No surprise exceptions, no narrowed return types.
- **I — Interface Segregation.** Keep interfaces small and focused. A `ReadOnlyRepository` should not be forced to implement `delete()`.
- **D — Dependency Inversion.** Depend on interfaces, not concretions. Inject through the constructor — **never** call `app()` or `resolve()` from business code.

---

## 5. Repository Pattern (Mandatory)

Every model with non-trivial queries gets a repository. **Controllers and services never call Eloquent directly.**

### 5.1 Interface

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ExpenseRepositoryInterface
{
    public function paginateForUser(string $userId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findForUser(string $userId, string $expenseId): ?Expense;

    public function create(array $data): Expense;

    public function update(Expense $expense, array $data): Expense;

    public function delete(Expense $expense): bool;

    public function totalsByCategoryForMonth(string $userId, int $year, int $month): Collection;

    public function dailyAverageForMonth(string $userId, int $year, int $month): float;

    public function lifetimeTotalsByCategory(string $userId): Collection;
}
```

### 5.2 Eloquent implementation

```php
<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(private readonly Expense $model) {}

    public function paginateForUser(string $userId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with('category')                                            // eager load — no N+1
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->where('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->where('occurred_at', '<=', $to))
            ->when($filters['search'] ?? null, fn ($q, $term) =>
                $q->where('description', 'like', '%' . $term . '%')       // parameter-bound
            )
            ->latest('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForUser(string $userId, string $expenseId): ?Expense
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('id', $expenseId)
            ->first();
    }

    public function create(array $data): Expense
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->fill($data)->save();

        return $expense->fresh(['category']);
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }

    public function totalsByCategoryForMonth(string $userId, int $year, int $month): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')                                            // no join — eager load
            ->get();
    }

    public function dailyAverageForMonth(string $userId, int $year, int $month): float
    {
        $total = (float) $this->model->newQuery()
            ->where('user_id', $userId)
            ->whereYear('occurred_at', $year)
            ->whereMonth('occurred_at', $month)
            ->sum('amount');

        $daysInMonth = (int) date('t', strtotime("{$year}-{$month}-01"));

        return $daysInMonth > 0 ? round($total / $daysInMonth, 2) : 0.0;
    }

    public function lifetimeTotalsByCategory(string $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();
    }
}
```

### 5.3 Service-provider binding

```php
// app/Providers/RepositoryServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \App\Repositories\Contracts\ExpenseRepositoryInterface::class,
        \App\Repositories\Eloquent\ExpenseRepository::class,
    );

    $this->app->bind(
        \App\Repositories\Contracts\CategoryRepositoryInterface::class,
        \App\Repositories\Eloquent\CategoryRepository::class,
    );
}
```

**Rules:**
- One interface per aggregate root.
- Repositories return Eloquent models, Collections, or Paginators — never `stdClass` arrays.
- Repositories never echo, return JSON, or know HTTP exists.
- All filtering happens inside repository methods — controllers pass arrays, not query closures.

---

## 6. Form Requests & Validation Rules

Every write endpoint has a dedicated FormRequest. No inline `$request->validate()` ever.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount'      => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:500'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
        ];
    }
}
```

**Rules:**
- One FormRequest per write action (`Store*`, `Update*`).
- Reusable rules → invokable Rule classes in `app/Rules/` (e.g. `StrongPassword`, `OwnedByUser`).
- Always use `$request->validated()` — never `$request->all()`, never `$request->input()` for persisted data.
- Always include the resolved user via `$this->user()->id`, never accept `user_id` from the client.

---

## 7. API Resources (Required for Every JSON Response)

Eloquent models are **never** returned from controllers. Every response goes through a Resource.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ExpenseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'amount'      => (float) $this->amount,
            'currency'    => $this->currency,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at->toIso8601String(),
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
```

For collections: `ExpenseResource::collection($paginator)` — Laravel wraps it with `data`, `links`, and `meta` automatically.

---

## 8. Pagination (Mandatory for All Listings)

- Every list endpoint paginates. `Model::all()` is banned in production code paths.
- Default `per_page = 15`, max `100`, configurable via `?per_page=`.
- Always call `->withQueryString()` so filters survive page changes.
- Use `cursorPaginate()` for very large datasets (exports).

```php
public function index(IndexExpenseRequest $request): AnonymousResourceCollection
{
    $perPage = min((int) $request->input('per_page', 15), 100);

    $expenses = $this->expenses->paginateForUser(
        userId:  $request->user()->id,
        filters: $request->validated(),
        perPage: $perPage,
    );

    return ExpenseResource::collection($expenses);
}
```

---

## 9. Eloquent-First Data Access — No Joins, No Raw SQL

**The rule:** use Eloquent ORM for all data access. No explicit `join()` calls. No raw SQL unless absolutely unavoidable.

- Define relationships on models (`hasMany`, `belongsTo`, `belongsToMany`).
- Traverse them via **eager loading**: `with('category')`, `with(['user', 'category'])`.
- Filter across relations with `whereHas('category', fn ($q) => $q->where('slug', 'food'))`.
- Aggregate across relations with `withCount`, `withSum`, `withAvg`.
- If a raw expression is truly unavoidable (e.g. `selectRaw('SUM(amount) as total')` for grouping), it must contain **zero user-supplied input**. All filtering values must travel through Eloquent's parameter-binding methods (`where`, `whereIn`, etc.).
- **Never** interpolate variables into SQL strings. Never. Not even once. Not even "just for a quick test."

### Bound parameters for the rare raw call

```php
// Only when Eloquent genuinely cannot express it:
DB::select('SELECT * FROM expenses WHERE user_id = ? AND amount > ?', [$userId, $threshold]);
```

### N+1 is a bug

Enable `Model::preventLazyLoading(! app()->isProduction());` in `AppServiceProvider::boot()`. Any lazy load outside production throws — caught in CI before it reaches users.

---

## 10. API Structure

- **Versioning:** all API routes under `/api/v1`. Breaking changes → `/api/v2`.
- **Resource naming:** plural kebab-case nouns — `/api/v1/expenses`, `/api/v1/expense-categories`.
- **HTTP verbs:** `GET` (read), `POST` (create), `PUT`/`PATCH` (update), `DELETE` (delete).
- **Status codes:** `200`, `201`, `204`, `400`, `401`, `403`, `404`, `409`, `422`, `429`, `500`.
- **Validation errors:** Laravel default `422` JSON shape:
  ```json
  {
    "message": "The given data was invalid.",
    "errors": { "amount": ["Amount must be greater than zero."] }
  }
  ```
- **Auth:** Sanctum bearer tokens. Routes inside `Route::middleware(['auth:sanctum', 'throttle:api'])`.
- **Pagination metadata:** handled by Resource collections — never roll your own.
- **Filtering / sorting:** documented query params (`?category_id=`, `?from=`, `?to=`, `?search=`, `?sort=-occurred_at`).
- **Rate limiting:** `throttle:60,1` (60 req/min) per authenticated user; stricter on auth endpoints.

---

## 11. Security Rules (Mandatory)

### 11.1 SQL injection prevention
- Use Eloquent and the Query Builder exclusively — both bind parameters automatically.
- **Never** concatenate user input into SQL.
- For any rare raw SQL, use `?` placeholders and bound arrays.
- Never accept user input for `orderBy`, `select`, table names, or column names — whitelist:
  ```php
  $sortable = ['amount', 'occurred_at', 'created_at'];
  $sortBy   = in_array($request->input('sort'), $sortable, true) ? $request->input('sort') : 'occurred_at';
  ```

### 11.2 Authentication
- Breeze for web; Sanctum for API.
- Throttle login: 5 attempts / minute / IP via `RateLimiter::for('login')`.
- Password policy: `Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()`.
- Session cookies: `Secure`, `HttpOnly`, `SameSite=Lax` (set in `config/session.php`).
- Revoke API tokens on logout.

### 11.3 Authorization
- Every model has a Policy. Controllers call `$this->authorize('update', $expense)` or use `can:` middleware.
- Use `Route::scopeBindings()` so `/expenses/{expense}` is auto-scoped to the authenticated user.
- Never trust an ID from the request body — always re-fetch through a user-scoped query.

### 11.4 Input validation & sanitization
- Every request goes through a FormRequest. No exceptions.
- Use type-appropriate rules (`integer`, `uuid`, `date`, `email`, `url`, `in:...`).
- Blade `{{ }}` escapes output. Never use `{!! !!}` with user content.
- For any rich-text field (not in scope today), sanitize via HTMLPurifier.

### 11.5 Mass assignment
- Define `$fillable` (allow-list) on every model. **`$guarded = []` is banned.**
- Pass `$request->validated()`, never `$request->all()`, to create/update.

### 11.6 CSRF, CORS, headers
- CSRF protection on every state-changing web route (Laravel default — don't disable it).
- CORS configured in `config/cors.php` with an explicit allow-list. Never `*` in production.
- Security headers via middleware:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: DENY`
  - `Referrer-Policy: same-origin`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HTTPS only)
  - A baseline `Content-Security-Policy`

### 11.7 Secrets & configuration
- All secrets in `.env`. Never commit `.env`. Always commit `.env.example`.
- Use `config()` in application code; reserve `env()` for `config/*.php` files (env() returns null after config caching).
- Rotate `APP_KEY` only with a documented re-encryption plan.

### 11.8 Rate limiting
- API: `throttle:60,1` per authenticated user.
- Login: 5 attempts / minute / IP.
- Report endpoints: lower limit if expensive (`throttle:20,1`).

### 11.9 Error handling & logging
- `APP_DEBUG=false` in any environment a user can reach.
- Generic error messages externally; full traces in server logs only.
- Never log passwords, tokens, full credit-card numbers, or full PII.
- Use `Log::info()` / `Log::error()` with context arrays, not interpolated strings.

### 11.10 File uploads (when added later)
- Validate both `mimes:` and `mimetypes:`.
- Store outside the webroot (`Storage::disk('private')`).
- Generate randomized filenames; never trust the client filename.

### 11.11 Dependencies
- `composer audit` in CI on every PR.
- `npm audit` in CI on every PR.
- Pin minimum versions; update via PRs, never `composer update` in production deploys.

---

## 12. Testing — After Every Feature, No Exceptions

**Definition: a feature is not done until its tests are written and the whole suite is green.**

### Workflow (run after every feature, every time)

```
php artisan test              # full Pest suite
vendor/bin/phpstan analyse    # static analysis
vendor/bin/pint --test        # style check
```

If any of the three fails, the feature is not done. Fix and re-run.

### Coverage targets for the one-day build

- **Auth flow:** register validation, login throttle, logout invalidates token, password rules.
- **Expense CRUD:** each endpoint, validation failures (422), authorization failures (403/404 — User A cannot touch User B's expenses), happy paths.
- **Reports:** each of the three reports — correct aggregation, empty-month edge case, single-expense case.
- **Repository unit tests:** one test per public method, against the SQLite in-memory connection.
- **Pagination:** asserted on every index endpoint.

### Test conventions

- Pest, `RefreshDatabase`, in-memory SQLite (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`).
- Factories for every model.
- Test names are sentences: `it('returns 403 when accessing another user\'s expense')`.
- Authorization tests are mandatory: explicitly verify User B receives 403 or 404, never 200, when targeting User A's resources.

### Minimum example

```php
it('lists only the authenticated user\'s expenses', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    Expense::factory()->for($user)->count(3)->create();
    Expense::factory()->for($other)->count(2)->create();

    Sanctum::actingAs($user);

    getJson('/api/v1/expenses')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data', 'links', 'meta']);
});
```

---

## 13. Code Quality Standards

- **PSR-12**, enforced by Pint.
- **Strict types**: `declare(strict_types=1);` at top of every PHP file.
- **Type declarations everywhere**: parameter, return, property. Use `void`, `never`, `self`, `static` accurately.
- **`final` by default** on controllers, services, repositories, requests, resources, policies.
- **`readonly` properties** for anything that doesn't mutate after construction.
- **PHPDoc** on every public method whose types aren't obvious (arrays, mixed, callables).
- **No magic numbers** — extract to constants or enum cases.
- **Methods stay short** — target ≤ 30 lines. If you need comments to mark sections, split the method.
- **Comments explain *why*, not *what*.** The code shows what.
- **Banned in committed code:** `dd()`, `dump()`, `var_dump()`, `print_r()`, commented-out code, `TODO`/`FIXME` without an associated issue.

---

## 14. Performance & Scalability

- Eager-load every relation that will be accessed. `Model::preventLazyLoading()` outside production.
- Composite DB indexes for the hot paths:
  - `expenses(user_id, occurred_at)`
  - `expenses(user_id, category_id)`
- Aggregate at the database, never in PHP loops.
- Cache report results (`Cache::remember`) keyed by `user_id + report + period`. Bust on `ExpenseCreated`/`Updated`/`Deleted` events.
- Database transactions around multi-statement writes (`DB::transaction(...)`).
- Use `decimal(12,2)` for money columns. Never floats for currency math.
- Queue any work > 200ms (emails, exports). For the one-day build, the `database` queue driver is fine.

---

## 15. Database Schema (One-Day Scope)

Only the tables needed today. Advanced features (budgets, recurring, attachments) are deferred.

| Table | Columns | Notes |
|---|---|---|
| `users` | `id` uuid, `name`, `email` unique, `password`, `email_verified_at`, `timezone`, `currency`, timestamps | Breeze default + `timezone` + `currency` |
| `categories` | `id` uuid, `name`, `slug` unique, `icon`, `color`, `is_system` boolean, timestamps | Seeded with 12 defaults |
| `expenses` | `id` uuid, `user_id` fk, `category_id` fk, `amount` decimal(12,2), `currency` char(3), `description` string(500), `occurred_at` timestamp, timestamps, soft deletes | Indexed `(user_id, occurred_at)`, `(user_id, category_id, occurred_at)` |
| `personal_access_tokens` | Sanctum default | |
| `jobs`, `failed_jobs`, `cache`, `sessions` | Laravel defaults | `database` driver today |

UUID primary keys everywhere. `decimal(12,2)` for money. Soft deletes on `expenses` so a delete is recoverable.

---

## 16. Docker Policy

**Docker is NOT included by default.** The project runs on a local PHP 8.3 + MySQL 8 stack via `php artisan serve`.

Docker configuration (`docker-compose.yml`, `docker/` folder, Sail, container Makefile targets) is added **only when the user explicitly requests Docker setup**. When that happens:

1. `composer require laravel/sail --dev`
2. `php artisan sail:install`
3. Documentation goes in `docs/docker.md` — never in the main README.
4. The local-PHP workflow must continue to work; Docker is purely additive.

---

## 17. Daily Workflow Discipline

To finish in one day, follow this loop **for every feature**:

1. **Plan the slice** — write down the endpoint, the FormRequest fields, the repository method signature, the Resource shape, the test you'll write. Two minutes, not twenty.
2. **Write the migration + factory** if the schema needs to change.
3. **Write the test first** (or alongside) — at minimum one happy-path, one validation-failure, one authorization-failure.
4. **Implement the repository method.** Eloquent, no joins, eager-load relations.
5. **Implement the FormRequest, controller, Resource.**
6. **Run `php artisan test`.** Green? Continue. Red? Fix before moving on.
7. **Run `vendor/bin/pint` + `vendor/bin/phpstan analyse`.** Fix anything they flag.
8. **Commit** with a Conventional Commit message: `feat(expenses): add monthly report endpoint`.

Never leave a feature half-done to start the next one. Never end the day with a red test.

---

## 18. Definition of Done (Per Feature & Per Day)

A feature is done when **all** are true:

1. Code follows every rule in this document.
2. FormRequest validates every input.
3. Policy authorizes the action.
4. Repository handles persistence — no Eloquent in controller.
5. API Resource shapes the response.
6. Pagination on any list.
7. Tests cover happy path + validation failure + authorization failure.
8. `php artisan test` passes.
9. `vendor/bin/phpstan analyse` passes.
10. `vendor/bin/pint --test` passes.
11. No `dd()`, no `TODO`, no commented-out code, no debug output.

**The day is done when:**
- All in-scope features above are complete and tested.
- The README explains setup, demo credentials, the three reports, and the API endpoints.
- `make setup && php artisan serve` runs the app cleanly from a fresh clone.
- The full test suite is green.
- The repo is pushed.

---

## 19. Quick Reference

| Need to... | Do this | Don't do this |
|---|---|---|
| Validate a request | `FormRequest` | `$request->validate(...)` inline |
| Add a custom rule | Invokable class in `app/Rules/` | Closure rule inline |
| Query the database | Repository method | `Expense::where(...)` in controller |
| Combine two tables | Eager loading (`with`) / `whereHas` | `->join(...)` |
| Run raw SQL | Don't | `DB::raw("... $userInput ...")` |
| Return JSON | API Resource | Return the model |
| List records | `paginate()`, `per_page` ≤ 100 | `all()` or `get()` without limit |
| Authorize | Policy + `$this->authorize()` | Manual `if ($user->id !== ...)` |
| Send an email | Queued Notification or Job | Inline `Mail::send()` in controller |
| Money math | `decimal(12,2)` + value object | `float` arithmetic |
| Add config | `config/*.php` + `.env` | Hardcoded literal |
| Ship a feature | Tests → suite green → commit | "I'll add tests later" |