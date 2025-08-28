<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">{{ __('app.applications') }}</h2>
            <div class="flex items-center space-x-4">
                <x-language-switcher />
                <x-user-menu />
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="appsPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">{{ __('app.install_apps') }}</h3>

                    @if($groups->isEmpty())
                        <div class="text-gray-600">{{ __('app.no_apks_found') }}</div>
                    @else
                        <div class="mb-4">
                            <input type="text" class="w-full sm:w-1/2 border-gray-300 rounded-md" placeholder="{{ __('app.search_apps_placeholder') }}" x-model="appQuery">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($groups as $appName => $entries)
                                @php
                                    $sorted = $entries->sortByDesc(fn($e) => $e->version ?? $e->filename)->values();
                                    $first = $sorted->first();
                                    $defaultPayload = json_encode([
                                        'filename' => $first->filename ?? null,
                                        'url' => $first->url ?? null,
                                        'lib_url' => $first->lib_url ?? null,
                                        'install_order' => $first->lib_install_order ?? null,
                                        'version' => $first->version ?? null,
                                        'offline' => ($first->offline_required ?? false) ? '1' : '0',
                                    ]);
                                    $hasVersion = $sorted->contains(function($e){ return !empty($e->version); });
                                @endphp
                                <div class="border rounded-lg p-4 hover:shadow cursor-pointer group" x-show="appFilterMatch('{{ $appName }}')" @click='openModal("{{ $appName }}", {!! $defaultPayload !!})'>
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ $first->icon_url ?? asset('favicon.ico') }}" alt="icon" class="h-12 w-12 rounded" />
                                        <div class="flex-1">
                                            <div class="text-base font-semibold text-gray-900">{{ $appName }}</div>
                                            <div class="mt-2">
                                                <label class="text-sm text-gray-600">{{ __('app.select_version') }}</label>
                                                @if($hasVersion)
                                                    <select class="mt-1 block w-full border-gray-300 rounded-md text-sm" @click.stop x-on:change="onVersionChange('{{ $appName }}', $event)">
                                                        @foreach($sorted as $entry)
                                                            <option value="{{ $entry->filename }}"
                                                                data-filename="{{ $entry->filename }}"
                                                                data-url="{{ $entry->url }}"
                                                                data-lib-url="{{ $entry->lib_url }}"
                                                                data-install-order="{{ $entry->lib_install_order }}"
                                                                data-version="{{ $entry->version }}"
                                                                data-offline="{{ $entry->offline_required ? '1' : '0' }}">
                                                                {{ $entry->version ?? $entry->filename }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <div class="mt-1 text-gray-800">-</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900 opacity-50" @click="closeModal()"></div>
                <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl relative z-10 max-h-[90vh] flex flex-col">
                    <div class="px-6 py-4 border-b shrink-0">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.select_devices_title') }}
                            <span class="text-gray-700" x-text="currentAppTitle"></span>
                            <span class="text-gray-500" x-show="currentVersionLabel" x-text="' (' + currentVersionLabel + ')'"></span>
                        </h3>
                    </div>
                    <div class="p-6 flex-1 overflow-hidden flex flex-col">
                        <div class="flex items-center justify-between mb-3 shrink-0">
                            <input type="text" class="w-1/2 border-gray-300 rounded-md" placeholder="{{ __('app.search_devices_placeholder') }}" x-model="search" @input="filterDevices()" />
                            <div class="space-x-2">
                                <button class="px-3 py-1 text-sm bg-gray-100 rounded" @click="selectAll()">{{ __('app.select_all') }}</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 rounded" @click="unselectAll()">{{ __('app.unselect_all') }}</button>
                            </div>
                        </div>
                        <div class="mb-3" x-show="apiErrorMessage" x-cloak>
                            <div class="px-3 py-2 rounded bg-red-50 text-red-800 text-sm border border-red-200" x-text="apiErrorMessage"></div>
                        </div>
                        <div class="mb-3" x-show="offlineOnly" x-cloak>
                            <div class="px-3 py-2 rounded bg-yellow-50 text-yellow-800 text-sm border border-yellow-200">
                                {{ __('app.offline_required_warning') }}
                            </div>
                        </div>
                        <!-- Permissions dropdown -->
                        <div class="mb-3 shrink-0">
                            <label class="block text-sm text-gray-600 mb-1">{{ __('app.required_permissions') }}</label>
                            <div class="relative">
                                <button type="button" class="w-full border rounded-md px-3 py-2 text-left" @click="openPerms = !openPerms">
                                    <span x-text="selectedPermissions.length ? selectedPermissions.join(', ') : '{{ __('app.none') }}'"></span>
                                </button>
                                <div class="mt-1 w-full bg-white border rounded-md shadow max-h-[50vh] overflow-y-auto overscroll-contain" x-show="openPerms" x-cloak @click.outside="openPerms = false">
                                    <div class="p-2 border-b sticky top-0 bg-white z-10">
                                        <input type="text" class="w-full border-gray-300 rounded-md" placeholder="{{ __('app.search') }}" x-model="permQuery">
                                    </div>
                                    <ul class="divide-y">
                                        <template x-for="perm in filteredPerms()" :key="perm.name">
                                            <li class="px-3 py-2 hover:bg-gray-50 flex items-start justify-between cursor-pointer relative" @click="togglePermission(perm.name)">
                                                <div class="flex items-start space-x-2">
                                                    <input type="checkbox" class="mt-1" :checked="selectedPermissions.includes(perm.name)" @click.stop @change="togglePermission(perm.name)">
                                                    <div>
                                                        <div class="text-sm font-medium" x-text="perm.name"></div>
                                                    </div>
                                                </div>
                                                <div class="ml-2 text-gray-400 relative" title="{{ __('app.more_info') }}" @click.stop x-data="{ showTip: false }" @mouseenter="showTip = true" @mouseleave="showTip = false">
                                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-100 text-gray-500 text-xs">?</span>
                                                    <div x-show="showTip" x-transition x-cloak class="absolute z-20 top-6 right-0 w-64 bg-white border border-gray-200 rounded-md shadow-lg p-2 text-xs text-gray-700">
                                                        <template x-if="perm.name">
                                                            <div x-text="(function(){
                                                                try {
                                                                    const key = perm.name.replace('android.permission.', '');
                                                                    const dict = @js(__('app.perm'));
                                                                    if (typeof dict === 'object' && dict && dict[key]) return dict[key];
                                                                } catch (e) {}
                                                                return perm.description || '{{ __('app.no_data') }}';
                                                            })()"></div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                        <li class="p-3 text-sm text-gray-500" x-show="filteredPerms().length === 0">{{ __('app.no_data') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded overflow-hidden flex-1 overflow-y-auto pb-24">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('app.device_name') }}</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('app.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="devicesTableBody">
                                    <template x-if="filteredDevices.length === 0">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-center text-gray-500">{{ __('app.no_devices_found') }}</td>
                                        </tr>
                                    </template>
                                    <template x-for="d in filteredDevices" :key="d.id">
                                        <tr class="cursor-pointer hover:bg-gray-50" :class="selectedDeviceIds.has(d.id) ? 'bg-blue-50' : ''" @click="toggleRow(d.id)">
                                            <td class="px-4 py-2 text-sm text-gray-700" x-text="d.id"></td>
                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                <span class="inline-flex items-center space-x-2">
                                                    <span class="inline-block h-2.5 w-2.5 rounded-full"
                                                          :class="(d.deviceStatus || '').toLowerCase() === 'online' ? 'bg-gradient-to-r from-blue-500 to-purple-600' : 'bg-gray-300'"></span>
                                                    <span x-text="d.custom_name || d.deviceName || d.name || ('#'+d.id)"></span>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <input type="checkbox" class="h-4 w-4" :value="d.id" @click.stop @change="toggleDevice(d.id, $event)" :checked="selectedDeviceIds.has(d.id)" :disabled="offlineOnly && (String(d.deviceStatus||'').toLowerCase() === 'online')">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t flex items-center justify-end space-x-3 shrink-0 sticky bottom-0 bg-white">
                        <button class="px-4 py-2 bg-gray-100 rounded" @click="closeModal()">{{ __('app.cancel') }}</button>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="selectedDeviceIds.size === 0" @click="confirmInstall()">{{ __('app.confirm_install') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function appsPage() {
            return {
                showModal: false,
                currentAppTitle: '',
                currentVersionLabel: '',
                selectedVersionByApp: {}, // appName -> {filename,url,lib_url,install_order}
                devices: [],
                filteredDevices: [],
                selectedDeviceIds: new Set(),
                search: '',
                offlineOnly: false,
                appQuery: '',
                apiErrorMessage: '',
                // Permissions UI state
                openPerms: false,
                permQuery: '',
                hoverDesc: '',
                selectedPermissions: [],
                allPermissions: [
                    // Actual, commonly used runtime permissions subset (full list is large)
                    { name: 'android.permission.ACCESS_COARSE_LOCATION', description: 'Approximate device location.' },
                    { name: 'android.permission.ACCESS_FINE_LOCATION', description: 'Precise device location.' },
                    { name: 'android.permission.ACTIVITY_RECOGNITION', description: 'Physical activity recognition.' },
                    { name: 'android.permission.BLUETOOTH_CONNECT', description: 'Connect to paired Bluetooth devices.' },
                    { name: 'android.permission.BLUETOOTH_SCAN', description: 'Scan for Bluetooth devices.' },
                    { name: 'android.permission.CAMERA', description: 'Access camera device.' },
                    { name: 'android.permission.POST_NOTIFICATIONS', description: 'Post notifications.' },
                    { name: 'android.permission.READ_CALENDAR', description: 'Read user calendar data.' },
                    { name: 'android.permission.WRITE_CALENDAR', description: 'Write user calendar data.' },
                    { name: 'android.permission.READ_CONTACTS', description: 'Read contacts.' },
                    { name: 'android.permission.WRITE_CONTACTS', description: 'Write contacts.' },
                    { name: 'android.permission.RECORD_AUDIO', description: 'Record audio.' },
                    { name: 'android.permission.READ_MEDIA_IMAGES', description: 'Read media images.' },
                    { name: 'android.permission.READ_MEDIA_VIDEO', description: 'Read media video.' },
                    { name: 'android.permission.READ_MEDIA_AUDIO', description: 'Read media audio.' },
                    { name: 'android.permission.READ_MEDIA_VISUAL_USER_SELECTED', description: 'Access user selected media.' },
                    { name: 'android.permission.READ_PHONE_NUMBERS', description: 'Read phone numbers.' },
                    { name: 'android.permission.READ_PHONE_STATE', description: 'Read phone state.' },
                    { name: 'android.permission.CALL_PHONE', description: 'Directly call phone numbers.' },
                    { name: 'android.permission.SEND_SMS', description: 'Send SMS messages.' },
                    { name: 'android.permission.READ_SMS', description: 'Read SMS messages.' },
                    { name: 'android.permission.RECEIVE_SMS', description: 'Receive SMS messages.' },
                    { name: 'android.permission.NEARBY_WIFI_DEVICES', description: 'Discover nearby Wi‑Fi devices.' },
                    { name: 'android.permission.BODY_SENSORS', description: 'Access body sensors.' },
                    { name: 'android.permission.UWB_RANGING', description: 'Ultra‑wideband ranging.' },
                    { name: 'android.permission.USE_FULL_SCREEN_INTENT', description: 'Use full screen notification intents.' },
                    { name: 'android.permission.VIBRATE', description: 'Control vibrator.' },
                    { name: 'android.permission.WAKE_LOCK', description: 'Prevent device from sleeping.' }
                ],
                openModal(appName, defaultVer) {
                    this.currentAppTitle = appName;
                    this.showModal = true;
                    this.selectedDeviceIds = new Set();
                    if (defaultVer && !this.selectedVersionByApp[appName]) {
                        this.selectedVersionByApp[appName] = defaultVer;
                    }
                    // Set header version label from default or existing selection
                    const verObj = this.selectedVersionByApp[appName];
                    this.currentVersionLabel = verObj ? (verObj.version || verObj.filename || '') : '';
                    // Set offlineOnly flag based on selected version option data-offline
                    try {
                        const selectEl = document.querySelector('select.mt-1');
                        if (selectEl) {
                            const opt = selectEl.selectedOptions && selectEl.selectedOptions[0];
                            this.offlineOnly = !!(opt && opt.dataset && String(opt.dataset.offline) === '1');
                        } else {
                            this.offlineOnly = !!(verObj && verObj.offline === '1');
                        }
                    } catch (e) { this.offlineOnly = false; }
                    if (this.devices.length === 0) {
                        fetch("{{ route('apps.devices') }}")
                            .then(r => r.json())
                            .then(d => {
                                if (d.success) {
                                    this.devices = d.devices || [];
                                    this.filteredDevices = this.devices;
                                }
                            });
                    } else {
                        this.filteredDevices = this.filterList(this.search);
                    }
                },
                appFilterMatch(name) {
                    const q = (this.appQuery || '').toLowerCase().trim();
                    if (!q) return true;
                    return String(name).toLowerCase().includes(q);
                },
                filteredPerms() {
                    const q = this.permQuery.toLowerCase().trim();
                    if (!q) return this.allPermissions;
                    return this.allPermissions.filter(p => p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q));
                },
                togglePermission(name) {
                    const i = this.selectedPermissions.indexOf(name);
                    if (i >= 0) this.selectedPermissions.splice(i, 1); else this.selectedPermissions.push(name);
                },
                closeModal() {
                    this.showModal = false;
                },
                onVersionChange(appName, e) {
                    const opt = e.target.selectedOptions && e.target.selectedOptions[0];
                    if (!opt) return;
                    this.selectedVersionByApp[appName] = {
                        filename: opt.dataset.filename,
                        url: opt.dataset.url,
                        lib_url: opt.dataset.libUrl || null,
                        install_order: opt.dataset.installOrder || null,
                        version: opt.dataset.version || null,
                    };
                    this.currentVersionLabel = (opt.dataset.version || opt.dataset.filename || '');
                    this.offlineOnly = String(opt.dataset.offline || '0') === '1';
                },
                filterList(q) {
                    const query = (q || '').toLowerCase();
                    return this.devices.filter(d => {
                        const name = (d.custom_name || d.deviceName || d.name || '').toLowerCase();
                        return name.includes(query) || String(d.id).includes(query);
                    });
                },
                filterDevices() {
                    this.filteredDevices = this.filterList(this.search);
                },
                toggleDevice(id, ev) {
                    if (ev.target.checked) this.selectedDeviceIds.add(id); else this.selectedDeviceIds.delete(id);
                },
                toggleRow(id) {
                    if (this.selectedDeviceIds.has(id)) {
                        this.selectedDeviceIds.delete(id);
                    } else {
                        this.selectedDeviceIds.add(id);
                    }
                },
                selectAll() {
                    this.filteredDevices.forEach(d => this.selectedDeviceIds.add(d.id));
                },
                unselectAll() {
                    this.filteredDevices.forEach(d => this.selectedDeviceIds.delete(d.id));
                },
                confirmInstall() {
                    const appName = this.currentAppTitle;
                    const ver = this.selectedVersionByApp[appName];
                    const payload = {
                        device_ids: Array.from(this.selectedDeviceIds),
                        app_title: appName,
                        app_filename: ver?.filename || '',
                        app_url: ver?.url || '',
                        lib_url: ver?.lib_url || null,
                        install_order: ver?.install_order || null,
                        permissions: this.selectedPermissions,
                    };
                    fetch("{{ route('apps.tasks.create') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    }).then(async (r) => {
                        let d = {};
                        try { d = await r.clone().json(); } catch (e) {}
                        if (!r.ok) {
                            this.apiErrorMessage = (d && d.message) ? d.message : 'Request failed';
                            if (d && Array.isArray(d.online_device_ids)) {
                                d.online_device_ids.forEach(id => this.selectedDeviceIds.delete(id));
                            }
                            return;
                        }
                        if (d && d.success) {
                            this.apiErrorMessage = '';
                            this.closeModal();
                        }
                    }).catch((e) => {
                        this.apiErrorMessage = 'Network error';
                    });
                }
            }
        }
    </script>
</x-app-layout>


