# Interview Questions & Answers — Expense Tracker Application

Laravel 11 · PHP 8.2 · MySQL 8 · Pest · Sanctum · Repository Pattern

---

## 1. Architecture & Design Patterns

---

**Q1. What problem does the Repository pattern solve in this application?**

**A:** The Repository pattern decouples the data-access logic from business logic. Controllers and services never call Eloquent directly — they call repository methods through an interface. This means:
- You can swap the data source (e.g., replace MySQL with an API) without touching controllers.
- Unit tests can swap the real repository with a mock/fake.
- All query logic is in one place, making it easy to find, review, and optimise.

In this app, `ExpenseRepositoryInterface` defines the contract and `ExpenseRepository` provides the Eloquent implementation. The binding is registered in `RepositoryServiceProvider`.

---

**Q2. Why is `ExpenseRepositoryInterface` bound in a service provider instead of just using `new ExpenseRepository()` directly?**

**A:** Binding through the service container enables:
1. **Dependency Inversion** — code depends on the abstraction, not the concrete class.
2. **Testability** — in tests you can bind a fake: `$this->app->bind(ExpenseRepositoryInterface::class, FakeExpenseRepository::class)`.
3. **Single point of change** — if you rename or replace the implementation, you change only the service provider binding, not every class that uses it.

Using `new ExpenseRepository()` directly would hardcode the dependency and make the codebase rigid.

---

**Q3. What is the role of `ExpenseReportService` and why is it not merged into the repository?**

**A:** `ExpenseReportService` orchestrates reporting logic — it calls repository methods, applies caching, and in the case of `monthOverMonthComparison()`, combines two separate data sets into a derived result. 

The repository's job is pure persistence: execute a query, return a result. The service's job is business logic: decide *which* queries to run, cache results, and transform data. Merging them would violate the Single Responsibility Principle — the repository would have to know about caching TTLs and business rules.

---

**Q4. Give a real example from this codebase where Open/Closed Principle is applied.**

**A:** `ExpenseReportService` is open for extension, closed for modification. To add a new report type (e.g., weekly totals), you add a new method `weeklyTotals()` without touching the existing `monthlyCategoryTotals()`, `monthlyDailyAverage()`, or `lifetimeCategoryTotals()` methods. Nothing already working is disturbed.

Similarly, adding a new repository method like `totalsByTagForRange()` only requires adding it to the interface and implementation — existing methods remain untouched.

---

**Q5. How does Dependency Injection work for controllers in this app?**

**A:** Laravel's service container automatically resolves constructor type-hints. When a request hits `ExpenseController`, Laravel sees:

```php
public function __construct(
    private readonly ExpenseRepositoryInterface $expenses,
    private readonly CategoryRepositoryInterface $categories,
    private readonly ExpenseReportService $reports,
) {}
```

It looks up each type-hint in the container, resolves the bound concrete class, and injects it. No manual instantiation is needed. This is called **automatic dependency resolution** or **autowiring**.

---

## 2. Laravel-Specific Concepts

---

**Q6. What is the difference between `$request->validated()` and `$request->all()`?**

**A:**
- `$request->all()` returns **everything** the client sent — including fields not in the rules, which could include unexpected or malicious keys.
- `$request->validated()` returns **only** fields that passed validation rules — nothing extra.

Using `$request->all()` with `create()` or `update()` risks **mass assignment** if the model's `$fillable` is not perfectly maintained. `$request->validated()` is the safe default because it acts as a whitelist by definition.

---

**Q7. Why does the app use dedicated `StoreExpenseRequest` classes instead of inline `$request->validate()`?**

**A:** Several reasons:
1. **Single Responsibility** — validation logic lives in its own class, not scattered in controller methods.
2. **Reusability** — the same request class can be reused across web and API controllers.
3. **Testability** — FormRequests can be unit-tested independently.
4. **Authorization** — the `authorize()` method handles policy checks before the controller body runs.
5. **Clean controllers** — controllers stay under 15 lines per action, as required by the engineering charter.

---

**Q8. What is an N+1 query problem? How does this application prevent it?**

**A:** An N+1 problem occurs when you load a collection of N records and then execute one additional query for each record to fetch a relationship. Example without eager loading:

```php
// 1 query to get 15 expenses, then 15 queries to load each category = 16 queries
foreach ($expenses as $expense) {
    echo $expense->category->name; // triggers a query each time
}
```

This app prevents it by using `->with('category')` in every repository method that returns expenses:

```php
return $this->model->newQuery()
    ->where('user_id', $userId)
    ->with('category') // loads all categories in 1 extra query
    ->paginate($perPage);
```

Additionally, `Model::preventLazyLoading(!app()->isProduction())` is enabled — any lazy load outside production throws an exception, catching N+1 bugs before they reach users.

---

**Q9. Explain `->withQueryString()` on a paginator.**

**A:** `withQueryString()` appends the current URL's query parameters to all pagination links. Without it, navigating to page 2 would lose applied filters:

```
Without: /expenses?page=2           ← loses ?category_id=3&from=2026-01-01
With:    /expenses?category_id=3&from=2026-01-01&page=2  ← filters preserved
```

This is critical for the expenses index where users filter by category, date range, or search term and then paginate through results.

---

**Q10. How does `Cache::remember()` work and how is it used in `ExpenseReportService`?**

**A:** `Cache::remember($key, $ttl, $callback)` checks if the key exists in the cache:
- If **yes** — returns the cached value immediately (no DB query).
- If **no** — executes the callback, stores the result under the key for `$ttl` seconds, then returns it.

In `ExpenseReportService`:
```php
$key = "report:monthly-category:{$userId}:{$year}:{$month}";
return Cache::remember($key, config('constants.reports.cache_ttl_seconds'), fn () =>
    $this->expenses->totalsByCategoryForMonth($userId, $year, $month)
);
```

The key includes `userId` so users never see each other's cached data. TTL is 3600 seconds (1 hour) for scheduled reports, and 300 seconds (5 min) for interactive range queries because they can't be easily enumerated for cache busting.

---

**Q11. When and why is `bustCacheForUser()` called?**

**A:** It's called after every expense `create`, `update`, and `delete`. Since report totals are derived from expense data, any modification makes the cached aggregates stale. The method deletes:
- `report:lifetime-category:{userId}`
- `report:monthly-category:{userId}:{year}:{month}` for the past N months
- `report:daily-average:{userId}:{year}:{month}` for the past N months

Note: range-based cache keys (`report:range-category:*`) are **not** busted here because there are infinite possible date ranges — they use a short 5-minute TTL instead, accepting brief staleness for interactive queries.

---

**Q12. What do soft deletes mean at the database level and why are they used for expenses?**

**A:** Soft deletes add a `deleted_at` timestamp column. When you call `$expense->delete()`, Laravel sets `deleted_at = now()` instead of issuing a `DELETE` statement. Eloquent automatically adds `WHERE deleted_at IS NULL` to all queries, so soft-deleted records are invisible by default.

For a financial application, soft deletes are preferred because:
1. **Audit trail** — you can see what was deleted and when.
2. **Recovery** — a mistakenly deleted expense can be restored with `$expense->restore()`.
3. **Reporting integrity** — historical reports can optionally include deleted records with `->withTrashed()`.

---

## 3. Database & Query Design

---

**Q13. Why are composite indexes created on `(user_id, occurred_at)` and `(user_id, category_id, occurred_at)`?**

**A:** Almost every query in this app filters by `user_id` first — a user only sees their own data. Adding `occurred_at` to the index supports efficient date-range queries (the most common filter). The database can seek directly to a user's records within a date range without a full table scan.

The second index `(user_id, category_id, occurred_at)` further optimises report queries that group or filter by both user and category simultaneously, such as `totalsByCategoryForMonth()`.

The **order matters**: `user_id` must be the leftmost column because queries always filter by user first.

---

**Q14. Why is `decimal(12, 2)` used for amounts instead of `float`?**

**A:** Floating-point types (`FLOAT`, `DOUBLE`) use binary representation and cannot exactly represent all decimal fractions:

```php
echo 0.1 + 0.2; // 0.30000000000000004 — not 0.3
```

In financial data, this leads to rounding errors that accumulate over summations. `decimal(12, 2)` stores exact decimal values in the database with up to 12 digits total and exactly 2 decimal places. MySQL performs arithmetic on `DECIMAL` values exactly, with no floating-point drift.

---

**Q15. Why does `totalsByCategoryForRange()` use explicit timestamps in `whereBetween`?**

**A:**
```php
->whereBetween('occurred_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
```

The `occurred_at` column is a `TIMESTAMP`, not a `DATE`. A date string like `'2026-05-13'` implicitly becomes `'2026-05-13 00:00:00'`, which would **exclude** all expenses on the last day that occurred after midnight (i.e., all of them). Appending `23:59:59` ensures the full last day is included.

---

**Q16. Why should you never edit an already-run migration in production?**

**A:** Laravel tracks which migrations have run in the `migrations` table by filename. If you edit a migration that has already been recorded as run, Laravel won't re-run it — so your change is silently ignored on existing environments. Other developers who pull your changes and run `migrate` also won't see the edit because their migration table already has that filename recorded.

The correct approach is always to create a **new migration** that alters the existing table, as done here with `drop_currency_columns.php`.

---

## 4. API Design & Security

---

**Q17. What HTTP status codes should be returned for each scenario?**

**A:**

| Scenario | Code |
|---|---|
| Successful GET / update | `200 OK` |
| Resource created | `201 Created` |
| Successful delete | `204 No Content` |
| Validation failure | `422 Unprocessable Entity` |
| Unauthenticated (no token) | `401 Unauthorized` |
| Authenticated but forbidden | `403 Forbidden` |
| Resource not found | `404 Not Found` |
| Too many requests | `429 Too Many Requests` |
| Server error | `500 Internal Server Error` |

---

**Q18. How does Laravel Sanctum authenticate API requests?**

**A:** The client sends a bearer token in the `Authorization` header:
```
Authorization: Bearer 1|abc123xyz...
```

Sanctum hashes the token and looks it up in the `personal_access_tokens` table. If found and not expired, it resolves the associated user and sets them as the authenticated user for the request. The `auth:sanctum` middleware rejects requests with missing or invalid tokens with a `401` response.

---

**Q19. What does `$this->whenLoaded('category')` do in `ExpenseResource`?**

**A:** It conditionally includes the relationship in the JSON response **only if it was eager-loaded**. If the category wasn't loaded (e.g., a lightweight list endpoint that skips `->with('category')`), the key is omitted from the response entirely — no extra query is triggered and no `null` is returned unexpectedly.

This prevents N+1 queries from happening silently inside resources.

---

**Q20. Why are API routes versioned under `/api/v1`?**

**A:** Versioning isolates breaking changes. If you need to change a response shape, rename a field, or alter validation rules in a way that would break existing API clients, you create `/api/v2` routes with the new behaviour. Clients on `/api/v1` continue working unaffected until they migrate. Without versioning, any breaking change instantly breaks all existing integrations.

---

## 5. Security

---

**Q21. What is SQL injection and how does this app prevent it?**

**A:** SQL injection is when user-supplied input is embedded directly into a SQL string, allowing an attacker to manipulate the query:

```php
// VULNERABLE — never do this
DB::select("SELECT * FROM expenses WHERE user_id = $userId");
```

This app prevents it by:
1. Using Eloquent's query builder exclusively — all values are bound as parameters automatically.
2. Banning raw SQL in application code. The only `selectRaw` calls contain **no user input** (`'category_id, SUM(amount) as total'` is a hardcoded string).
3. Whitelisting sortable columns:
```php
$sortable = ['amount', 'occurred_at', 'created_at'];
$sortBy = in_array($request->input('sort'), $sortable, true) ? $request->input('sort') : 'occurred_at';
```

---

**Q22. What is a mass assignment vulnerability?**

**A:** Mass assignment occurs when user-supplied data is passed directly to `create()` or `update()` without filtering. An attacker could inject extra fields:

```http
POST /expenses
{"amount": 50, "description": "lunch", "user_id": 999}
```

If `user_id` is in `$fillable` and you use `$request->all()`, the attacker sets their expense to belong to user 999. This app prevents it two ways:
1. Every model has an explicit `$fillable` allow-list.
2. `$request->validated()` only passes fields that have validation rules — `user_id` is never in the rules (it's always set server-side from `$request->user()->id`).

---

**Q23. What is CSRF and how does Laravel protect against it?**

**A:** Cross-Site Request Forgery (CSRF) tricks a logged-in user's browser into submitting a malicious form on a different website. Laravel protects against it by issuing a unique token per session and validating it on every state-changing request (`POST`, `PUT`, `PATCH`, `DELETE`). The `@csrf` Blade directive injects a hidden `_token` field in forms.

API routes using Sanctum bearer tokens are exempt because CSRF exploits cookies — bearer tokens in headers cannot be sent cross-origin by a browser's automatic form submission.

---

**Q24. What does `Route::scopeBindings()` protect against?**

**A:** It prevents **Insecure Direct Object Reference (IDOR)**. Without scope bindings, a URL like `/users/1/expenses/99` would load expense 99 regardless of whether it belongs to user 1. With `scopeBindings()`, Laravel automatically adds a `WHERE user_id = 1` constraint when resolving the nested `{expense}` binding — so if expense 99 belongs to user 2, the route returns 404.

---

**Q25. What does `Password::uncompromised()` check?**

**A:** It queries the [Have I Been Pwned](https://haveibeenpwned.com/Passwords) API (using k-anonymity — only the first 5 characters of the SHA-1 hash are sent) to check whether the password has appeared in a known data breach. If the password is in a breach database, it's rejected — even if it meets all other complexity requirements. This stops users from choosing passwords that attackers already have in their dictionaries.

---

## 6. Performance

---

**Q26. How many database queries does a paginated expenses list page execute?**

**A:** Exactly **3 queries**:
1. `SELECT COUNT(*) FROM expenses WHERE user_id = ?` — to calculate total pages for the paginator.
2. `SELECT * FROM expenses WHERE user_id = ? ORDER BY created_at DESC LIMIT 15 OFFSET 0` — the actual page of expenses.
3. `SELECT * FROM categories WHERE id IN (?, ?, ...)` — the eager-loaded categories for all 15 expenses in one batch query.

Without eager loading, query 3 would become 15 separate queries (N+1).

---

**Q27. What is the difference between `paginate()` and `cursorPaginate()`?**

**A:**

| | `paginate()` | `cursorPaginate()` |
|---|---|---|
| Navigation | Page numbers (1, 2, 3…) | Cursor token (opaque) |
| SQL | `LIMIT x OFFSET y` | `WHERE id > ?` |
| Performance on large data | Degrades (high OFFSET is slow) | Constant — always fast |
| Jump to page | Yes | No — only next/previous |
| Count query | Yes (extra query) | No |

`cursorPaginate()` is recommended for large datasets (exports, infinite scroll). `paginate()` is used for standard UI listings where page numbers are needed.

---

**Q28. When should you use `DB::transaction()`?**

**A:** Whenever you have two or more write operations that must either all succeed or all fail together (atomicity). Example: creating an expense and logging an audit entry. If the audit insert fails, the expense creation should roll back too.

```php
DB::transaction(function () use ($data) {
    $expense = $this->expenses->create($data);
    $this->auditLog->record('expense.created', $expense->id);
});
```

Without a transaction, a partial failure leaves the database in an inconsistent state.

---

## 7. Testing

---

**Q29. Why is SQLite in-memory used for tests instead of MySQL?**

**A:**
1. **Speed** — SQLite in-memory runs entirely in RAM with no disk I/O, making the test suite significantly faster.
2. **Isolation** — each test run gets a fresh database; no shared state between runs or developers.
3. **No setup** — no MySQL server needed in CI pipelines; SQLite is built into PHP.

The trade-off is that SQLite doesn't support all MySQL features (e.g., full-text search, some JSON functions). For those, you'd use a real MySQL test database.

---

**Q30. What does `RefreshDatabase` do in Pest tests?**

**A:** It wraps each test in a database transaction and rolls it back after the test completes (or re-runs migrations for the first test). This means every test starts with a clean, empty database regardless of what previous tests did — no test can pollute another's data.

```php
uses(RefreshDatabase::class);

it('creates an expense', function () {
    $user = User::factory()->create();
    // ... test runs against a fresh DB
    // After test: DB is rolled back automatically
});
```

---

**Q31. Why must you write an authorization test asserting User B gets 403/404 for User A's expense?**

**A:** Authorization bugs are among the most critical security vulnerabilities — they allow data leaks between users. A test that only checks the happy path (owner can view their expense) does **not** verify that the policy is actually enforced. An accidental removal of `$this->authorize()` from the controller would pass the happy-path test but fail the cross-user test, catching the regression before it ships.

```php
it('returns 404 when accessing another user\'s expense', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $expense = Expense::factory()->for($owner)->create();

    Sanctum::actingAs($attacker);

    getJson("/api/v1/expenses/{$expense->id}")->assertNotFound();
});
```

---

**Q32. What is the benefit of using factories over manually inserting test data?**

**A:**
1. **Defaults** — factories provide sensible defaults; you only override what matters for the specific test.
2. **Relationships** — `Expense::factory()->for($user)->create()` automatically wires up the `user_id` foreign key.
3. **Maintainability** — when the schema changes, you update the factory once rather than dozens of test fixtures.
4. **Readability** — `User::factory()->count(3)->create()` is clearer than three manual `DB::table('users')->insert([...])` calls.

---

## 8. PHP 8.x & Code Quality

---

**Q33. What does `declare(strict_types=1)` do? Give an example of a bug it catches.**

**A:** It enables strict type checking for scalar type hints in that file. Without it, PHP silently coerces types:

```php
function add(int $a, int $b): int {
    return $a + $b;
}

add("5", "3"); // Without strict_types: works, returns 8
               // With strict_types=1: TypeError thrown
```

In this app, if a controller accidentally passed a string `"5"` to a repository method typed `int $userId`, strict types would throw a `TypeError` immediately instead of silently producing a wrong query.

---

**Q34. Why are controllers, services, and repositories declared `final`?**

**A:** `final` prevents inheritance, which:
1. **Enforces composition over inheritance** — if you need new behaviour, inject a collaborator or create a new class. Don't extend and override.
2. **Makes contracts explicit** — the only way to substitute a dependency is through its interface, not by subclassing.
3. **Prevents accidental breakage** — a subclass that overrides a method incorrectly can introduce subtle bugs that are hard to trace.

If you genuinely need to extend behaviour, the correct pattern is to use the interface and create a new implementation.

---

**Q35. What does `readonly` on a constructor property mean?**

**A:** A `readonly` property can be written exactly once (during construction) and never reassigned afterward. Any attempt to modify it after construction throws an `Error`:

```php
final class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(private readonly Expense $model) {}

    public function someMethod(): void
    {
        $this->model = new Expense(); // Error: Cannot modify readonly property
    }
}
```

This guarantees that injected dependencies remain stable throughout the object's lifetime — a form of immutability for constructor arguments.

---

**Q36. Explain the nullsafe operator `?->` used in the views.**

**A:** The nullsafe operator short-circuits a method/property chain if any step returns `null`, instead of throwing a fatal error:

```php
$expense->category?->name
// Equivalent to:
$expense->category !== null ? $expense->category->name : null
```

In views, an expense might have a `null` category (if the category was deleted). Without `?->`, accessing `->name` on null would crash the page. With it, null is returned gracefully and the view can display a fallback like `'Unknown'`.

---

## 9. Application Logic

---

**Q37. Why is `subMonthNoOverflow()` used instead of `subMonth()` for the "Last Month" preset?**

**A:** `subMonth()` can overflow on months with fewer days than the current month. On March 31, `subMonth()` would try to create February 31 — which doesn't exist — and silently rolls over to March 3 (losing the correct month). `subMonthNoOverflow()` clamps to the last valid day of the target month (February 28/29), returning the correct result every time.

---

**Q38. Walk through the `monthOverMonthComparison()` logic.**

**A:**
1. Fetch cached category totals for the **current** month and **previous** month (two separate cache reads, potentially zero DB queries if warm).
2. Create lookup maps keyed by `category_id` for O(1) access.
3. Build the **union** of all category IDs from both months — this ensures:
   - Categories that spent in the current month but not last month appear (direction: `'new'`).
   - Categories that spent last month but not this month still appear (direction: `'down'` or `'same'` with 0 current spend).
4. For each category ID, compute `change_pct` and `direction`:
   - If previous > 0: `change_pct = ((current - previous) / previous) * 100`
   - If previous = 0 and current > 0: direction = `'new'` (no percentage makes sense)
5. Sort by `this_month` descending and return.

---

**Q39. Why does `diffInDays($toDate) + 1` need the `+1` for the daily average?**

**A:** `diffInDays` counts the **difference** between two dates — it counts the gaps, not the endpoints. For a single day (same from and to), `diffInDays` returns 0. Adding 1 makes it inclusive:

```
From: May 1 → To: May 1  → diffInDays = 0, +1 = 1 day  ✓
From: May 1 → To: May 3  → diffInDays = 2, +1 = 3 days ✓
```

Without `+1`, the daily average for a single day would divide by zero (guarded by `max(1, ...)`), and multi-day averages would be inflated.

---

**Q40. Why is a UTF-8 BOM prepended to the CSV export?**

**A:** When a user opens a CSV in Microsoft Excel, Excel uses the byte-order mark (`\xEF\xBB\xBF`) to detect UTF-8 encoding. Without it, Excel defaults to the system's ANSI encoding, which corrupts non-ASCII characters like `₹` (the Rupee symbol) or accented characters in descriptions. The BOM tells Excel explicitly: "this file is UTF-8" — ensuring symbols render correctly without any manual import configuration.

---

## 10. Scenario-Based Questions

---

**Q41. After adding a new expense, the dashboard still shows the old total. What do you investigate?**

**A:**
1. **Check if `bustCacheForUser()` is called** in the `store()` method — it should be called after every successful `create()`. If missing, cached report totals are never invalidated.
2. **Check the cache driver** — if using the `database` driver, inspect the `cache` table to see if the old key is still there and what its expiry is.
3. **Check the cache key** — if the dashboard uses `categoryTotalsForRange` (range-based queries), those keys are NOT busted by `bustCacheForUser()`. They rely on the 5-minute TTL. A quick fix would be to also bust range keys, or accept the 5-minute staleness.
4. **Check that the test suite covers this** — there should be a test asserting the dashboard total changes after a new expense is created.

---

**Q42. A pen-tester reports that `/api/v1/expenses/99` returns 200 for any authenticated user, not just the owner. What happened and how do you fix it?**

**A:**
**Root Cause:** The `show()` method in the API controller is likely calling `Expense::find($id)` instead of `$this->expenses->findForUser($userId, $id)`. This fetches the expense globally with no user scope.

**Fix:**
```php
// BROKEN
public function show(string $id): ExpenseResource {
    $expense = Expense::find($id); // no user scope!
    return new ExpenseResource($expense);
}

// FIXED
public function show(string $id): ExpenseResource {
    $expense = $this->expenses->findForUser(auth()->id(), (int) $id);
    abort_unless($expense !== null, 404);
    $this->authorize('view', $expense);
    return new ExpenseResource($expense->load('category'));
}
```

**Prevention:**
- Always use `findForUser()` (which scopes by `user_id`) — never `find()` in user-scoped contexts.
- Write a mandatory authorization test: User B must receive 404 for User A's expense.
- Enable `Route::scopeBindings()` for nested routes as a secondary safety net.

---

**Q43. You need to add an optional "Notes" field (up to 1000 chars) to expenses. List every file to modify.**

**A:**

| File | Change |
|---|---|
| `database/migrations/new_migration.php` | Add `$table->text('notes')->nullable()` |
| `app/Models/Expense.php` | Add `'notes'` to `$fillable` |
| `database/factories/ExpenseFactory.php` | Add `'notes' => null` or `fake()->sentence()` |
| `app/Http/Requests/Expense/StoreExpenseRequest.php` | Add `'notes' => ['nullable', 'string', 'max:1000']` |
| `app/Http/Requests/Expense/UpdateExpenseRequest.php` | Add `'notes' => ['sometimes', 'nullable', 'string', 'max:1000']` |
| `app/Http/Resources/ExpenseResource.php` | Add `'notes' => $this->notes` |
| `resources/views/expenses/create.blade.php` | Add textarea for notes |
| `resources/views/expenses/edit.blade.php` | Add pre-filled textarea for notes |
| `resources/views/expenses/index.blade.php` | Optionally show notes in table |
| `resources/views/expenses/pdf.blade.php` | Optionally include notes in PDF |
| `tests/Feature/Api/ExpenseTest.php` | Add notes to create/update test payloads |

---

**Q44. The `totalsByCategoryForRange` query is slow for users with 50,000+ expenses. How do you optimise it?**

**A:**

1. **Verify the index is being used** — run `EXPLAIN` on the query. The `(user_id, occurred_at)` index should be used; if not, check for implicit type casts.

2. **Add a more specific composite index**:
   ```sql
   CREATE INDEX idx_expenses_user_cat_date ON expenses(user_id, category_id, occurred_at, amount);
   ```
   This is a **covering index** — all columns the query needs (`user_id`, `category_id`, `occurred_at` for filtering, `amount` for `SUM`) are in the index. MySQL never needs to touch the main table rows.

3. **Extend the cache TTL** — range queries are already cached for 5 minutes. For less interactive use cases, increase to 15–30 minutes.

4. **Pre-aggregate with a summary table** — a background job populates a `expense_daily_summaries(user_id, category_id, date, total)` table. Range queries then hit this small summary table instead of the full expenses table.

5. **Partition the table** — partition `expenses` by `occurred_at` (range partitioning by year/month). Queries with date filters only scan the relevant partitions.

---

**Q45. How would you migrate the `database` queue/cache driver to Redis with zero downtime?**

**A:**

1. **Provision Redis** — set up a Redis instance (local, ElastiCache, Redis Cloud, etc.).

2. **Add the connection** in `config/database.php` under `redis` (Laravel includes this by default).

3. **Update `.env`** on production:
   ```
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=database   # keep sessions on DB until verified
   REDIS_HOST=your-redis-host
   ```

4. **Deploy** — at this point new cache writes go to Redis. Old cache keys in the DB are simply never read again and expire naturally.

5. **Drain the database queue** — wait for all existing `jobs` table jobs to process before switching `QUEUE_CONNECTION=redis`, or run both workers briefly during the transition.

6. **Migrate sessions last** — sessions are the most user-impacting. Switch `SESSION_DRIVER=redis` in a separate deploy after confirming Redis is stable. Existing DB sessions expire naturally; users just need to log in once.

7. **No code changes required** — Laravel's cache, queue, and session abstractions mean the application code is identical regardless of driver.

---

*These questions cover the full engineering surface of this application — from SQL indexes to PHP type safety to production incident response.*
