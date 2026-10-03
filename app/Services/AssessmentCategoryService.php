<?php

namespace App\Services;

use App\Models\ScoreCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AssessmentCategoryService
{
    /** @var Collection<int, ScoreCategory>|null */
    protected static ?Collection $cachedCategories = null;

    public static function clearCache(): void
    {
        static::$cachedCategories = null;
    }

    /**
     * @return Collection<int, ScoreCategory>
     */
    protected function getCategories(): Collection
    {
        if (static::$cachedCategories !== null) {
            return static::$cachedCategories;
        }

        try {
            if (Schema::hasTable('score_categories')) {
                static::$cachedCategories = ScoreCategory::orderBy('min_percentage')->get();
            } else {
                static::$cachedCategories = collect();
            }
        } catch (\Throwable) {
            static::$cachedCategories = collect();
        }

        return static::$cachedCategories;
    }

    /**
     * Determine category based on percentage score.
     */
    public function getCategory(float $percentage): string
    {
        $categories = $this->getCategories();

        if ($categories->isNotEmpty()) {
            foreach ($categories as $cat) {
                if ($percentage <= $cat->max_percentage) {
                    return $cat->name;
                }
            }

            return $categories->last()->name;
        }

        if ($percentage <= 20.0) {
            return 'Resistor';
        }

        if ($percentage <= 40.0) {
            return 'Follower';
        }

        if ($percentage <= 60.0) {
            return 'Supporter';
        }

        if ($percentage <= 80.0) {
            return 'Initiator';
        }

        return 'Achiever';
    }

    /**
     * Get the emoji associated with the category.
     */
    public function getEmoji(string $category): string
    {
        $cat = $this->getCategories()->firstWhere('name', $category);
        if ($cat && ! empty($cat->emoji)) {
            return $cat->emoji;
        }

        return match (strtolower(trim($category))) {
            'resistor', 'resistant' => '🛡️',
            'follower' => '👥',
            'supporter' => '🌱',
            'initiator', 'driver' => '🚀',
            'achiever', 'champion' => '🏆',
            'divergent' => '⚡',
            'fragmented' => '🧩',
            'aligned' => '👥',
            'synchronised' => '⚙️',
            'unified' => '🚩',
            default => '🎯',
        };
    }

    /**
     * Get theme color hex code for SVG meter and accents.
     */
    public function getColorHex(string $category): string
    {
        $cat = $this->getCategories()->firstWhere('name', $category);
        if ($cat && ! empty($cat->color)) {
            return $cat->color;
        }

        return match (strtolower(trim($category))) {
            'resistor', 'resistant', 'divergent' => '#EF4444',    // Red 500
            'follower', 'fragmented' => '#F97316',                // Orange 500
            'supporter', 'aligned' => '#10B981',                  // Green 500
            'initiator', 'driver', 'synchronised' => '#F59E0B',   // Amber / Yellow 500
            'achiever', 'champion', 'unified' => '#3B82F6',       // Blue 500
            default => '#6B7280',
        };
    }

    /**
     * Get Tailwind CSS badge classes.
     */
    public function getBadgeClass(string $category): string
    {
        $cat = $this->getCategories()->firstWhere('name', $category);
        if ($cat) {
            return $cat->badge_class;
        }

        return match (strtolower(trim($category))) {
            'resistor', 'resistant', 'divergent' => 'bg-rose-50 text-rose-700 ring-rose-600/20 border-rose-200',
            'follower', 'fragmented' => 'bg-orange-50 text-orange-700 ring-orange-600/20 border-orange-200',
            'supporter', 'aligned' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 border-emerald-200',
            'initiator', 'driver', 'synchronised' => 'bg-amber-50 text-amber-700 ring-amber-600/20 border-amber-200',
            'achiever', 'champion', 'unified' => 'bg-blue-50 text-blue-700 ring-blue-600/20 border-blue-200',
            default => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 border-indigo-200',
        };
    }

    /**
     * Get all categories definition with ranges.
     *
     * @return array<int, array{name: string, range: string, emoji: string, color: string}>
     */
    public function getAllCategories(): array
    {
        $categories = $this->getCategories();

        if ($categories->isNotEmpty()) {
            return $categories->map(fn (ScoreCategory $cat) => [
                'name' => $cat->name,
                'range' => $cat->range_label,
                'emoji' => $cat->emoji,
                'color' => $cat->color,
            ])->all();
        }

        return [
            ['name' => 'Resistor', 'range' => '0–20%', 'emoji' => '🛡️', 'color' => '#EF4444'],
            ['name' => 'Follower', 'range' => '>20–40%', 'emoji' => '👥', 'color' => '#F97316'],
            ['name' => 'Supporter', 'range' => '>40–60%', 'emoji' => '🌱', 'color' => '#10B981'],
            ['name' => 'Initiator', 'range' => '>60–80%', 'emoji' => '🚀', 'color' => '#F59E0B'],
            ['name' => 'Achiever', 'range' => '>80–100%', 'emoji' => '🏆', 'color' => '#3B82F6'],
        ];
    }
}
