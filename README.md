# Expense Tracker

A personal finance web application built with Laravel 12. Track your daily expenses, organise them by category, and view spending reports — all secured with a full authentication system.

---

## Project Setup (Using Docker)

The easiest way to run this project is with Docker. No need to install PHP, MySQL, or Node.js manually.

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed and running

---

### 1. Clone the Repository

```bash
git clone https://github.com/nithinpp1998/expense-tracker.git
cd expense-tracker
```

---

### 2. Copy the Docker Environment File

```bash
cp .env.docker .env
```

> The `.env.docker` file is already pre-configured for Docker. No further changes needed.

---

### 3. Build and Start the Project

```bash
docker compose up --build -d
```

This command will:
- Build the application image
- Start the web server on port **8080**
- Start the MySQL database on port **3307**
- Automatically run migrations and seed demo data

---

### 4. Open the Application

Visit: **http://localhost:8080**

**Demo login credentials:**

| Field    | Value              |
|----------|--------------------|
| Email    | `demo@example.com` |
| Password | `password`         |

---

### Useful Docker Commands

```bash
# Stop the application
docker compose down

# Stop and remove all data (including database)
docker compose down -v

# View application logs
docker compose logs -f app

# Run artisan commands inside the container
docker compose exec app php artisan migrate:fresh --seed

# Open a shell inside the container
docker compose exec app bash
```

---

## Project Features

### Authentication
Users can register, log in, log out, and reset their password. Each user's data is fully private — no one can see or access another user's expenses.

---

### Expense Management
The core feature of the app. Users can:
- **Add** an expense with a description, amount, date, and category
- **Edit** or **delete** any of their expenses
- **Filter** expenses by category, date range, or keyword
- **Export** expenses as a **CSV** or **PDF** file

---

### Category Management
Expenses are organised into categories. Users can:
- Use the 12 built-in system categories (Food, Transport, Shopping, etc.)
- Create, edit, and delete their own custom categories
- Assign a name and colour to each category

---

### Dashboard
The home screen gives a quick overview of spending:
- **Total spend** for the selected period
- **Daily average** spend
- **All-time total** across all categories
- **Spending by category** — doughnut chart
- **Top categories** — bar chart with amounts
- **Recent expenses** — latest activity table
- **Date range filter** — view any custom period with preset shortcuts (This Month, Last 30 Days, etc.)

---

### Reports
Three detailed spending reports are available under the Reports menu:

| Report | What it shows |
|---|---|
| **Monthly Category** | How much was spent per category in a selected month, shown as a doughnut chart and table |
| **Daily Average** | The average amount spent per day in a selected month |
| **Lifetime Totals** | All-time spending per category with percentage share |
| **vs. Last Month** | Side-by-side comparison of this month vs last month per category, with % change indicators |

---

### REST API
All data is accessible via a versioned JSON API for developers or integrations:
- Full expense CRUD: `GET / POST / PUT / DELETE /api/v1/expenses`
- Category listing: `GET /api/v1/categories`
- Report endpoints: `/api/v1/reports/monthly-category`, `/api/v1/reports/monthly-average`, `/api/v1/reports/lifetime`
- Secured with **Sanctum bearer tokens** — log in via `POST /api/v1/login` to get a token
