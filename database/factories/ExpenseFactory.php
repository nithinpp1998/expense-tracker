<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
final class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 0.01, 5000.00),
            'description' => $this->faker->sentence(4),
            'occurred_at' => $this->faker->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
