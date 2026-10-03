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
                'name' => 'Resistor',
                'min_percentage' => 0.00,
                'max_percentage' => 20.00,
                'emoji' => '🛡️',
                'color' => '#EF4444',
                'description' => 'Score from 0% up to 20% (1.0 - 2.0). Resistant: Anxious or skeptical of change; needs safety and guidance.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Follower',
                'min_percentage' => 20.01,
                'max_percentage' => 40.00,
                'emoji' => '👥',
                'color' => '#F97316',
                'description' => 'Score from >20% up to 40% (2.1 - 4.0). Follower: Complies when directed, but lacks proactive ownership.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Supporter',
                'min_percentage' => 40.01,
                'max_percentage' => 60.00,
                'emoji' => '🌱',
                'color' => '#10B981',
                'description' => 'Score from >40% up to 60% (4.1 - 6.0). Supporter: Positive attitude, adapts well, and cooperates readily.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Initiator',
                'min_percentage' => 60.01,
                'max_percentage' => 80.00,
                'emoji' => '🚀',
                'color' => '#F59E0B',
                'description' => 'Score from >60% up to 80% (6.1 - 8.0). Driver / Initiator: Proactive, creates action plans, and drives transitions.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Achiever',
                'min_percentage' => 80.01,
                'max_percentage' => 100.00,
                'emoji' => '🏆',
                'color' => '#3B82F6',
                'description' => 'Score from >80% up to 100% (8.1 - 10.0). Champion / Achiever: Exemplary change leader; drives results and elevates others.',
                'sort_order' => 5,
            ],
        ];

        // Delete old fruit or legacy categories if they exist
        ScoreCategory::whereIn('name', ['Apple', 'Orange', 'Tomato', 'Lemon', 'Cucumber', 'Catalyst', 'Strategist', 'Operator'])->delete();

        foreach ($categories as $cat) {
            ScoreCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
