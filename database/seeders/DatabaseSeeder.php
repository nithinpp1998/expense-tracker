<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'timezone' => 'UTC',
                'currency' => 'USD',
            ]
        );

        $categories = Category::all();

        for ($i = 89; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = random_int(1, 4);

            for ($j = 0; $j < $count; $j++) {
                Expense::factory()->create([
                    'user_id' => $demo->id,
                    'category_id' => $categories->random()->id,
                    'occurred_at' => $date->copy()->addHours(random_int(7, 22)),
                ]);
            }
        }
    }
}
