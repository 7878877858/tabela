<?php

namespace App\Services;

use App\Models\Asset;

class AssetCodeService
{
    public static function generate(): string
    {
        $maxId = (int) Asset::max('id');
        $next = $maxId + 1;

        do {
            $code = 'AST-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (Asset::where('asset_code', $code)->exists());

        return $code;
    }
}
