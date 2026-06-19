<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Support\ListPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AnimalInvestmentService
{
    public const TYPES = Buffalo::ANIMAL_TYPES;

    public const PER_PAGE_OPTIONS = ListPagination::OPTIONS;

    public const DEFAULT_PER_PAGE = ListPagination::DEFAULT;

    /**
     * All-time purchase totals by animal type (active + sold + dead with purchase_price).
     *
     * @return array<string, float>
     */
    public function totalsByType(): array
    {
        $rows = Buffalo::query()
            ->whereNotNull('purchase_price')
            ->where('purchase_price', '>', 0)
            ->selectRaw('animal_type, SUM(purchase_price) as total')
            ->groupBy('animal_type')
            ->pluck('total', 'animal_type');

        $totals = [];
        foreach (self::TYPES as $type) {
            $totals[$type] = (float) ($rows[$type] ?? 0);
        }

        return $totals;
    }

    public function grandTotal(?array $totals = null): float
    {
        $totals ??= $this->totalsByType();

        return array_sum($totals);
    }

    public function filteredQuery(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $animalType = null,
        ?string $search = null
    ): Builder {
        return $this->baseFilteredQuery($dateFrom, $dateTo, $animalType, $search)
            ->orderByDesc('purchase_date')
            ->orderBy('tag_number');
    }

    protected function baseFilteredQuery(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $animalType = null,
        ?string $search = null
    ): Builder {
        $query = Buffalo::query()
            ->whereNotNull('purchase_price')
            ->where('purchase_price', '>', 0);

        if ($dateFrom) {
            $query->whereDate('purchase_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('purchase_date', '<=', $dateTo);
        }
        if ($animalType && in_array($animalType, self::TYPES, true)) {
            $query->where('animal_type', $animalType);
        }
        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('tag_number', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        return $query;
    }

    public function paginatedReport(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $animalType = null,
        ?string $search = null,
        int $perPage = self::DEFAULT_PER_PAGE
    ): LengthAwarePaginator {
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true)
            ? $perPage
            : self::DEFAULT_PER_PAGE;

        return $this->filteredQuery($dateFrom, $dateTo, $animalType, $search)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function reportRows(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $animalType = null,
        ?string $search = null
    ): Collection {
        return $this->filteredQuery($dateFrom, $dateTo, $animalType, $search)
            ->get()
            ->map(fn (Buffalo $b) => [
                'id'             => $b->id,
                'purchase_date'  => $b->purchase_date?->format('d-m-Y') ?? '—',
                'tag_number'     => $b->tag_number,
                'animal_type'    => $b->animal_type,
                'animal_type_label' => $b->animal_type_label,
                'name'           => $b->name ?? '—',
                'purchase_price' => (float) $b->purchase_price,
                'show_url'       => route('buffalo.show', $b),
            ]);
    }

    /**
     * @return array<string, float>
     */
    public function totalsForFilters(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $animalType = null,
        ?string $search = null
    ): array {
        $rows = $this->baseFilteredQuery($dateFrom, $dateTo, $animalType, $search)
            ->selectRaw('animal_type, SUM(purchase_price) as total')
            ->groupBy('animal_type')
            ->pluck('total', 'animal_type');

        $totals = [];
        foreach (self::TYPES as $type) {
            $totals[$type] = (float) ($rows[$type] ?? 0);
        }

        return $totals;
    }
}
