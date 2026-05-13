<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->category = Category::factory()->create();
});

it('returns paginated expenses for the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Expense::factory()->for($user)->for($this->category)->count(3)->create();
    Expense::factory()->for($other)->for($this->category)->count(2)->create();

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/expenses')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson('/api/v1/expenses')->assertUnauthorized();
});

it('creates an expense and returns 201', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/expenses', [
        'amount' => 42.50,
        'description' => 'Test coffee',
        'category_id' => $this->category->id,
        'occurred_at' => now()->format('Y-m-d'),
    ])->assertCreated()
        ->assertJsonPath('data.description', 'Test coffee');

    expect((float) $this->getJson('/api/v1/expenses')->json('data.0.amount'))->toBe(42.50);
});

it('returns 422 when amount is missing', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/expenses', [
        'description' => 'Test',
        'category_id' => $this->category->id,
        'occurred_at' => now()->format('Y-m-d'),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['amount']);
});

it('returns 422 when category_id does not exist', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/expenses', [
        'amount' => 10.00,
        'description' => 'Test',
        'category_id' => 'non-existent-uuid',
        'occurred_at' => now()->format('Y-m-d'),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id']);
});

it('returns 404 when accessing another user expense', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $expense = Expense::factory()->for($owner)->for($this->category)->create();

    Sanctum::actingAs($other);

    $this->getJson("/api/v1/expenses/{$expense->id}")->assertNotFound();
});

it('updates an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->for($this->category)->create();

    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/expenses/{$expense->id}", ['description' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.description', 'Updated');
});

it('returns 404 when updating another user expense', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $expense = Expense::factory()->for($owner)->for($this->category)->create();

    Sanctum::actingAs($other);

    $this->patchJson("/api/v1/expenses/{$expense->id}", ['description' => 'Hack'])->assertNotFound();
});

it('soft-deletes an expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->for($user)->for($this->category)->create();

    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/expenses/{$expense->id}")->assertNoContent();

    expect(Expense::withTrashed()->find($expense->id))->not->toBeNull();
    expect(Expense::find($expense->id))->toBeNull();
});

it('returns 404 when deleting another user expense', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $expense = Expense::factory()->for($owner)->for($this->category)->create();

    Sanctum::actingAs($other);

    $this->deleteJson("/api/v1/expenses/{$expense->id}")->assertNotFound();
});
