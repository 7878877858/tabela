<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class ListingSearch
{
    public static function term(?string $search): ?string
    {
        $search = trim((string) $search);

        return $search !== '' ? $search : null;
    }

    public static function likeTerm(?string $search): ?string
    {
        $search = self::term($search);

        return $search ? '%' . $search . '%' : null;
    }

    public static function applyAnimalFields(Builder $query, ?string $search, string $table = ''): Builder
    {
        $term = self::likeTerm($search);
        if (!$term) {
            return $query;
        }

        $prefix = $table ? $table . '.' : '';

        return $query->where(function (Builder $q) use ($term, $prefix) {
            $q->where($prefix . 'tag_number', 'like', $term)
                ->orWhere($prefix . 'name', 'like', $term);
        });
    }

    public static function applyTextColumns(Builder $query, ?string $search, array $columns): Builder
    {
        $term = self::likeTerm($search);
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', $term);
            }
        });
    }
}
