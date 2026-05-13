<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns monthly category report', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->for($user)->for($category)->create([
        'amount' => 100.00,
        'occurred_at' => now()->startOfMonth()->addDays(2),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/reports/monthly-category?year='.now()->year.'&month='.now()->month);

    $response->assertOk()
        ->assertJsonStructure(['year', 'month', 'data'])
        ->assertJsonCount(1, 'data');

    expect((float) $response->json('data.0.total'))->toBe(100.0);
});

it('returns empty data for month with no expenses', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/reports/monthly-category?year=2020&month=1')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('returns monthly daily average', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $daysInMonth = (int) now()->daysInMonth;

    Expense::factory()->for($user)->for($category)->create([
        'amount' => $daysInMonth * 5.0,
        'occurred_at' => now()->startOfMonth()->addDays(1),
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/reports/monthly-average?year='.now()->year.'&month='.now()->month);

    $response->assertOk();
    expect((float) $response->json('average'))->toBe(5.0);
});

it('returns zero average for empty month', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->getJson('/api/v1/reports/monthly-average?year=2020&month=1')
        ->assertOk();

    expect((float) $response->json('average'))->toBe(0.0);
});

it('returns lifetime category totals', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Expense::factory()->for($user)->for($category)->count(3)->create(['amount' => 50.00]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/reports/lifetime');

    $response->assertOk()
        ->assertJsonStructure(['data'])
        ->assertJsonCount(1, 'data');

    expect((float) $response->json('data.0.total'))->toBe(150.0);
});

it('returns 401 on report endpoints without auth', function () {
    $this->getJson('/api/v1/reports/monthly-category')->assertUnauthorized();
    $this->getJson('/api/v1/reports/monthly-average')->assertUnauthorized();
    $this->getJson('/api/v1/reports/lifetime')->assertUnauthorized();
});
