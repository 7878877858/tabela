<?php

namespace App\Console\Commands;

use App\Services\CalfService;
use Illuminate\Console\Command;

class BackfillCalfAnimalsCommand extends Command
{
    protected $signature = 'animals:backfill-calves';

    protected $description = 'Create calf animal records from existing parent birth information';

    public function handle(CalfService $calfService): int
    {
        $this->info('Scanning parent animals for calf birth data...');

        $stats = $calfService->backfillLegacyCalves();

        $this->table(
            ['Created', 'Updated', 'Skipped'],
            [[$stats['created'], $stats['updated'], $stats['skipped']]]
        );

        $this->info('Calf backfill complete.');

        return self::SUCCESS;
    }
}
