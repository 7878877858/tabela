<?php

namespace App\Services;

use App\Models\Buffalo;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CalfBirthReportService
{
    public function query(?string $dateFrom = null, ?string $dateTo = null, ?string $search = null): Builder
    {
        $query = Buffalo::query()
            ->whereIn('animal_type', ['buffalo', 'cow'])
            ->whereNotNull('birth_date')
            ->with('birthCalf')
            ->orderByDesc('birth_date')
            ->orderBy('tag_number');

        if ($dateFrom) {
            $query->whereDate('birth_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('birth_date', '<=', $dateTo);
        }

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('tag_number', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('calf_tag_number', 'like', $term);
            });
        }

        return $query;
    }

    public function forDailyReport(Carbon|string $reportDate): Collection
    {
        return $this->query(
            Carbon::parse($reportDate)->toDateString(),
            Carbon::parse($reportDate)->toDateString()
        )->get();
    }

    public function paginated(
        ?string $dateFrom,
        ?string $dateTo,
        ?string $search,
        int $perPage
    ): LengthAwarePaginator {
        return $this->query($dateFrom, $dateTo, $search)
            ->paginate($perPage)
            ->withQueryString();
    }
}
