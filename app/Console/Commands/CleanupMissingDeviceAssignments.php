<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupMissingDeviceAssignments extends Command
{
    protected $signature = 'devices:cleanup-missing-assignments {--chunk=1000 : Number of device IDs to check per batch}';

    protected $description = 'Delete device assignments that reference device IDs not present in mysql_second.goProfiles';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $totalDeleted = 0;
        $processedIds = 0;

        // Iterate distinct device_ids from device_assignments in chunks
        DB::table('device_assignments')
            ->select('device_id')
            ->distinct()
            ->orderBy('device_id')
            ->chunk($chunkSize, function ($rows) use (&$totalDeleted, &$processedIds) {
                $deviceIds = collect($rows)
                    ->pluck('device_id')
                    ->filter()
                    ->map(fn($id) => (string) $id)
                    ->values()
                    ->all();

                if (empty($deviceIds)) {
                    return true;
                }

                // Check existence in external mysql_second.goProfiles
                $existing = DB::connection('mysql_second')
                    ->table('goProfiles')
                    ->whereIn('id', $deviceIds)
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->all();

                $existingMap = array_flip($existing);
                $missing = array_values(array_filter($deviceIds, fn($id) => !isset($existingMap[$id])));

                if (!empty($missing)) {
                    $deleted = DB::table('device_assignments')->whereIn('device_id', $missing)->delete();
                    $totalDeleted += (int) $deleted;
                }

                $processedIds += count($deviceIds);
                return true;
            });

        $this->info("Processed device IDs: {$processedIds}; Deleted assignments: {$totalDeleted}");
        return self::SUCCESS;
    }
}


