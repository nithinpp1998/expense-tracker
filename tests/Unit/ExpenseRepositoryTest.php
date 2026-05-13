<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Eloquent\ExpenseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeRepo(): ExpenseRepository
{
    return new ExpenseRepository(new Expense);
}

it('paginates expenses for the correct user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $cat = Category::factory()->create();

    Expense::factory()->for($user)->for($cat)->count(3)->create();
    Expense::factory()->for($other)->for($cat)->count(2)->create();

    $result = makeRepo()->paginateForUser($user->id, [], 15);

    expect($result->total())->toBe(3);
});

it('finds an expense by user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->for(Category::factory()->create())->create();

    $found = makeRepo()->findForUser($user->id, $expense->id);

    expect($found?->id)->toBe($expense->id);
});

it('returns null when expense belongs to another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $expense = Expense::factory()->for($other)->for(Category::factory()->create())->create();

    expect(makeRepo()->findForUser($user->id, $expense->id))->toBeNull();
});

it('creates an expense', function () {
    $user = User::factory()->create();
    $cat = Category::factory()->create();

    $expense = makeRepo()->create([
        'user_id' => $user->id,
        'category_id' => $cat->id,
        'amount' => 99.99,
        'description' => 'Test expense',
        'occurred_at' => now(),
    ]);

    expect($expense->amount)->toBe('99.99');
    expect(Expense::count())->toBe(1);
});

it('updates an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->for(Category::factory()->create())->create();

    $updated = makeRepo()->update($expense, ['description' => 'Updated description']);

    expect($updated->description)->toBe('Updated description');
});

it('soft-deletes an expense', function () {
    $expense = Expense::factory()->for(User::factory()->create())->for(Category::factory()->create())->create();

    makeRepo()->delete($expense);

    expect(Expense::count())->toBe(0);
    expect(Expense::withTrashed()->count())->toBe(1);
});

it('calculates totals by category for month', function () {
    $user = User::factory()->create();
    $cat = Category::factory()->create();

    Expense::factory()->for($user)->for($cat)->count(2)->create([
        'amount' => 50.00,
        'occurred_at' => now()->startOfMonth()->addDays(1),
    ]);

    $result = makeRepo()->totalsByCategoryForMonth($user->id, (int) now()->year, (int) now()->month);

    expect($result)->toHaveCount(1);
    expect((float) $result->first()->total)->toBe(100.0);
});

it('calculates daily average for month', function () {
    $user = User::factory()->create();
    $cat = Category::factory()->create();
    $days = (int) now()->daysInMonth;

    Expense::factory()->for($user)->for($cat)->create([
        'amount' => $days * 10.0,
        'occurred_at' => now()->startOfMonth()->addDays(1),
    ]);

    $avg = makeRepo()->dailyAverageForMonth($user->id, (int) now()->year, (int) now()->month);

    expect($avg)->toBe(10.0);
});

it('returns zero daily average for empty month', function () {
    $user = User::factory()->create();

    $avg = makeRepo()->dailyAverageForMonth($user->id, 2020, 1);

    expect($avg)->toBe(0.0);
});

it('calculates lifetime totals by category', function () {
    $user = User::factory()->create();
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    Expense::factory()->for($user)->for($cat1)->count(2)->create(['amount' => 25.00]);
    Expense::factory()->for($user)->for($cat2)->create(['amount' => 100.00]);

    $result = makeRepo()->lifetimeTotalsByCategory($user->id);

    expect($result)->toHaveCount(2);
    expect($result->sum(fn ($r) => (float) $r->total))->toBe(150.0);
});
