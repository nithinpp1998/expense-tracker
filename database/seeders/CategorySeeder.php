<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    private const CATEGORIES = [
        ['name' => 'Food & Dining',       'slug' => 'food-dining',       'color' => '#f97316'],
        ['name' => 'Transportation',      'slug' => 'transportation',    'color' => '#3b82f6'],
        ['name' => 'Entertainment',       'slug' => 'entertainment',     'color' => '#8b5cf6'],
        ['name' => 'Utilities',           'slug' => 'utilities',         'color' => '#eab308'],
        ['name' => 'Rent & Housing',      'slug' => 'rent-housing',      'color' => '#6b7280'],
        ['name' => 'Healthcare',          'slug' => 'healthcare',        'color' => '#ef4444'],
        ['name' => 'Shopping',            'slug' => 'shopping',          'color' => '#ec4899'],
        ['name' => 'Travel',              'slug' => 'travel',            'color' => '#06b6d4'],
        ['name' => 'Education',           'slug' => 'education',         'color' => '#10b981'],
        ['name' => 'Subscriptions',       'slug' => 'subscriptions',     'color' => '#f59e0b'],
        ['name' => 'Savings & Investing', 'slug' => 'savings-investing', 'color' => '#22c55e'],
        ['name' => 'Other',               'slug' => 'other',             'color' => '#71717a'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $data) {
            Category::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_system' => true, 'is_active' => true])
            );
        }
    }
}
