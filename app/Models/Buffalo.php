<?php
// app/Models/Buffalo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Buffalo extends Model
{
    protected $table = 'buffaloes'; // 🔥 FIX
    protected $fillable = [
        'tag_number',
        'animal_type',
        'mother_buffalo_id',
        'name',
        'gender',
        'weight',
        'dob',
        'purchase_date',
        'purchase_price',
        'status',
        'lactation_status',
        'notes',
        'heat_date',
        'ai_date',
        'pregnancy_check_date',
        'expected_delivery_date',

        'birth_date',
        'calf_tag_number',
        'calf_gender',
        'calf_weight',
        'sold_date',
        'sale_price',
        'buyer_name',
        'sold_reason',
    ];

    protected $casts = [
        'dob' => 'date',
        'purchase_date' => 'date',
        'heat_date' => 'date',
        'ai_date' => 'date',
        'pregnancy_check_date' => 'date',
        'expected_delivery_date' => 'date',
        'birth_date' => 'date',
        'sold_date' => 'date',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function milkEntries(): HasMany
    {
        return $this->hasMany(MilkEntry::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Buffalo::class, 'mother_buffalo_id');
    }

    public function calves(): HasMany
    {
        return $this->hasMany(Buffalo::class, 'mother_buffalo_id');
    }

    public function birthCalf(): HasOne
    {
        return $this->hasOne(Buffalo::class, 'mother_buffalo_id')
            ->whereIn('animal_type', ['buffalo_calf', 'cow_calf'])
            ->latestOfMany();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function todayMilk()
    {
        return $this->milkEntries()->whereDate('entry_date', today())->first();
    }

    public function totalMilkThisMonth(): float
    {
        return $this->milkEntries()
            ->whereYear('entry_date', now()->year)
            ->whereMonth('entry_date', now()->month)
            ->sum('total_liters');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => '🟢 ' . __('buffalo.active'),
            'sold'   => '🔴 ' . __('buffalo.sold'),
            'dead'   => '⚫ ' . __('buffalo.dead'),
            default  => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge-green',
            'sold'   => 'badge-red',
            'dead'   => 'badge-gray',
            default  => 'badge-gray',
        };
    }

    public function getSoldPriceAttribute(): ?float
    {
        return $this->sale_price !== null ? (float) $this->sale_price : null;
    }

    public function setSoldPriceAttribute($value): void
    {
        $this->attributes['sale_price'] = $value;
    }

    public function getProfitLossAttribute(): ?float
    {
        if ($this->sale_price === null || $this->purchase_price === null) {
            return null;
        }

        return round((float) $this->sale_price - (float) $this->purchase_price, 2);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getLactationLabelAttribute(): string
    {
        return match ($this->lactation_status) {
            'lactating' => __('buffalo.lactating'),
            'dry'       => __('buffalo.dry'),
            'pregnant'  => __('buffalo.pregnant'),
            default     => $this->lactation_status,
        };
    }

    public function getAnimalTypeLabelAttribute(): string
    {
        return match ($this->animal_type ?? 'buffalo') {
            'cow'          => 'ગાય',
            'buffalo_calf' => 'ભેંસ બચ્ચું',
            'cow_calf'     => 'ગાય બચ્ચું',
            default        => 'ભેંસ',
        };
    }

    public static function animalTypeOptions(): array
    {
        return [
            'buffalo'      => '🐃 ભેંસ',
            'cow'          => '🐄 ગાય',
            'buffalo_calf' => '🐃 ભેંસ બચ્ચું',
            'cow_calf'     => '🐄 ગાય બચ્ચું',
        ];
    }

    /** @var list<string> */
    public const ANIMAL_TYPES = ['buffalo', 'cow', 'buffalo_calf', 'cow_calf'];

    public static function normalizeAnimalType(?string $type): string
    {
        $t = strtolower(trim(str_replace(['-', ' '], '_', (string) $type)));

        if (in_array($t, self::ANIMAL_TYPES, true)) {
            return $t;
        }

        if (str_contains($t, 'buffalo') && str_contains($t, 'calf')) {
            return 'buffalo_calf';
        }
        if (str_contains($t, 'cow') && str_contains($t, 'calf')) {
            return 'cow_calf';
        }
        if ($t === 'calf') {
            return 'buffalo_calf';
        }

        return $t === 'cow' ? 'cow' : 'buffalo';
    }

    /**
     * Active head counts by animal_type from registered animals.
     *
     * @return array{buffalo: int, cow: int, buffalo_calf: int, cow_calf: int}
     */
    public static function activeCountsByAnimalType(bool $activeOnly = true): array
    {
        $counts = array_fill_keys(self::ANIMAL_TYPES, 0);

        $query = static::query();
        if ($activeOnly) {
            $query->where('status', 'active');
        }

        foreach ($query->selectRaw('animal_type, COUNT(*) as aggregate')
            ->groupBy('animal_type')
            ->get() as $row) {
            $key = self::normalizeAnimalType($row->animal_type);
            if (isset($counts[$key])) {
                $counts[$key] += (int) $row->aggregate;
            }
        }

        return $counts;
    }

    public static function totalHeadCount(bool $activeOnly = true): int
    {
        return array_sum(self::activeCountsByAnimalType($activeOnly));
    }

    public static function animalTypeSummaryLabel(array $counts): string
    {
        return sprintf(
            'ભેંસ %d · ગાય %d · ભેંસ બચ્ચું %d · ગાય બચ્ચું %d',
            $counts['buffalo'] ?? 0,
            $counts['cow'] ?? 0,
            $counts['buffalo_calf'] ?? 0,
            $counts['cow_calf'] ?? 0
        );
    }

    public function getNormalizedAnimalTypeAttribute(): string
    {
        return self::normalizeAnimalType($this->animal_type);
    }

    public function getDisplayLabelAttribute(): string
    {
        $name = $this->name ? ' - ' . $this->name : '';

        return $this->tag_number . $name . ' (' . $this->animal_type_label . ')';
    }

    /**
     * Mother animal label for calf birth tables: tag only when name is missing.
     */
    public function getMotherDisplayLabelAttribute(): string
    {
        if ($this->name) {
            return $this->tag_number . ' - ' . $this->name . ' (' . $this->animal_type_label . ')';
        }

        return $this->tag_number;
    }

    /**
     * Calf tag for birth tables — linked calf record preferred.
     */
    public function getCalfBirthTagAttribute(): string
    {
        return $this->birthCalf?->tag_number
            ?? $this->calf_tag_number
            ?? '—';
    }
}
