<?php

namespace App\Services;

use App\Models\Buffalo;
use Illuminate\Support\Facades\DB;

class CalfService
{
    public function calfTypeForParent(Buffalo $parent): ?string
    {
        return match (Buffalo::normalizeAnimalType($parent->animal_type)) {
            'buffalo' => 'buffalo_calf',
            'cow'     => 'cow_calf',
            default   => null,
        };
    }

    public function parentHasBirthInfo(Buffalo $parent): bool
    {
        return $parent->birth_date !== null;
    }

    public function findCalfForParent(Buffalo $parent): ?Buffalo
    {
        $calf = Buffalo::where('mother_buffalo_id', $parent->id)
            ->whereIn('animal_type', ['buffalo_calf', 'cow_calf'])
            ->first();

        if ($calf) {
            return $calf;
        }

        if ($parent->calf_tag_number) {
            return Buffalo::where('tag_number', $parent->calf_tag_number)
                ->whereIn('animal_type', ['buffalo_calf', 'cow_calf'])
                ->first();
        }

        return null;
    }

    /**
     * Create or update the calf animal linked to a buffalo/cow parent.
     */
    public function syncFromParent(Buffalo $parent): ?Buffalo
    {
        $calfType = $this->calfTypeForParent($parent);

        if (!$calfType || !$this->parentHasBirthInfo($parent)) {
            return null;
        }

        return DB::transaction(function () use ($parent, $calfType) {
            $existing = $this->findCalfForParent($parent);

            $payload = [
                'animal_type'       => $calfType,
                'mother_buffalo_id' => $parent->id,
                'gender'            => $parent->calf_gender,
                'birth_date'        => $parent->birth_date,
                'weight'            => $parent->calf_weight,
                'status'            => $parent->status ?? 'active',
                'lactation_status'  => 'dry',
                'name'              => $existing?->name ?: 'Calf',
            ];

            if ($existing) {
                $existing->update($payload);
                $calf = $existing->fresh();
            } else {
                $tag = $this->resolveCalfTag($parent, $calfType);
                $calf = Buffalo::create(array_merge($payload, [
                    'tag_number' => $tag,
                ]));
            }

            if ($parent->calf_tag_number !== $calf->tag_number) {
                $parent->forceFill([
                    'calf_tag_number' => $calf->tag_number,
                ])->saveQuietly();
            }

            return $calf;
        });
    }

    protected function resolveCalfTag(Buffalo $parent, string $calfType): string
    {
        $prefix = AnimalTagService::prefixFor($calfType);
        $candidate = $parent->calf_tag_number;

        if ($candidate && $this->isUsableCalfTag($candidate, $prefix, null)) {
            return strtoupper($candidate);
        }

        return AnimalService::generateTag($calfType);
    }

    protected function isUsableCalfTag(string $tag, string $prefix, ?int $ignoreId): bool
    {
        $tag = strtoupper(trim($tag));

        if (!str_starts_with($tag, $prefix)) {
            return false;
        }

        $query = Buffalo::where('tag_number', $tag);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return !$query->exists();
    }

    /**
     * Backfill calf records for legacy parent birth data.
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function backfillLegacyCalves(): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        Buffalo::whereIn('animal_type', ['buffalo', 'cow'])
            ->whereNotNull('birth_date')
            ->orderBy('id')
            ->each(function (Buffalo $parent) use (&$stats) {
                $before = $this->findCalfForParent($parent);
                $calf = $this->syncFromParent($parent);

                if (!$calf) {
                    $stats['skipped']++;

                    return;
                }

                if ($before) {
                    $stats['updated']++;
                } else {
                    $stats['created']++;
                }
            });

        return $stats;
    }
}
