<?php

namespace App\Services;

use App\Models\Buffalo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnimalTagService
{
    public const TYPES = ['buffalo', 'cow', 'buffalo_calf', 'cow_calf'];

    public static function prefixFor(string $animalType): string
    {
        return match ($animalType) {
            'buffalo'      => 'B',
            'cow'          => 'C',
            'buffalo_calf' => 'BC',
            'cow_calf'     => 'CC',
            default        => throw new InvalidArgumentException('Invalid animal type: ' . $animalType),
        };
    }

    public static function preview(string $animalType): string
    {
        $prefix = self::prefixFor($animalType);
        $next = self::nextSequenceNumber($prefix, $animalType);

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public static function generate(string $animalType): string
    {
        if (!in_array($animalType, self::TYPES, true)) {
            throw new InvalidArgumentException('Invalid animal type: ' . $animalType);
        }

        return DB::transaction(function () use ($animalType) {
            $prefix = self::prefixFor($animalType);

            Buffalo::where(function ($query) use ($animalType, $prefix) {
                $query->where('animal_type', $animalType)
                    ->orWhere('tag_number', 'like', $prefix . '%');
            })->lockForUpdate()->get(['id', 'tag_number']);

            $next = self::nextSequenceNumber($prefix, $animalType);

            return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    protected static function nextSequenceNumber(string $prefix, string $animalType): int
    {
        $tags = Buffalo::where('animal_type', $animalType)
            ->orWhere('tag_number', 'like', $prefix . '%')
            ->pluck('tag_number');

        $max = 0;

        foreach ($tags as $tag) {
            $num = self::parseSequenceNumber($tag, $prefix);
            if ($num !== null && $num > $max) {
                $max = $num;
            }
        }

        return $max + 1;
    }

    protected static function parseSequenceNumber(string $tag, string $prefix): ?int
    {
        $tag = strtoupper(trim($tag));

        if (!str_starts_with($tag, $prefix)) {
            return null;
        }

        $suffix = substr($tag, strlen($prefix));

        if ($suffix === '' || !ctype_digit($suffix)) {
            return null;
        }

        return (int) $suffix;
    }
}
