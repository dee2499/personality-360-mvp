<?php

namespace App\Models;

use Database\Factories\ScoreCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreCategory extends Model
{
    /** @use HasFactory<ScoreCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'min_percentage',
        'max_percentage',
        'emoji',
        'color',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'min_percentage' => 'float',
            'max_percentage' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function getRangeLabelAttribute(): string
    {
        $min = (int) $this->min_percentage == $this->min_percentage ? (int) $this->min_percentage : $this->min_percentage;
        $max = (int) $this->max_percentage == $this->max_percentage ? (int) $this->max_percentage : $this->max_percentage;

        if ($min == 0) {
            return "0% – {$max}%";
        }

        return ">{$min}% – {$max}%";
    }

    public function getBadgeClassAttribute(): string
    {
        $lower = strtolower($this->name);

        return match ($lower) {
            'apple' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 border-emerald-200',
            'orange' => 'bg-orange-50 text-orange-700 ring-orange-600/20 border-orange-200',
            'tomato' => 'bg-rose-50 text-rose-700 ring-rose-600/20 border-rose-200',
            'lemon' => 'bg-amber-50 text-amber-700 ring-amber-600/20 border-amber-200',
            'cucumber' => 'bg-teal-50 text-teal-700 ring-teal-600/20 border-teal-200',
            default => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 border-indigo-200',
        };
    }
}
