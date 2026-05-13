<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Category;
use App\Models\Expense;
use App\Policies\CategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ExpenseRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);

        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
