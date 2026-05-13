# Interview Questions — Expense Tracker Application

A curated set of interview-level questions based on this Laravel 11 + PHP 8.2 expense tracker.
Questions span architecture, logic, security, performance, testing, and code design.

---

## 1. Architecture & Design Patterns

1. **Repository Pattern**
   - What problem does the Repository pattern solve in this application?
   - Why are controllers not allowed to call Eloquent models directly?
   - What is the benefit of binding `ExpenseRepositoryInterface` to `ExpenseRepository` in the service provider instead of instantiating it directly?

2. **Service Layer**
   - What is the responsibility of `ExpenseReportService` and why is it separate from the repository?
   - If you needed to add a new report type, where exactly would you add it and why?

3. **SOLID Principles**
   - Give a concrete example from this codebase where the Single Responsibility Principle is followed.
   - How does the use of interfaces (`ExpenseRepositoryInterface`) satisfy the Dependency Inversion Principle?
   - Where would the Open/Closed Principle be violated if you added a new currency format by editing an existing controller instead of extending?

4. **Dependency Injection**
   - How does Laravel's service container resolve constructor-injected dependencies in controllers?
   - What would break if you called `new ExpenseRepository()` directly in a controller instead of injecting it?

---

## 2. Laravel-Specific Concepts

5. **FormRequests**
   - Why does this app use dedicated `StoreExpenseRequest` / `UpdateExpenseRequest` classes instead of inline `$request->validate()`?
   - What is the difference between `$request->validated()` and `$request->all()`? Why is `validated()` always used here?
   - What does `authorize()` returning `false` do in a FormRequest?

6. **Eloquent ORM**
   - What is an N+1 query problem? How does this application prevent it?
   - Explain `->withQueryString()` on a paginator. When would omitting it break the UI?
   - Why is `Model::preventLazyLoading()` enabled outside production?

7. **Middleware & Route Model Binding**
   - How does `Route::scopeBindings()` protect against IDOR (Insecure Direct Object Reference) attacks?
   - What does `abort_unless($expense !== null, 404)` achieve compared to letting Eloquent throw an exception?

8. **Caching**
   - Explain the cache key strategy used in `ExpenseReportService`. Why does it include `user_id`?
   - What is `Cache::remember()` and how does it differ from `Cache::get()` + `Cache::put()`?
   - When is the cache busted and why is that necessary?
   - Why does `categoryTotalsForRange` use a 5-minute TTL while other reports use a longer TTL?

9. **Soft Deletes**
   - What does soft deleting an expense mean at the database level?
   - How would you query only soft-deleted expenses? What Eloquent method would you use?
   - Why might soft deletes be preferred over hard deletes in a financial application?

---

## 3. Database & Query Design

10. **Indexing**
    - Why are composite indexes created on `(user_id, occurred_at)` and `(user_id, category_id, occurred_at)`?
    - What is a covering index and does either index here qualify?
    - What queries would benefit most from the `(user_id, occurred_at)` index?

11. **Aggregations**
    - Explain the SQL that backs `totalsByCategoryForMonth()`. Why use `SUM()` with `GROUP BY` at the DB level rather than summing in PHP?
    - Why does `totalsByCategoryForRange()` use `whereBetween` with explicit timestamps (`00:00:00` / `23:59:59`) rather than just date strings?

12. **Decimal vs Float**
    - Why is `decimal(12, 2)` used for the `amount` column instead of `float` or `double`?
    - What real-world bug can occur if you store currency values as floats?

13. **Migrations**
    - What is the correct approach when you need to remove a column from a live database that already has data? (as demonstrated by `drop_currency_columns` migration)
    - Why should you never edit an already-run migration file in production?

---

## 4. API Design & Security

14. **REST API**
    - Why are all API routes versioned under `/api/v1`? What happens when you need breaking changes?
    - What HTTP status code should be returned for: a validation failure / unauthorized access / successful deletion?
    - Why are Eloquent models never returned directly from API controllers?

15. **Laravel Sanctum**
    - How does Sanctum differ from Passport for API authentication?
    - How would you revoke a user's API token on logout?
    - What does `auth:sanctum` middleware do when no token is provided?

16. **API Resources**
    - What is the purpose of `ExpenseResource`? What problem does it solve compared to returning `$expense->toArray()`?
    - How does `$this->whenLoaded('category')` work and why is it useful?

17. **Rate Limiting**
    - Why is a stricter rate limit applied to login endpoints compared to general API endpoints?
    - How would you implement per-user rate limiting vs per-IP rate limiting in Laravel?

---

## 5. Security

18. **SQL Injection**
    - Why is raw SQL with string interpolation banned in this project?
    - If you absolutely must use a raw expression, what is the safe way to pass user input?
    - Is `selectRaw('category_id, SUM(amount) as total')` safe? Why?

19. **Mass Assignment**
    - What is a mass assignment vulnerability? How does `$fillable` prevent it?
    - Why is `$guarded = []` considered dangerous?

20. **CSRF**
    - What is a CSRF attack? How does Laravel protect against it by default?
    - Why are API routes (Sanctum token-based) exempt from CSRF protection?

21. **Authorization**
    - What is the difference between authentication and authorization?
    - How does a Policy differ from middleware for authorization?
    - What would happen if the `ExpensePolicy` was missing and a user guessed another user's expense ID?

22. **Password Security**
    - What does `Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()` check?
    - What does "uncompromised" mean in the context of Laravel's password validation rule?

---

## 6. Performance

23. **Eager Loading**
    - What query is executed with `->with('category')` vs without it when iterating 50 expenses?
    - How many queries does `paginateForUser()` execute when it returns 15 expenses? Explain each.

24. **Pagination**
    - Why is `Model::all()` banned in listing endpoints?
    - What is the difference between `paginate()` and `cursorPaginate()`? When would you prefer cursor pagination?

25. **Database Transactions**
    - When should you wrap multiple database writes in a `DB::transaction()`?
    - What happens to partially completed writes if an exception is thrown inside a transaction?

---

## 7. Testing

26. **Pest & RefreshDatabase**
    - What does `RefreshDatabase` do between tests? Why is SQLite in-memory used instead of MySQL for tests?
    - What is the difference between a Feature test and a Unit test in this project?

27. **Authorization Testing**
    - Why is it mandatory to write a test asserting that User B receives a 403/404 when accessing User A's expense?
    - How would you use `Sanctum::actingAs()` in a Pest test?

28. **Factory Usage**
    - What is the benefit of using `Expense::factory()->for($user)->count(3)->create()` over manually inserting records?
    - How would you create an expense factory state for "large amount" expenses (> ₹10,000)?

---

## 8. PHP 8.x & Code Quality

29. **Strict Types**
    - What does `declare(strict_types=1)` do? Give an example of a bug it would catch.
    - What is the difference between `int $id` and no type hint when `declare(strict_types=1)` is active?

30. **`final` & `readonly`**
    - Why are controllers, services, and repositories declared `final`?
    - What does `readonly` on a constructor property mean? What prevents you from reassigning it?

31. **Arrow Functions & Nullsafe Operator**
    - Explain `$monthly->sum(fn($r) => (float)$r->total)`. What does the arrow function capture?
    - What does `$expense->category?->name` do if `category` is `null`?

32. **Named Arguments & Match Expression**
    - Rewrite the following using a `match` expression:
      ```php
      if ($changePct > 0.5) { $direction = 'up'; }
      elseif ($changePct < -0.5) { $direction = 'down'; }
      else { $direction = 'same'; }
      ```

---

## 9. Application Logic

33. **Date Range Filtering**
    - In `DashboardController`, why is the guard `if ($toDate->gt($now->copy()->endOfDay()))` necessary?
    - Why use `subMonthNoOverflow()` instead of `subMonth()` for the "Last Month" preset?
    - Explain what `diffInDays($toDate) + 1` computes and why the `+1` is needed.

34. **Month-over-Month Comparison**
    - Walk through the logic of `monthOverMonthComparison()`. What does "union of both months' category IDs" mean and why is it needed?
    - What does `direction = 'new'` mean in the MoM report and when is it set?

35. **Report Caching Strategy**
    - Why is the MoM comparison report not directly cached in its own key, and instead relies on two cached monthly totals?
    - What is `bustCacheForUser()` doing and when is it called?

36. **CSV & PDF Export**
    - Why is a UTF-8 BOM (`\xEF\xBB\xBF`) prepended to the CSV output?
    - How does `response()->streamDownload()` differ from building the full CSV string in memory and then returning it?

---

## 10. Scenario-Based Questions

37. A user reports that after adding a new expense, the dashboard still shows the old total. What would you investigate first?

38. You need to add a "Notes" field (optional, up to 1000 chars) to expenses. List every file you would need to modify.

39. The `totalsByCategoryForRange` query is slow for users with 10,000+ expenses. What optimizations would you apply?

40. A penetration tester reports that `/api/v1/expenses/123` returns a 200 for any authenticated user, not just the owner. How did this happen and how do you fix it?

41. You want to add a budget feature where users set a monthly limit per category and get warned when they exceed 80%. Describe the database schema changes and which layers of the application you would add/modify.

42. How would you migrate the `database` cache/queue driver to Redis with zero downtime?

---

*Good luck! These questions cover the full stack of this application — from SQL to security to clean PHP.*
