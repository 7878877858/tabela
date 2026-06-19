<?php

namespace App\Support;

use Illuminate\Http\Request;

class ListPagination
{
    public const DEFAULT = 25;

    /** @var list<int> */
    public const OPTIONS = [10, 25, 50, 100];

    public static function resolvePerPage(Request $request, ?int $default = null): int
    {
        $perPage = (int) $request->get('per_page', $default ?? self::DEFAULT);

        return in_array($perPage, self::OPTIONS, true)
            ? $perPage
            : ($default ?? self::DEFAULT);
    }
}
