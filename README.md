# Expense Tracker

A production-ready personal finance web application built with Laravel 12. Track your daily expenses, organise them by category, and visualise your spending through three detailed reports — all secured behind a full authentication system with a REST API.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack & Versions](#tech-stack--versions)
- [Project Setup Instructions](#project-setup-instructions)
- [Docker Setup](#docker-setup)
- [Configuration Details](#configuration-details)
- [Features](#features)
- [How the Project Works](#how-the-project-works)
- [API Reference](#api-reference)
- [Development Guidelines](#development-guidelines)
- [Additional Notes](#additional-notes)

---

## Project Overview

Expense Tracker is a full-stack web application that lets authenticated users:

- Record and manage personal expenses with descriptions, amounts, dates, and categories
- Browse and filter expenses with pagination
- View three types of spending reports with charts
- Access all data programmatically through a versioned REST API secured with Sanctum tokens

The application follows clean architecture principles: Repository pattern for all data access, FormRequest validation on every write endpoint, Policy-based authorisation, and API Resources shaping every JSON response.

---

## Tech Stack & Versions

| Layer | Technology | Version |
|---|---|---|
| **Language** | PHP | 8.2.x |
| **Framework** | Laravel | 12.x |
| **Auth** | Laravel Breeze + Sanctum | 2.4 / 4.3 |
| **Database** | MySQL | 8.0 |
| **Frontend** | Blade + Alpine.js | 3.x |
| **CSS** | Tailwind CSS | 3.x |
| **Bundler** | Vite | 7.x |
| **HTTP Client** | Axios | 1.x |
| **Node.js** | Node.js | 20+ |
| **Package Manager** | npm | 11.x |
| **Testing** | Pest PHP | 3.x |
| **Static Analysis** | Larastan | 3.x |
| **Code Style** | Laravel Pint | 1.x |
| **Charts** | Chart.js | CDN |

---

## Project Setup Instructions

### Prerequisites

Make sure the following are installed on your machine before proceeding:

- **PHP 8.2+** with extensions: `pdo_mysql`, `mbstring`, `exif`, `bcmath`, `gd`, `zip`
- **Composer 2**
- **MySQL 8.0**
- **Node.js 20+** and **npm**

---

### 1. Clone the Repository

```bash
git clone https://github.com/nithinpp1998/expense-tracker.git
cd expense-tracker
```

---

### 2. Install PHP Dependencies

```bash
composer install
```

---

### 3. Install Node Dependencies

```bash
npm install
```

---

### 4. Environment Setup

Copy the example environment file and open it for editing:

```bash
cp .env.example .env
```

Update the following values in `.env`:

```env
APP_NAME="Expense Tracker"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_tracker
DB_USERNAME=root
DB_PASSWORD=
```

---

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

### 6. Database Setup

Create the database in MySQL:

```sql
CREATE DATABASE expense_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run all migrations:

```bash
php artisan migrate
```

---

### 7. Seed Demo Data

Seeds 12 system categories and a demo user with ~90 days of sample expenses:

```bash
php artisan db:seed
```

**Demo credentials:**

| Field | Value |
|---|---|
| Email | `demo@example.com` |
| Password | `password` |

---

### 8. Build Frontend Assets

For development (with hot reload):

```bash
npm run dev
```

For production (compiled & minified):

```bash
npm run build
```

---

### 9. Run the Application

```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

### 10. Run Tests

```bash
php artisan test
```

> **Note:** Tests use a separate database `expense_tracker_test`. Create it before running:
> ```sql
> CREATE DATABASE expense_tracker_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
> ```

---

## Docker Setup

The application includes a full Docker configuration with three services: PHP-FPM, Nginx, and MySQL.

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running

---

### 1. Copy the Docker Environment File

```bash
cp .env.docker .env
```

The `.env.docker` file is pre-configured with Docker-specific settings (`DB_HOST=db`, port `8080`, etc.).

---

### 2. Build and Start Containers

```bash
docker compose up --build -d
```

This will:
- Build the PHP-FPM image (with Node for frontend assets)
- Start the Nginx webserver on port `8080`
- Start MySQL on host port `3307`
- Run the entrypoint script which: generates `APP_KEY`, waits for the database, runs migrations, and seeds demo data

---

### 3. Access the Application

Open your browser at: **http://localhost:8080**

---

### Useful Docker Commands

```bash
# View running containers
docker compose ps

# View application logs
docker compose logs -f app

# Stop all containers
docker compose down

# Stop and remove all data (including database volume)
docker compose down -v

# Run artisan commands inside the container
docker compose exec app php artisan migrate:fresh --seed

# Open a shell inside the app container
docker compose exec app bash
```

---

### Docker Services

| Service | Container Name | Port (Host → Container) |
|---|---|---|
| PHP-FPM (app) | expense-tracker-app | — |
| Nginx | expense-tracker-nginx | `8080 → 80` |
| MySQL | expense-tracker-db | `3307 → 3306` |

---

## Configuration Details

### Required Environment Variables

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `Expense Tracker` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_KEY` | Encryption key (auto-generated) | — |
| `APP_DEBUG` | Show detailed errors | `true` |
| `APP_URL` | Full URL of the application | `http://localhost:8000` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host (`db` in Docker) | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `expense_tracker` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | _(empty)_ |
| `SESSION_DRIVER` | Session storage driver | `database` |
| `CACHE_STORE` | Cache driver | `database` |
| `QUEUE_CONNECTION` | Queue driver | `database` |

### Docker-Specific Variables

| Variable | Description | Default |
|---|---|---|
| `APP_PORT` | Host port for Nginx | `8080` |
| `DB_FORWARD_PORT` | Host port for MySQL | `3307` |
| `DB_ROOT_PASSWORD` | MySQL root password | `rootsecret` |

---

## Features

### Authentication
- User registration with email and password
- Login and logout
- Password reset via email link
- Session-based authentication for web; token-based for API (Sanctum)
- Throttled login: 5 attempts per minute per IP

### Expense Management
- Create, read, update, and delete expenses
- Fields: description, amount (decimal), date, category, currency
- Filterable list: by category, date range, keyword search
- Paginated results (default 15 per page, max 100)
- Export expenses

### Category Management
- Full CRUD for custom categories
- 12 built-in system categories (seeded)
- Toggle categories active/inactive
- Each category has a name, slug, and colour

### Reports
| Report | Description |
|---|---|
| **Monthly Category** | Doughnut chart + breakdown table of spending per category for a selected month |
| **Daily Average** | Average daily spend for a selected month, calculated as total ÷ days in month |
| **Lifetime Totals** | All-time spending per category with percentage share |

### REST API (v1)
- Full expense CRUD: `GET/POST/PUT/PATCH/DELETE /api/v1/expenses`
- Category listing: `GET /api/v1/categories`
- All three reports as JSON endpoints
- Sanctum bearer token authentication
- Rate limiting: 60 req/min general, 20 req/min on report endpoints

### Dashboard
- Summary stat cards: total expenses, total categories, this-month spend, daily average
- Bar chart of spending over the last 30 days
- Top categories doughnut chart
- Recent expenses table

### Security
- CSRF protection on all state-changing web routes
- Security headers middleware: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`
- Strict Policy-based authorisation — users can only access their own data
- All user input validated through FormRequest classes
- No raw SQL — Eloquent ORM with bound parameters throughout

---

## How the Project Works

### Request Lifecycle (Web)

```
Browser Request
    │
    ▼
routes/web.php          ← Route matched, auth middleware applied
    │
    ▼
FormRequest             ← Input validated (rules, types, existence checks)
    │
    ▼
Policy                  ← Authorization checked (is this user allowed?)
    │
    ▼
Controller              ← Thin: delegates to Repository or Service
    │
    ├──▶ Repository     ← All database queries live here (Eloquent, no joins)
    │
    └──▶ Service        ← Report aggregations with Cache::remember
    │
    ▼
Blade View              ← Data rendered server-side with {{ }} escaping
```

### Request Lifecycle (API)

```
API Request (Bearer Token)
    │
    ▼
routes/api.php          ← Sanctum auth + throttle middleware
    │
    ▼
FormRequest             ← Input validated
    │
    ▼
Policy                  ← Authorization checked
    │
    ▼
Controller              ← Delegates to Repository
    │
    ▼
API Resource            ← Shapes the JSON response (never returns raw model)
    │
    ▼
JSON Response           ← With data / links / meta (paginated)
```

### User Flow

1. **Register** at `/register` → account created, redirected to dashboard
2. **Dashboard** shows summary stats, charts, and recent expenses
3. **Add Expense** → fill in description, amount, date, category → saved to database
4. **Browse Expenses** → filterable, searchable, paginated list at `/expenses`
5. **Edit / Delete** → inline from the expenses list
6. **View Reports** → navigate to Reports menu → select month/year → view chart + breakdown
7. **API Access** → POST to `/api/v1/login` with credentials → receive token → use as `Authorization: Bearer {token}` header

---

## API Reference

### Authentication

All API endpoints require a Sanctum token. Log in to obtain one:

```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "demo@example.com",
  "password": "password"
}
```

Use the returned token in subsequent requests:

```http
Authorization: Bearer {token}
```

### Endpoints

| Method | URI | Description |
|---|---|---|
| `GET` | `/api/v1/expenses` | List expenses (paginated, filterable) |
| `POST` | `/api/v1/expenses` | Create an expense |
| `PUT/PATCH` | `/api/v1/expenses/{id}` | Update an expense |
| `DELETE` | `/api/v1/expenses/{id}` | Delete an expense |
| `GET` | `/api/v1/categories` | List all categories |
| `GET` | `/api/v1/categories/{id}` | Get a single category |
| `GET` | `/api/v1/reports/monthly-category` | Monthly per-category totals |
| `GET` | `/api/v1/reports/monthly-average` | Monthly daily average |
| `GET` | `/api/v1/reports/lifetime` | Lifetime per-category totals |

### Query Parameters (Expenses)

| Parameter | Type | Description |
|---|---|---|
| `category_id` | integer | Filter by category |
| `from` | date | Start date (`YYYY-MM-DD`) |
| `to` | date | End date (`YYYY-MM-DD`) |
| `search` | string | Keyword search in description |
| `per_page` | integer | Results per page (max 100) |

---

## Development Guidelines

### Coding Standards

- **Strict types**: `declare(strict_types=1);` at the top of every PHP file
- **PSR-12** style, enforced by Laravel Pint (`vendor/bin/pint`)
- **`final`** keyword on all controllers, services, repositories, requests, resources, and policies
- **`readonly`** on constructor-injected dependencies that do not mutate
- Full type declarations on all method parameters and return types
- No `dd()`, `dump()`, `var_dump()`, or commented-out code in committed files

Run all three quality checks before committing:

```bash
php artisan test              # Test suite
vendor/bin/phpstan analyse    # Static analysis (level 6)
vendor/bin/pint --test        # Code style check
```

---

### Folder Structure

```
expense-tracker/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/          # JSON API controllers
│   │   │   ├── Web/             # Blade view controllers
│   │   │   └── Auth/            # Breeze auth controllers
│   │   ├── Requests/
│   │   │   ├── Expense/         # StoreExpenseRequest, UpdateExpenseRequest, IndexExpenseRequest
│   │   │   └── Category/        # StoreCategoryRequest, UpdateCategoryRequest
│   │   ├── Resources/           # ExpenseResource, CategoryResource
│   │   └── Middleware/          # SecurityHeaders, etc.
│   ├── Models/                  # Eloquent models (User, Expense, Category)
│   ├── Policies/                # ExpensePolicy, CategoryPolicy
│   ├── Repositories/
│   │   ├── Contracts/           # ExpenseRepositoryInterface, CategoryRepositoryInterface
│   │   └── Eloquent/            # ExpenseRepository, CategoryRepository
│   ├── Services/                # ExpenseReportService (report aggregations + caching)
│   └── Providers/               # RepositoryServiceProvider (interface → implementation bindings)
├── database/
│   ├── migrations/              # All database schema migrations
│   ├── factories/               # Model factories for testing
│   └── seeders/                 # CategorySeeder, DatabaseSeeder (demo user + expenses)
├── resources/
│   ├── css/app.css              # Tailwind + shadcn/Zinc CSS custom properties
│   ├── js/app.js                # Alpine.js bootstrap
│   └── views/
│       ├── layouts/             # app.blade.php (main shell), guest.blade.php
│       ├── expenses/            # index, create, edit
│       ├── categories/          # index, create, edit
│       ├── reports/             # monthly-category, monthly-average, lifetime
│       ├── dashboard.blade.php
│       └── auth/                # login, register, password reset
├── routes/
│   ├── web.php                  # Web routes (auth-protected)
│   └── api.php                  # API v1 routes (Sanctum-protected)
├── tests/
│   ├── Feature/
│   │   ├── Api/                 # ExpenseTest, ReportTest
│   │   └── Auth/                # Registration, login, password tests
│   └── Unit/                    # ExpenseRepositoryTest
├── docker/
│   ├── nginx/default.conf       # Nginx configuration for Laravel
│   ├── php/local.ini            # PHP settings (upload limits, OPcache)
│   └── entrypoint.sh           # Container startup script
├── Dockerfile                   # Multi-stage: Node (assets) + PHP-FPM
└── docker-compose.yml           # app + webserver + db services
```

---

### Layer Responsibilities

| Layer | Responsibility | Must NOT |
|---|---|---|
| **Controller** | Receive request → authorize → delegate → return response | Query the database directly |
| **Repository** | All database queries | Know HTTP exists, return JSON |
| **Service** | Business logic, report aggregations, caching | Handle HTTP requests |
| **FormRequest** | Validate and authorize incoming input | Contain business logic |
| **Policy** | Authorize actions against a model | Query the database |
| **Resource** | Shape JSON output | Contain logic |
| **Model** | Relationships, casts, scopes, accessors | Contain business logic, send email |

---

### Testing Requirements

- Every feature must have: one happy-path test, one validation-failure test (422), one authorization-failure test (403/404)
- Repository methods each have a dedicated unit test
- Tests use `RefreshDatabase` and MySQL (`expense_tracker_test` database)
- Factories exist for all models

Run the full suite:

```bash
php artisan test
```

Run a specific test file:

```bash
php artisan test tests/Feature/Api/ExpenseTest.php
```

---

### Roles & Permissions

This application is single-user scoped — each user can only see and modify their own data.

- **Expenses**: scoped to `user_id` at the repository level — no user can ever read or modify another user's expenses
- **Categories**: system categories (seeded) are shared and read-only; custom categories are user-editable
- **API tokens**: Sanctum personal access tokens are issued per user and revoked on logout

---

## Additional Notes

### Common Issues & Fixes

**`php artisan` using wrong PHP version (Windows)**

If `php` on your PATH is not 8.2, use the full path:

```bash
C:\laragon\bin\php\php-8.2.5-Win32-vs16-x64\php.exe artisan serve
```

**`APP_KEY` is missing**

```bash
php artisan key:generate
```

**Views not updating after template changes**

```bash
php artisan view:clear
```

**Docker: database connection refused on first start**

The entrypoint script retries the database connection automatically. If it still fails, wait a few seconds and restart:

```bash
docker compose restart app
```

**Permission errors on storage (Linux/Mac)**

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### Important Development Notes

- **Never use `$request->all()`** — always use `$request->validated()` to prevent mass-assignment issues
- **Never query Eloquent in a controller** — always go through a repository method
- **`Model::preventLazyLoading()`** is enabled outside production — a lazy-load will throw an exception in development, catching N+1 bugs early
- **Do not commit `.env`** — only `.env.example` and `.env.docker` are version-controlled
- **Money is stored as `decimal(12,2)`** — never use floats for currency

---

### Deployment Notes

Before deploying to production:

```bash
# 1. Set environment
APP_ENV=production
APP_DEBUG=false

# 2. Install production dependencies only
composer install --no-dev --optimize-autoloader

# 3. Build frontend assets
npm run build

# 4. Cache config, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run migrations
php artisan migrate --force
```

> Make sure `APP_DEBUG=false` in production. Never expose stack traces to end users.
