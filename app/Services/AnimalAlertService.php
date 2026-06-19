<?php

namespace App\Services;

use App\Models\Buffalo;
use App\Models\HealthRecord;
use App\Models\VaccinationRecord;
use App\Support\AnimalRegistry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnimalAlertService
{
    public const VACCINE_INTERVAL_DAYS = 365;

    public const AI_CHECK_DAYS = 30;

    public const TREATMENT_FOLLOWUP_DAYS = 14;

    public function vaccinationDueCount(): int
    {
        return $this->vaccinationDueAnimals()->count();
    }

    public function pregnancyCheckDueCount(): int
    {
        return $this->pregnancyCheckDueAnimals()->count();
    }

    public function treatmentFollowUpCount(): int
    {
        return $this->treatmentFollowUpAnimals()->count();
    }

    public function deliveryDueCount(): int
    {
        $today = today();

        return Buffalo::where('status', 'active')
            ->whereBetween('expected_delivery_date', [$today, $today->copy()->addDays(7)])
            ->count();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function vaccinationDueAnimals(): Collection
    {
        $cutoff = today()->subDays(self::VACCINE_INTERVAL_DAYS);

        $lastVaccinations = VaccinationRecord::query()
            ->selectRaw('buffalo_id, MAX(vaccination_date) as last_date')
            ->groupBy('buffalo_id')
            ->pluck('last_date', 'buffalo_id');

        return Buffalo::where('status', 'active')
            ->orderBy('tag_number')
            ->get()
            ->filter(function (Buffalo $b) use ($lastVaccinations, $cutoff) {
                $last = $lastVaccinations->get($b->id);

                return !$last || Carbon::parse($last)->lt($cutoff);
            })
            ->map(fn (Buffalo $b) => AnimalRegistry::entryFromModel($b))
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function pregnancyCheckDueAnimals(): Collection
    {
        $aiCutoff = today()->subDays(self::AI_CHECK_DAYS);

        return Buffalo::where('status', 'active')
            ->where(function ($q) use ($aiCutoff) {
                $q->where(function ($q2) use ($aiCutoff) {
                    $q2->whereNotNull('ai_date')
                        ->whereNull('pregnancy_check_date')
                        ->whereDate('ai_date', '<=', $aiCutoff);
                })->orWhere(function ($q2) {
                    $q2->where('lactation_status', 'pregnant')
                        ->whereNotNull('expected_delivery_date')
                        ->whereDate('expected_delivery_date', '<=', today()->addDays(14));
                });
            })
            ->orderBy('tag_number')
            ->get()
            ->map(fn (Buffalo $b) => AnimalRegistry::entryFromModel($b))
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function treatmentFollowUpAnimals(): Collection
    {
        $since = today()->subDays(self::TREATMENT_FOLLOWUP_DAYS);

        $buffaloIds = HealthRecord::query()
            ->whereDate('record_date', '>=', $since)
            ->whereNotNull('treatment')
            ->where('treatment', '!=', '')
            ->distinct()
            ->pluck('buffalo_id');

        return Buffalo::whereIn('id', $buffaloIds)
            ->where('status', 'active')
            ->orderBy('tag_number')
            ->get()
            ->map(fn (Buffalo $b) => AnimalRegistry::entryFromModel($b))
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function deliveryDueAnimals(): Collection
    {
        $today = today();

        return Buffalo::where('status', 'active')
            ->whereBetween('expected_delivery_date', [$today, $today->copy()->addDays(7)])
            ->orderBy('expected_delivery_date')
            ->get()
            ->map(fn (Buffalo $b) => AnimalRegistry::entryFromModel($b))
            ->values();
    }
}
