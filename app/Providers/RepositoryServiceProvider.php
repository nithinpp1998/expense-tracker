<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ExpenseRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class,
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class,
        );
    }
}
