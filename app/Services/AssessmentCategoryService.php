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
            return 'Apple';
        }

        if ($percentage <= 40.0) {
            return 'Orange';
        }

        if ($percentage <= 60.0) {
            return 'Tomato';
        }

        if ($percentage <= 80.0) {
            return 'Lemon';
        }

        return 'Cucumber';
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

        return match ($category) {
            'Apple' => '🍏',
            'Orange' => '🍊',
            'Tomato' => '🍅',
            'Lemon' => '🍋',
            'Cucumber' => '🥒',
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

        return match ($category) {
            'Apple' => '#10B981',    // Emerald 500
            'Orange' => '#F97316',   // Orange 500
            'Tomato' => '#EF4444',   // Red / Rose 500
            'Lemon' => '#F59E0B',    // Amber / Yellow 500
            'Cucumber' => '#059669', // Green 600
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

        return match ($category) {
            'Apple' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 border-emerald-200',
            'Orange' => 'bg-orange-50 text-orange-700 ring-orange-600/20 border-orange-200',
            'Tomato' => 'bg-rose-50 text-rose-700 ring-rose-600/20 border-rose-200',
            'Lemon' => 'bg-amber-50 text-amber-700 ring-amber-600/20 border-amber-200',
            'Cucumber' => 'bg-teal-50 text-teal-700 ring-teal-600/20 border-teal-200',
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
            ['name' => 'Apple', 'range' => '0–20%', 'emoji' => '🍏', 'color' => '#10B981'],
            ['name' => 'Orange', 'range' => '>20–40%', 'emoji' => '🍊', 'color' => '#F97316'],
            ['name' => 'Tomato', 'range' => '>40–60%', 'emoji' => '🍅', 'color' => '#EF4444'],
            ['name' => 'Lemon', 'range' => '>60–80%', 'emoji' => '🍋', 'color' => '#F59E0B'],
            ['name' => 'Cucumber', 'range' => '>80–100%', 'emoji' => '🥒', 'color' => '#059669'],
        ];
    }
}
