<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">{{ __('app.applications') }}</h2>
    </x-slot>

    <div class="py-6" x-data="appsPage()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">{{ __('app.install_apps') }}</h3>

                    @if($groups->isEmpty())
                        <div class="text-gray-600">{{ __('app.no_apks_found') }}</div>
                    @else
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
                                    ]);
                                    $hasVersion = $sorted->contains(function($e){ return !empty($e->version); });
                                @endphp
                                <div class="border rounded-lg p-4 hover:shadow cursor-pointer group" @click='openModal("{{ $appName }}", {!! $defaultPayload !!})'>
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
                                                                data-install-order="{{ $entry->lib_install_order }}">
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
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('app.select_devices_title') }} <span class="text-gray-500" x-text="currentAppTitle"></span></h3>
                    </div>
                    <div class="p-6 flex-1 overflow-hidden flex flex-col">
                        <div class="flex items-center justify-between mb-3 shrink-0">
                            <input type="text" class="w-1/2 border-gray-300 rounded-md" placeholder="{{ __('app.search_devices_placeholder') }}" x-model="search" @input="filterDevices()" />
                            <div class="space-x-2">
                                <button class="px-3 py-1 text-sm bg-gray-100 rounded" @click="selectAll()">{{ __('app.select_all') }}</button>
                                <button class="px-3 py-1 text-sm bg-gray-100 rounded" @click="unselectAll()">{{ __('app.unselect_all') }}</button>
                            </div>
                        </div>
                        <div class="border rounded overflow-hidden flex-1 overflow-y-auto">
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
                                            <td class="px-4 py-2 text-sm text-gray-700" x-text="d.custom_name || d.deviceName || d.name || ('#'+d.id)"></td>
                                            <td class="px-4 py-2 text-right">
                                                <input type="checkbox" class="h-4 w-4" :value="d.id" @click.stop @change="toggleDevice(d.id, $event)" :checked="selectedDeviceIds.has(d.id)">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t flex items-center justify-end space-x-3 shrink-0">
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
                selectedVersionByApp: {}, // appName -> {filename,url,lib_url,install_order}
                devices: [],
                filteredDevices: [],
                selectedDeviceIds: new Set(),
                search: '',
                openModal(appName, defaultVer) {
                    this.currentAppTitle = appName;
                    this.showModal = true;
                    this.selectedDeviceIds = new Set();
                    if (defaultVer && !this.selectedVersionByApp[appName]) {
                        this.selectedVersionByApp[appName] = defaultVer;
                    }
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
                    };
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
                    };
                    fetch("{{ route('apps.tasks.create') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    }).then(r => r.json()).then(d => {
                        if (d.success) {
                            this.closeModal();
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>


