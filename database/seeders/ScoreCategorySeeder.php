<?php

namespace Database\Seeders;

use App\Models\ScoreCategory;
use Illuminate\Database\Seeder;

class ScoreCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Apple',
                'min_percentage' => 0.00,
                'max_percentage' => 20.00,
                'emoji' => '🍏',
                'color' => '#10B981',
                'description' => 'Score from 0% up to 20%',
                'sort_order' => 1,
            ],
            [
                'name' => 'Orange',
                'min_percentage' => 20.01,
                'max_percentage' => 40.00,
                'emoji' => '🍊',
                'color' => '#F97316',
                'description' => 'Score from >20% up to 40%',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tomato',
                'min_percentage' => 40.01,
                'max_percentage' => 60.00,
                'emoji' => '🍅',
                'color' => '#EF4444',
                'description' => 'Score from >40% up to 60%',
                'sort_order' => 3,
            ],
            [
                'name' => 'Lemon',
                'min_percentage' => 60.01,
                'max_percentage' => 80.00,
                'emoji' => '🍋',
                'color' => '#F59E0B',
                'description' => 'Score from >60% up to 80%',
                'sort_order' => 4,
            ],
            [
                'name' => 'Cucumber',
                'min_percentage' => 80.01,
                'max_percentage' => 100.00,
                'emoji' => '🥒',
                'color' => '#059669',
                'description' => 'Score from >80% up to 100%',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $cat) {
            ScoreCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
