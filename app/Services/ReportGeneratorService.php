<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Models\Expense;
use App\Models\FeedEntry;
use App\Models\HealthRecord;
use App\Models\Income;
use App\Models\MilkEntry;
use App\Models\VaccinationRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportGeneratorService
{
    public function filtersFromRequest(array $input): array
    {
        return [
            'from_date'    => $input['from_date'] ?? now()->startOfMonth()->toDateString(),
            'to_date'      => $input['to_date'] ?? now()->toDateString(),
            'animal_type'  => $input['animal_type'] ?? '',
            'report_type'  => $input['report_type'] ?? 'milk',
        ];
    }

    protected function buffaloIdsForType(string $animalType): ?array
    {
        if ($animalType === '') {
            return null;
        }

        return Buffalo::where('animal_type', $animalType)->pluck('id')->all();
    }

    protected function applyBuffaloFilter(Builder $query, string $buffaloColumn, ?array $buffaloIds): Builder
    {
        if ($buffaloIds !== null) {
            $query->whereIn($buffaloColumn, $buffaloIds);
        }

        return $query;
    }

    public function generate(array $filters): array
    {
        return match ($filters['report_type']) {
            'feed'         => $this->feedReport($filters),
            'expense'      => $this->expenseReport($filters),
            'income'       => $this->incomeReport($filters),
            'health'       => $this->healthReport($filters),
            'vaccination'  => $this->vaccinationReport($filters),
            'combined'     => $this->combinedReport($filters),
            'monthly'      => $this->monthlyReport($filters),
            'yearly'       => $this->yearlyReport($filters),
            default        => $this->milkReport($filters),
        };
    }

    public function milkReport(array $filters): array
    {
        $buffaloIds = $this->buffaloIdsForType($filters['animal_type']);
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $query = MilkEntry::with('buffalo')
            ->whereBetween('entry_date', [$from, $to]);

        $this->applyBuffaloFilter($query, 'buffalo_id', $buffaloIds);

        $entries = $query->orderBy('entry_date')->orderBy('buffalo_id')->get();

        $daily = $entries->groupBy(fn ($e) => $e->entry_date->toDateString())
            ->map(fn ($group, $date) => [
                'date'    => $date,
                'morning' => $group->sum('morning_liters'),
                'evening' => $group->sum('evening_liters'),
                'total'   => $group->sum('total_liters'),
            ])->values();

        $byAnimal = $entries->groupBy('buffalo_id')->map(function ($group) {
            $b = $group->first()->buffalo;

            return [
                'tag'     => $b?->tag_number ?? '—',
                'name'    => $b?->name ?? '—',
                'type'    => $b?->animal_type ?? '—',
                'morning' => $group->sum('morning_liters'),
                'evening' => $group->sum('evening_liters'),
                'total'   => $group->sum('total_liters'),
                'days'    => $group->count(),
            ];
        })->sortByDesc('total')->values();

        return [
            'title'    => 'Milk Report',
            'filters'  => $filters,
            'daily'    => $daily,
            'byAnimal' => $byAnimal,
            'summary'  => [
                'morning' => $entries->sum('morning_liters'),
                'evening' => $entries->sum('evening_liters'),
                'total'   => $entries->sum('total_liters'),
            ],
        ];
    }

    public function feedReport(array $filters): array
    {
        $buffaloIds = $this->buffaloIdsForType($filters['animal_type']);
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $query = FeedEntry::with(['buffalo', 'feed'])
            ->whereBetween('entry_date', [$from, $to]);

        $this->applyBuffaloFilter($query, 'buffalo_id', $buffaloIds);

        $entries = $query->orderBy('entry_date')->get();

        $byFeed = $entries->groupBy('feed_id')->map(function ($group) {
            $feed = $group->first()->feed;

            return [
                'feed'    => $feed?->name ?? '—',
                'morning' => $group->where('feed_time', 'morning')->sum('quantity'),
                'evening' => $group->where('feed_time', 'evening')->sum('quantity'),
                'total'   => $group->sum('quantity'),
            ];
        })->sortByDesc('total')->values();

        return [
            'title'   => 'Feed Report',
            'filters' => $filters,
            'entries' => $entries,
            'byFeed'  => $byFeed,
            'summary' => ['total' => $entries->sum('quantity')],
        ];
    }

    public function expenseReport(array $filters): array
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $entries = Expense::whereBetween('expense_date', [$from, $to])
            ->orderBy('expense_date')
            ->get();

        $byCategory = $entries->groupBy('category')->map(fn ($g, $cat) => [
            'category' => $cat,
            'total'    => $g->sum('amount'),
        ])->values();

        return [
            'title'      => 'Expense Report',
            'filters'    => $filters,
            'entries'    => $entries,
            'byCategory' => $byCategory,
            'summary'    => ['total' => $entries->sum('amount')],
        ];
    }

    public function incomeReport(array $filters): array
    {
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $entries = Income::whereBetween('income_date', [$from, $to])
            ->orderBy('income_date')
            ->get();

        $byCategory = $entries->groupBy('category')->map(fn ($g, $cat) => [
            'category' => $cat,
            'total'    => $g->sum('amount'),
        ])->values();

        return [
            'title'      => 'Income Report',
            'filters'    => $filters,
            'entries'    => $entries,
            'byCategory' => $byCategory,
            'summary'    => ['total' => $entries->sum('amount')],
        ];
    }

    public function healthReport(array $filters): array
    {
        $buffaloIds = $this->buffaloIdsForType($filters['animal_type']);
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $query = HealthRecord::with('buffalo')
            ->whereBetween('record_date', [$from, $to]);

        $this->applyBuffaloFilter($query, 'buffalo_id', $buffaloIds);

        $entries = $query->orderBy('record_date')->get();

        return [
            'title'   => 'Health Report',
            'filters' => $filters,
            'entries' => $entries,
            'summary' => [
                'visits'  => $entries->count(),
                'cost'    => $entries->sum('medicine_cost'),
            ],
        ];
    }

    public function vaccinationReport(array $filters): array
    {
        $buffaloIds = $this->buffaloIdsForType($filters['animal_type']);
        $from = Carbon::parse($filters['from_date']);
        $to = Carbon::parse($filters['to_date']);

        $query = VaccinationRecord::with('buffalo')
            ->whereBetween('vaccination_date', [$from, $to]);

        $this->applyBuffaloFilter($query, 'buffalo_id', $buffaloIds);

        $entries = $query->orderBy('vaccination_date')->get();

        return [
            'title'   => 'Vaccination Report',
            'filters' => $filters,
            'entries' => $entries,
            'summary' => ['count' => $entries->count()],
        ];
    }

    public function combinedReport(array $filters): array
    {
        return [
            'title'       => 'Combined Farm Report',
            'filters'     => $filters,
            'milk'        => $this->milkReport($filters),
            'feed'        => $this->feedReport($filters),
            'expense'     => $this->expenseReport($filters),
            'income'      => $this->incomeReport($filters),
            'health'      => $this->healthReport($filters),
            'vaccination' => $this->vaccinationReport($filters),
        ];
    }

    public function monthlyReport(array $filters): array
    {
        $from = Carbon::parse($filters['from_date'])->startOfMonth();
        $to = Carbon::parse($filters['to_date'])->endOfMonth();

        return $this->combinedReport(array_merge($filters, [
            'from_date' => $from->toDateString(),
            'to_date'   => $to->toDateString(),
        ]));
    }

    public function yearlyReport(array $filters): array
    {
        $year = Carbon::parse($filters['from_date'])->year;

        $months = collect(range(1, 12))->map(function ($m) use ($year, $filters) {
            $from = Carbon::create($year, $m, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
            $slice = array_merge($filters, [
                'from_date' => $from->toDateString(),
                'to_date'   => $to->toDateString(),
            ]);

            return [
                'month'   => $m,
                'milk'    => MilkEntry::whereYear('entry_date', $year)->whereMonth('entry_date', $m)->sum('total_liters'),
                'feed'    => FeedEntry::whereYear('entry_date', $year)->whereMonth('entry_date', $m)->sum('quantity'),
                'expense' => Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $m)->sum('amount'),
                'income'  => Income::whereYear('income_date', $year)->whereMonth('income_date', $m)->sum('amount'),
            ];
        });

        return [
            'title'   => 'Yearly Report ' . $year,
            'filters' => $filters,
            'months'  => $months,
            'summary' => [
                'milk'    => $months->sum('milk'),
                'feed'    => $months->sum('feed'),
                'expense' => $months->sum('expense'),
                'income'  => $months->sum('income'),
            ],
        ];
    }
}
