<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\ApkEntry;
use Carbon\Carbon;

class AdminApkController extends Controller
{
    public function index(Request $request)
    {
        $apkRootPath = public_path('apks');
        $groups = [];
        $groupIcons = [];
        $availableLibs = [];

        if (is_dir($apkRootPath)) {
            $directories = array_values(array_filter(scandir($apkRootPath), function ($item) use ($apkRootPath) {
                return $item !== '.' && $item !== '..' && is_dir($apkRootPath . DIRECTORY_SEPARATOR . $item);
            }));

            sort($directories, SORT_NATURAL | SORT_FLAG_CASE);

            // Preload existing entries for quick lookup
            $existing = ApkEntry::all()->groupBy(function ($e) { return $e->app_name.'/'.$e->filename; });

            foreach ($directories as $dir) {
                $folderPath = $apkRootPath . DIRECTORY_SEPARATOR . $dir;
                $files = array_values(array_filter(scandir($folderPath), function ($file) use ($folderPath) {
                    if (!is_file($folderPath . DIRECTORY_SEPARATOR . $file)) {
                        return false;
                    }
                    $lower = strtolower($file);
                    return str_ends_with($lower, '.apk') || str_ends_with($lower, '.apks');
                }));

                sort($files, SORT_NATURAL | SORT_FLAG_CASE);

                // Resolve group icon URL (prefer icon.*; support png, webp, jpg, jpeg, svg)
                $iconFile = null;
                $iconExts = ['png', 'webp', 'jpg', 'jpeg', 'svg'];
                foreach ($iconExts as $ext) {
                    $candidate = $folderPath . DIRECTORY_SEPARATOR . 'icon.' . $ext;
                    if (is_file($candidate)) {
                        $iconFile = 'icon.' . $ext;
                        break;
                    }
                }
                if ($iconFile === null) {
                    foreach ($iconExts as $ext) {
                        $matches = glob($folderPath . DIRECTORY_SEPARATOR . '*.' . $ext) ?: [];
                        if (!empty($matches)) {
                            $iconFile = basename($matches[0]);
                            break;
                        }
                    }
                }
                if ($iconFile !== null) {
                    $groupIcons[$dir] = rtrim(config('app.url'), '/') . '/apks/' . $dir . '/' . $iconFile;
                }

                // Collect additional library APKs from immediate subdirectories
                $libFiles = [];
                $libSearchPatterns = [
                    $folderPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.apk',
                    $folderPath . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.apks',
                ];
                $libPaths = [];
                foreach ($libSearchPatterns as $pattern) {
                    $matches = glob($pattern) ?: [];
                    if (!empty($matches)) {
                        $libPaths = array_merge($libPaths, $matches);
                    }
                }
                foreach ($libPaths as $libPath) {
                    $rel = basename(dirname($libPath)) . '/' . basename($libPath);
                    $libVersion = $this->extractVersionFromFilename(basename($libPath));
                    $libFiles[] = [
                        'value' => $rel,
                        'label' => $rel,
                        'url' => rtrim(config('app.url'), '/') . '/apks/' . $dir . '/' . $rel,
                        'version' => $libVersion,
                    ];
                }
                usort($libFiles, function ($a, $b) { return strnatcasecmp($a['label'], $b['label']); });
                $availableLibs[$dir] = $libFiles;

                foreach ($files as $fileName) {
                    $key = $dir . '/' . $fileName;
                    $record = $existing->get($key)?->first();
                    $url = rtrim(config('app.url'), '/') . '/apks/' . $dir . '/' . $fileName;
                    $version = $this->extractVersionFromFilename($fileName);

                    // Determine default lib selection if DB not set
                    $selectedLib = $record?->lib_filename;
                    if (!$selectedLib && !empty($availableLibs[$dir])) {
                        foreach ($availableLibs[$dir] as $lib) {
                            if (!empty($lib['version']) && $lib['version'] === $version) {
                                $selectedLib = $lib['value'];
                                break;
                            }
                        }
                    }

                    $groups[$dir][] = [
                        'filename' => $fileName,
                        'url' => $url,
                        'enabled' => $record !== null,
                        'add_date' => $record?->add_date,
                        'version' => $record?->version ?? $version,
                        'lib_filename' => $selectedLib,
                        'lib_install_order' => $record?->lib_install_order,
                        'offline_required' => (bool)($record?->offline_required),
                    ];
                }
            }
        }

        return view('admin.apks.index', [
            'groups' => $groups,
            'groupIcons' => $groupIcons,
            'availableLibs' => $availableLibs,
        ]);
    }

    public function enable(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required', 'string'],
            'filename' => ['required', 'string'],
            'lib_filename' => ['nullable', 'string'],
            'lib_install_order' => ['nullable', 'in:before,after'],
            'offline_required' => ['nullable', 'boolean'],
        ]);

        $appName = $data['app_name'];
        $filename = $data['filename'];

        $url = rtrim(config('app.url'), '/') . '/apks/' . $appName . '/' . $filename;
        $version = $this->extractVersionFromFilename($filename);
        $libFilename = $data['lib_filename'] ?? null;
        $libInstallOrder = $data['lib_install_order'] ?? null;

        // group icon (support png, webp, jpg, jpeg, svg; prefer icon.*)
        $folderPath = public_path('apks' . DIRECTORY_SEPARATOR . $appName);
        $iconUrl = null;
        $iconExts = ['png', 'webp', 'jpg', 'jpeg', 'svg'];
        foreach ($iconExts as $ext) {
            $candidate = $folderPath . DIRECTORY_SEPARATOR . 'icon.' . $ext;
            if (is_file($candidate)) {
                $iconUrl = rtrim(config('app.url'), '/') . '/apks/' . $appName . '/icon.' . $ext;
                break;
            }
        }
        if ($iconUrl === null) {
            foreach ($iconExts as $ext) {
                $matches = glob($folderPath . DIRECTORY_SEPARATOR . '*.' . $ext) ?: [];
                if (!empty($matches)) {
                    $iconUrl = rtrim(config('app.url'), '/') . '/apks/' . $appName . '/' . basename($matches[0]);
                    break;
                }
            }
        }

        // Resolve lib URL if provided and exists within group folder
        $libUrl = null;
        if ($libFilename) {
            $candidate = $folderPath . DIRECTORY_SEPARATOR . $libFilename;
            if (is_file($candidate)) {
                $libUrl = rtrim(config('app.url'), '/') . '/apks/' . $appName . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $libFilename);
                // If chosen lib has same version as main, keep existing DB lib if set; otherwise allow saving this selection
                $libVersion = $this->extractVersionFromFilename(basename($libFilename));
                if ($libVersion === $version) {
                    $existing = ApkEntry::where('app_name', $appName)->where('filename', $filename)->first();
                    if ($existing && $existing->lib_filename && $existing->lib_filename !== $libFilename) {
                        // Do not override; keep DB values
                        $libFilename = $existing->lib_filename;
                        $libUrl = $existing->lib_url;
                        $libInstallOrder = $existing->lib_install_order;
                    }
                }
            }
        }

        ApkEntry::updateOrCreate([
            'app_name' => $appName,
            'filename' => $filename,
        ], [
            'url' => $url,
            'version' => $version,
            'icon_url' => $iconUrl,
            'lib_filename' => $libFilename,
            'lib_url' => $libUrl,
            'lib_install_order' => $libInstallOrder,
            'offline_required' => (bool)($data['offline_required'] ?? false),
            'add_date' => Carbon::now(),
        ]);

        return Redirect::back()->with('status', __('app.apk_enabled'));
    }

    public function disable(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required', 'string'],
            'filename' => ['required', 'string'],
        ]);

        ApkEntry::where('app_name', $data['app_name'])
            ->where('filename', $data['filename'])
            ->delete();

        return Redirect::back()->with('status', __('app.apk_disabled'));
    }

    private function extractVersionFromFilename(string $filename): ?string
    {
        // Extract version-like sequence from filename.
        // Examples handled: 25.6.7.38, 1.2.3, 2_3_4, v1-2-3, build-123
        $name = pathinfo($filename, PATHINFO_FILENAME);
        // Normalize common separators to dots so boundaries are consistent
        $normalized = str_replace(['_', '-'], '.', $name);
        // Prefer versions with at least two octets (e.g., 1.2 or more), up to 4 octets
        if (preg_match_all('/\d+(?:\.\d+){1,3}/', $normalized, $matches) && !empty($matches[0])) {
            return end($matches[0]);
        }
        // Fallback: single number
        if (preg_match_all('/\d+/', $normalized, $matches) && !empty($matches[0])) {
            return end($matches[0]);
        }
        return null;
    }
}
