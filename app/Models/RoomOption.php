<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RoomOption extends Model
{
    protected $fillable = [
        'group',
        'key',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $clearOptionCache = function (self $option): void {
            foreach (array_unique(array_filter([
                $option->key,
                $option->getOriginal('key'),
            ])) as $key) {
                Cache::forget("room_option_id:{$option->group}:{$key}");

                if ($option->wasChanged('group')) {
                    Cache::forget("room_option_id:{$option->getOriginal('group')}:{$key}");
                }
            }
        };

        static::saved($clearOptionCache);
        static::deleted($clearOptionCache);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function optionsFor(string $group, $selectedValue = null): Collection
    {
        return static::active()
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'key', 'label']);
    }

    public static function validIdsFor(string $group): array
    {
        return static::optionsFor($group)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public static function activeLabelsFor(string $group): Collection
    {
        return static::active()
            ->where('group', $group)
            ->pluck('label');
    }

    public static function idForKey(string $group, $key): ?int
    {
        if ($key === null || $key === '') {
            return null;
        }

        return \Illuminate\Support\Facades\Cache::remember(
            "room_option_id:{$group}:{$key}",
            86400,
            fn () => static::active()->where('group', $group)->where('key', (string) $key)->value('id')
        );
    }

    public static function resolveId(string $group, $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $matched = static::active()->where('group', $group)->where('id', (int) $value)->value('id');
            if ($matched) {
                return (int) $matched;
            }
        }

        $str = trim((string) $value);
        $slugU = \Illuminate\Support\Str::slug($str, '_');
        $slugD = \Illuminate\Support\Str::slug($str, '-');
        $lower = strtolower($str);

        // Common real estate synonym mappings
        $synonyms = [
            'anyone' => 'any',
            'all' => 'any',
            'bachelor' => 'bachelors',
            'studio' => 'studio_apartment',
            'pg unit' => 'pg',
            'commercial unit' => null,
            'plot' => null,
            'company / corporate' => 'company_lease',
        ];

        if (array_key_exists($lower, $synonyms)) {
            if ($synonyms[$lower] === null) {
                return null;
            }
            $slugU = $synonyms[$lower];
        }

        return static::active()
            ->where('group', $group)
            ->where(function ($q) use ($str, $slugU, $slugD, $lower) {
                $q->where('key', $str)
                  ->orWhere('key', $slugU)
                  ->orWhere('key', $slugD)
                  ->orWhere('label', $str)
                  ->orWhere('label', 'like', "%{$str}%");
                if (str_starts_with($lower, 'any')) {
                    $q->orWhere('key', 'any');
                }
            })
            ->value('id');
    }

    public static function resolveOption(string $group, $value, bool $includeInactive = false): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $query = static::query()->where('group', $group);

        if (!$includeInactive) {
            $query->active();
        }

        return is_numeric($value)
            ? $query->where('id', (int) $value)->first()
            : $query->where('key', (string) $value)->first();
    }

    public static function getLabel(string $group, $value, ?string $fallback = null): string
    {
        if ($value === null || $value === '') {
            return $fallback ?? 'N/A';
        }

        $option = static::resolveOption($group, $value, false);

        if ($option) {
            return $option->label;
        }

        return $fallback ?? 'N/A';
    }

    public static function formatLabel(string $group, $value, ?string $fallback = null): string
    {
        if ($value === null || $value === '') {
            return $fallback ?? 'N/A';
        }

        $fallbackLabel = $fallback ?? (is_string($value) ? str_replace('_', ' ', $value) : (string) $value);

        return ucwords((string) $fallbackLabel);
    }
}
