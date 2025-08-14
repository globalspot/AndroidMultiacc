<?php

// assign_devices.php
// Configure parameters below, then run: php assign_devices.php

declare(strict_types=1);

use App\Models\DeviceAssignment;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

// Ensure we are in project root when executed from elsewhere
chdir(__DIR__);

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

// ---------------
// Parameters (edit these values)
// ---------------

// Target user ID to assign devices to (required)
$userId = 2; // e.g. 123

// Gate URL to filter devices by (required, exact match)
$gateUrl = '185.225.32.145:4723'; // e.g. 'https://my-gate'

// createDate limit value (UNIX timestamp, integer) and operator
// Operator: '>=' for created after/on; '<=' for created before/on
$dateOperator = '>='; // '>=' or '<='
$dateLimit = mktime(0, 0, 0, 8, 7, 2025); // e.g. 1693526400

// Access level for created/updated assignments: 'user' | 'manager' | 'owner'
$accessLevel = 'user';

// Optional device group id to attach to the assignment, or null
$deviceGroupId = 1; // e.g. 5 or null

// Processing chunk size when scanning devices
$chunkSize = 1000;

// Validate required config
if ($userId <= 0) {
    fwrite(STDERR, "Config error: 'userId' must be a positive integer (edit assign_devices.php)\n");
    exit(1);
}
if ($gateUrl === '') {
    fwrite(STDERR, "Config error: 'gateUrl' must be a non-empty string (edit assign_devices.php)\n");
    exit(1);
}
if (!in_array($dateOperator, ['>=', '<='], true)) {
    fwrite(STDERR, "Config error: 'dateOperator' must be '>=' or '<=' (edit assign_devices.php)\n");
    exit(1);
}
if ($dateLimit <= 0) {
    fwrite(STDERR, "Config error: 'dateLimit' must be a positive UNIX timestamp (edit assign_devices.php)\n");
    exit(1);
}

// ---------------
// Assignment Logic
// ---------------

set_time_limit(0);

$totalProcessed = 0;
$totalAssigned = 0;
$totalUpdated = 0;
$errors = 0;

// Build query to organic DB (mysql_second)
$query = DB::connection('mysql_second')
    ->table('goProfiles')
    ->select('id')
    ->where('valid', 1)
    ->where('gateUrl', $gateUrl)
    ->orderBy('id')
    ->limit(500);

// Apply date limit
$query->where('createDate', $dateOperator, $dateLimit);

// Process in chunks to avoid memory pressure
$createLimit = 1;
$query->chunk($chunkSize, function ($rows) use (&$totalProcessed, &$totalAssigned, &$totalUpdated, &$errors, $userId, $accessLevel, $deviceGroupId, $createLimit) {
    foreach ($rows as $row) {
        $totalProcessed++;
        $deviceId = (string)$row->id;
        try {
            // Use updateOrCreate to respect unique(user_id, device_id)
            $assignment = DeviceAssignment::updateOrCreate(
                [
                    'user_id' => $userId,
                    'device_id' => $deviceId,
                ],
                [
                    'device_group_id' => $deviceGroupId,
                    'access_level' => $accessLevel,
                    'is_active' => true,
                ]
            );

            if ($assignment->wasRecentlyCreated) {
                $totalAssigned++;
                if ($totalAssigned >= $createLimit) {
                    break;
                }
            } else {
                $totalUpdated++;
            }
        } catch (\Throwable $e) {
            $errors++;
            fwrite(STDERR, sprintf("Error assigning device %s to user %d: %s\n", $deviceId, $userId, $e->getMessage()));
        }
    }
});

// Summary
echo "Assignment completed.\n";
echo sprintf("Gate URL: %s\n", $gateUrl);
echo sprintf("createDate %s %d\n", $dateOperator, $dateLimit);
echo sprintf("Target user_id: %d\n", $userId);
echo sprintf("Access level: %s\n", $accessLevel);
echo sprintf("Processed: %d, Created: %d, Updated: %d, Errors: %d\n",
    $totalProcessed, $totalAssigned, $totalUpdated, $errors);

exit($errors > 0 ? 2 : 0);


