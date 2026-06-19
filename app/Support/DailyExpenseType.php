<?php

namespace App\Support;

class DailyExpenseType
{
    public const MEDICINE = 'medicine';

    public const DOCTOR_FEE = 'doctor_fee';

    public const LABOUR = 'labour';

    public const DIESEL = 'diesel';

    public const TRANSPORT = 'transport';

    public const OTHER_DAILY = 'other_daily';

    public static function options(): array
    {
        return [
            self::MEDICINE    => '💊 દવા',
            self::DOCTOR_FEE  => '👨‍⚕️ ડૉક્ટર ફી',
            self::LABOUR      => '👷 મજૂરી',
            self::DIESEL      => '⛽ ડીઝલ',
            self::TRANSPORT   => '🚚 પરિવહન',
            self::OTHER_DAILY => '📋 અન્ય દૈનિક ખર્ચ',
        ];
    }

    public static function ledgerCategory(string $type): string
    {
        return match ($type) {
            self::MEDICINE   => 'medicine',
            self::DOCTOR_FEE => 'veterinary',
            self::LABOUR     => 'labour',
            default          => 'other',
        };
    }

    public static function label(string $type): string
    {
        return self::options()[$type] ?? $type;
    }
}
