<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');

})->name('home');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Role-based routes
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\DashboardController::class, 'admin'])->name('admin.dashboard');
    // Admin APK management
    Route::get('/admin/apks', [App\Http\Controllers\AdminApkController::class, 'index'])->name('admin.apks.index');
    Route::post('/admin/apks/enable', [App\Http\Controllers\AdminApkController::class, 'enable'])->name('admin.apks.enable');
    Route::post('/admin/apks/disable', [App\Http\Controllers\AdminApkController::class, 'disable'])->name('admin.apks.disable');
});

Route::middleware(['auth', 'verified', 'role:manager'])->group(function () {
    Route::get('/manager', [App\Http\Controllers\DashboardController::class, 'manager'])->name('manager.dashboard');
});

Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::get('/user', [App\Http\Controllers\DashboardController::class, 'user'])->name('user.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Device management routes
    Route::get('/devices', [App\Http\Controllers\DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/chunk', [App\Http\Controllers\DeviceController::class, 'chunk'])->name('devices.chunk');
    Route::post('/devices/create', [App\Http\Controllers\DeviceController::class, 'createDevice'])->name('devices.create');
    Route::post('/devices/batch-create', [App\Http\Controllers\DeviceController::class, 'batchCreate'])->name('devices.batchCreate');

    Route::post('/devices/assign', [App\Http\Controllers\DeviceController::class, 'assign'])->name('devices.assign');
    Route::delete('/devices/{deviceId}/unassign/{userId}', [App\Http\Controllers\DeviceController::class, 'unassign'])->name('devices.unassign');
    Route::delete('/devices/{deviceId}/cancel-assignment', [App\Http\Controllers\DeviceController::class, 'cancelAssignment'])->name('devices.cancelAssignment');
    Route::post('/devices/groups', [App\Http\Controllers\DeviceController::class, 'createGroup'])->name('devices.createGroup');
    Route::get('/devices/search', [App\Http\Controllers\DeviceController::class, 'search'])->name('devices.search');
    
    // Device automation routes
Route::post('/devices/{deviceId}/start', [App\Http\Controllers\DeviceController::class, 'startDevice'])->name('devices.start');
Route::post('/devices/{deviceId}/stop', [App\Http\Controllers\DeviceController::class, 'stopDevice'])->name('devices.stop');
Route::get('/devices/{deviceId}/status', [App\Http\Controllers\DeviceController::class, 'getDeviceStatus'])->name('devices.status');

// Background refresh routes
Route::get('/devices/refresh/all', [App\Http\Controllers\DeviceController::class, 'refreshAllDevices'])->name('devices.refresh.all');
Route::get('/devices/refresh/screenshots', [App\Http\Controllers\DeviceController::class, 'refreshScreenshots'])->name('devices.refresh.screenshots');
Route::get('/devices/{deviceId}/screenshot', [App\Http\Controllers\DeviceController::class, 'getScreenshot'])->name('devices.getScreenshot');

// Custom device name routes
Route::post('/devices/{deviceId}/custom-name', [App\Http\Controllers\DeviceController::class, 'updateCustomName'])->name('devices.updateCustomName');
Route::delete('/devices/{deviceId}/custom-name', [App\Http\Controllers\DeviceController::class, 'deleteCustomName'])->name('devices.deleteCustomName');

// Device group limit management routes (admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/devices/groups/{groupId}/limit', [App\Http\Controllers\DeviceController::class, 'getGroupLimit'])->name('devices.getGroupLimit');
    Route::put('/devices/groups/{groupId}/limit', [App\Http\Controllers\DeviceController::class, 'updateGroupLimit'])->name('devices.updateGroupLimit');
    // Created device limit routes
    Route::get('/devices/groups/{groupId}/created-limit', [App\Http\Controllers\DeviceController::class, 'getCreatedGroupLimit'])->name('devices.getCreatedGroupLimit');
    Route::put('/devices/groups/{groupId}/created-limit', [App\Http\Controllers\DeviceController::class, 'updateCreatedGroupLimit'])->name('devices.updateCreatedGroupLimit');
    Route::get('/devices/by-gate-url/{gateUrl}', [App\Http\Controllers\DeviceController::class, 'getDevicesByGateUrl'])->name('devices.getByGateUrl');
    
    // User assignment routes (admin only)
    Route::get('/user-assignments', [App\Http\Controllers\UserAssignmentController::class, 'index'])->name('user-assignments.index');
    Route::post('/user-assignments/assign', [App\Http\Controllers\UserAssignmentController::class, 'assignUserToGroup'])->name('user-assignments.assign');
    Route::delete('/user-assignments/remove', [App\Http\Controllers\UserAssignmentController::class, 'removeUserFromGroup'])->name('user-assignments.remove');
});

// Manager device assignment routes
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/device-assignments', [App\Http\Controllers\UserAssignmentController::class, 'deviceAssignmentInterface'])->name('device-assignments.index');
    Route::post('/device-assignments/assign', [App\Http\Controllers\UserAssignmentController::class, 'assignDeviceToUser'])->name('device-assignments.assign');
    Route::delete('/device-assignments/remove', [App\Http\Controllers\UserAssignmentController::class, 'removeDeviceAssignment'])->name('device-assignments.remove');
    Route::get('/device-assignments/group-users/{groupId}', [App\Http\Controllers\UserAssignmentController::class, 'getGroupUsers'])->name('device-assignments.groupUsers');
    Route::get('/device-assignments/gate-devices/{groupId}', [App\Http\Controllers\UserAssignmentController::class, 'getGateUrlDevices'])->name('device-assignments.gateDevices');
    Route::get('/device-assignments/my-assignments', [App\Http\Controllers\UserAssignmentController::class, 'getMyAssignments'])->name('device-assignments.myAssignments');
    Route::get('/device-assignments/managed-assignments', [App\Http\Controllers\UserAssignmentController::class, 'getManagedAssignments'])->name('device-assignments.managedAssignments');
    // Group device bulk assignment helpers
    Route::get('/device-assignments/free-stats/{groupId}', [App\Http\Controllers\UserAssignmentController::class, 'getFreeDeviceStats'])->name('device-assignments.freeStats');
    Route::post('/device-assignments/assign-free', [App\Http\Controllers\UserAssignmentController::class, 'assignFreeDevicesToUser'])->name('device-assignments.assignFree');
    // Group invite generation (manager)
    Route::post('/device-assignments/{groupId}/invites', [App\Http\Controllers\GroupInviteController::class, 'generate'])->name('group-invites.generate');
});

// Group invite consumption (authed user)
Route::middleware(['auth'])->group(function () {
    Route::post('/invites/{token}/accept', [App\Http\Controllers\GroupInviteController::class, 'accept'])->name('group-invites.accept');
});

// Publicly viewable invite landing (shows confirmation if logged in)
Route::get('/invites/{token}', [App\Http\Controllers\GroupInviteController::class, 'show'])->name('group-invites.show');

// Screenshot request route (available to all authenticated users)
Route::post('/devices/{deviceId}/request-screenshot', [App\Http\Controllers\DeviceController::class, 'requestScreenshot'])->name('devices.requestScreenshot');

    // Apps install tasks
    Route::get('/apps', [App\Http\Controllers\AppsController::class, 'index'])->name('apps.index');
    Route::get('/apps/devices', [App\Http\Controllers\AppsController::class, 'devices'])->name('apps.devices');
    Route::post('/apps/tasks', [App\Http\Controllers\AppsController::class, 'createTasks'])->name('apps.tasks.create');
    
    // Automation Macros
    Route::prefix('automation/macros')->name('automation.macros.')->group(function () {
        Route::get('/', [App\Http\Controllers\AutomationMacrosController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\AutomationMacrosController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\AutomationMacrosController::class, 'store'])->name('store');
        Route::get('/{macro}', [App\Http\Controllers\AutomationMacrosController::class, 'show'])->name('show');
        Route::get('/{macro}/edit', [App\Http\Controllers\AutomationMacrosController::class, 'edit'])->name('edit');
        Route::put('/{macro}', [App\Http\Controllers\AutomationMacrosController::class, 'update'])->name('update');
        Route::delete('/{macro}', [App\Http\Controllers\AutomationMacrosController::class, 'destroy'])->name('destroy');
        Route::post('/{macro}/execute', [App\Http\Controllers\AutomationMacrosController::class, 'execute'])->name('execute');
    });
    
    Route::get('/automation/action-types', [App\Http\Controllers\AutomationMacrosController::class, 'getActionTypes'])->name('automation.action-types');
});

// Language switching route
Route::get('/language/{locale}', [App\Http\Controllers\LanguageController::class, 'switchLanguage'])->name('language.switch');

require __DIR__.'/auth.php';
