<?php

namespace App\Support;

use App\Models\Buffalo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnimalRegistry
{
    /**
     * @param  callable(Builder): void|null  $queryModifier
     */
    public static function entries(?callable $queryModifier = null): Collection
    {
        $query = Buffalo::query()->orderBy('tag_number');

        if ($queryModifier) {
            $queryModifier($query);
        }

        return $query->get()->map(fn (Buffalo $b) => self::entryFromModel($b));
    }

    public static function activeEntries(): Collection
    {
        return self::entries(fn (Builder $q) => $q->where('status', 'active'));
    }

    public static function json(?callable $queryModifier = null): string
    {
        return self::entries($queryModifier)->toJson(JSON_UNESCAPED_UNICODE);
    }

    public static function entryFromModel(Buffalo $b): array
    {
        $type = Buffalo::normalizeAnimalType($b->animal_type ?? 'buffalo');

        return [
            'id' => $b->id,
            'tag' => $b->tag_number,
            'name' => $b->name ?? '',
            'type' => $type,
            'type_label' => $b->animal_type_label,
            'label' => $b->display_label,
            'search' => mb_strtolower(implode(' ', array_filter([
                $b->tag_number,
                $b->name,
                $type,
                $b->animal_type_label,
                str_replace('_', ' ', $type),
            ]))),
        ];
    }
}
