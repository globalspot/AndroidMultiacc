<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v9.2.4/ol.css" integrity="sha384-TD0YxjZ2J2wGQ0Mm/0uG9G8zQYap6jzjXf3gH0S4oF6v6H0wq0z2pQxWJ8fKpG8N" crossorigin="anonymous" />
    @endpush
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.device_management') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-language-switcher />
                <x-user-menu />
            </div>
        </div>
    </x-slot>

    <style>
        /* Select2 tag cloud gradient styling to match site theme */
        .select2-container--default .select2-selection--multiple {
            border-color: #d1d5db; /* gray-300 */
            padding: 4px;
            min-height: 44px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 0.5rem; /* rounded */
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            padding: 4px 8px;
            margin-top: 6px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.9);
            margin-right: 6px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered li.select2-search.select2-search--inline .select2-search__field {
            margin-top: 8px;
        }
        /* Ensure proper text display in device cards */
        .device-name-display {
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            line-height: 1.4;
        }
        
        .device-card .text-sm {
            line-height: 1.5;
            word-wrap: break-word;
        }
        
        /* Ensure proper spacing in device info */
        .device-card .mb-3:last-child {
            margin-bottom: 0;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 1024px) {
            /* Stack filters vertically on mobile */
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            /* Make search input full width on mobile */
            .search-input {
                width: 100%;
            }
        }
        
        @media (max-width: 640px) {
            .device-name-display {
                font-size: 1rem;
                line-height: 1.3;
            }
            
            /* Ensure device cards have proper spacing on mobile */
            .device-card {
                padding: 1rem;
            }

            /* Mobile-only layout changes for device cards (no impact on desktop) */
            .device-card .device-flex {
                flex-direction: column;
                gap: 1rem;
            }
            .device-card .device-flex > * + * {
                margin-left: 0 !important; /* cancel space-x-* from desktop */
            }
            .device-card .screenshot-container {
                width: 100% !important;
                height: auto !important;
                min-height: 16rem; /* keep placeholder visible when no image */
            }
            .device-card .screenshot-container img {
                width: 100% !important;
                height: auto !important;
                object-fit: contain !important; /* show full screenshot height without cropping */
            }

            /* Place user filter on new line on mobile only */
            .group-user-filter {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }
            .group-user-filter form {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                width: 100%;
            }
            .group-user-filter select,
            .group-user-filter label {
                width: 100%;
            }
        }

        /* Normalize OpenLayers controls inside map picker */
        #mapPicker .ol-control {
            font-family: inherit;
            pointer-events: none; /* avoid invisible boxes blocking map */
        }
        #mapPicker .ol-control button,
        #mapPicker .ol-control input {
            pointer-events: auto; /* allow interaction only on actual controls */
        }
        #mapPicker .ol-control button {
            width: 32px;
            height: 32px;
            padding: 0;
            line-height: 30px;
            font-size: 16px;
            border-radius: 0.375rem; /* rounded-md */
            background-color: rgba(255, 255, 255, 0.95);
            color: #111827; /* gray-900 */
            border: 1px solid #d1d5db; /* gray-300 */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }
        /* Apply same styling to batch picker controls */
        #batchMapPicker .ol-control {
            font-family: inherit;
            pointer-events: none;
        }
        #batchMapPicker .ol-control button,
        #batchMapPicker .ol-control input { pointer-events: auto; }
        #batchMapPicker .ol-control button {
            width: 32px;
            height: 32px;
            padding: 0;
            line-height: 30px;
            font-size: 16px;
            border-radius: 0.375rem;
            background-color: rgba(255,255,255,0.95);
            color: #111827;
            border: 1px solid #d1d5db;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }
        /* Force bottom-right placement for all controls */
        .ol-control {
            left: auto !important;
            right: 8px !important;
            width: auto !important;
            max-width: 44px; /* keep controls narrow */
        }
        /* Keep zoom container tight to its buttons */
        #mapPicker .ol-zoom {
            display: inline-flex !important;
            flex-direction: column;
            gap: 6px;
            top: auto !important;
            left: auto !important;
            bottom: 56px !important;
            right: 8px !important;
            width: auto !important;
            max-width: 44px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #mapPicker .ol-rotate {
            top: auto !important;
            left: auto !important;
            bottom: 108px !important;
            right: 8px !important;
        }
        #batchMapPicker .ol-zoom {
            display: inline-flex !important;
            flex-direction: column;
            gap: 6px;
            top: auto !important;
            left: auto !important;
            bottom: 56px !important;
            right: 8px !important;
            width: auto !important;
            max-width: 44px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #batchMapPicker .ol-rotate {
            top: auto !important;
            left: auto !important;
            bottom: 108px !important;
            right: 8px !important;
        }
        /* Attribution placement bottom-right */
        #mapPicker .ol-attribution {
            left: auto !important;
            right: 8px !important;
            bottom: 8px !important;
            top: auto !important;
        }
        #mapPicker .ol-attribution.ol-uncollapsible {
            left: auto !important;
            right: 8px !important;
            bottom: 8px !important;
            top: auto !important;
            width: auto !important;
            padding: 2px 6px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb; /* gray-200 */
        }
        #batchMapPicker .ol-attribution {
            left: auto !important;
            right: 8px !important;
            bottom: 8px !important;
            top: auto !important;
        }
        #batchMapPicker .ol-attribution.ol-uncollapsible {
            left: auto !important;
            right: 8px !important;
            bottom: 8px !important;
            top: auto !important;
            width: auto !important;
            padding: 2px 6px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
        }
        /* Collapsed state: show only small button, no bar */
        #mapPicker .ol-attribution.ol-collapsed {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            width: auto !important;
            padding: 0 !important;
        }
        #batchMapPicker .ol-attribution.ol-collapsed {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            width: auto !important;
            padding: 0 !important;
        }
        #mapPicker .ol-attribution ul {
            font-size: 11px;
            color: #374151; /* gray-700 */
            margin: 2px 6px;
            white-space: nowrap;
        }
        #batchMapPicker .ol-attribution ul {
            font-size: 11px;
            color: #374151;
            margin: 2px 6px;
            white-space: nowrap;
        }
        /* When collapsible, hide the long list by default */
        #mapPicker .ol-attribution:not(.ol-uncollapsible).ol-collapsed ul {
            display: none !important;
        }
        #batchMapPicker .ol-attribution:not(.ol-uncollapsible).ol-collapsed ul {
            display: none !important;
        }
        /* Do not block map dragging when attribution is visible */
        #mapPicker .ol-attribution { pointer-events: none; }
        #mapPicker .ol-attribution button { pointer-events: auto; }
        #mapPicker .ol-attribution ul { pointer-events: none; }

        /* Apply same controls fix for batch map */
        #batchMapPicker .ol-attribution { pointer-events: none; }
        #batchMapPicker .ol-attribution button { pointer-events: auto; }
        #batchMapPicker .ol-attribution ul { pointer-events: none; }

        /* Completely hide hidden rotate control to avoid wide invisible box */
        #mapPicker .ol-rotate.ol-hidden {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
        }
        #batchMapPicker .ol-rotate.ol-hidden {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
        }
    </style>

    <!-- Create Device Modal -->
    <div id="createDeviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.create_device_title') }}</h3>
                    <button id="closeCreateDeviceModal" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.device_name') }}</label>
                        <input type="text" id="newDeviceName" placeholder="{{ __('app.leave_blank_to_default') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.hardware_profile') }}</label>
                            <select id="newDeviceHw" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none">
                                <option value="">{{ __('app.random') }}</option>
                                @foreach($hardwareProfiles as $hp)
                                    <option value="{{ $hp->id }}">{{ $hp->title }} ({{ $hp->dimension }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.android_version') }}</label>
                            <select id="newDeviceOs" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none">
                                <option value="">{{ __('app.random') }}</option>
                                @foreach($osImages as $os)
                                    <option value="{{ $os->id }}">{{ $os->android }} (SDK {{ $os->skdVersion }}, {{ $os->arch }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.device_proxy') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="newDeviceProxy" placeholder="host:port" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.proxy_login') }}</label>
                            <input type="text" id="newDeviceProxyUser" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.proxy_pass') }}</label>
                            <input type="password" id="newDeviceProxyPass" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.gate_urls_managed') }}</label>
                        <select id="newDeviceGateUrl" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none">
                            @foreach($createDeviceGateUrls as $g)
                                <option value="{{ $g }}">{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.latitude') }} / {{ __('app.longitude') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input type="text" id="newDeviceLat" placeholder="{{ __('app.latitude') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                            <input type="text" id="newDeviceLng" placeholder="{{ __('app.longitude') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                            <button id="openMapPicker" type="button" class="mt-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm">{{ __('app.pick_on_map') }}</button>
                        </div>
                        <div id="mapPickerContainer" class="mt-3 hidden">
                            <div id="mapPicker" style="height: 300px;" class="rounded-md border"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button id="closeCreateDeviceModalFooter" class="px-4 py-2 rounded-md border">{{ __('app.cancel') }}</button>
                    <button id="submitCreateDevice" class="px-4 py-2 rounded-md bg-indigo-600 text-white">{{ __('app.create') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Create Devices Modal -->
    <div id="batchCreateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.batch_create_devices_title') }}</h3>
                    <button id="closeBatchCreateModal" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.devices_count') }}</label>
                            <input type="number" min="1" id="batchDeviceCount" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.gate_urls_managed') }}</label>
                            <select id="batchGateUrl" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none">
                                @foreach($createDeviceGateUrls as $g)
                                    <option value="{{ $g }}">{{ $g }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.hardware_profiles') }}</label>
                            <select id="batchHwProfiles" multiple data-placeholder="{{ __('app.hardware_profiles') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none min-h-[120px] tag-select">
                                @foreach($hardwareProfiles as $hp)
                                    <option value="{{ $hp->id }}">{{ $hp->title }} ({{ $hp->dimension }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.random') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.os_images') }}</label>
                            <select id="batchOsImages" multiple data-placeholder="{{ __('app.os_images') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none min-h-[120px] tag-select">
                                @foreach($osImages as $os)
                                    <option value="{{ $os->id }}">{{ $os->android }} (SDK {{ $os->skdVersion }}, {{ $os->arch }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.random') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.proxy_list') }}</label>
                        <textarea id="batchProxyList" rows="4" placeholder="ip:port or ip-range:port-range" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.proxy_each_newline_hint') }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.proxy_login') }}</label>
                            <input type="text" id="batchProxyLogin" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.proxy_pass') }}</label>
                            <input type="password" id="batchProxyPass" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.latitude') }} / {{ __('app.longitude') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <input type="text" id="batchLat" placeholder="{{ __('app.latitude') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                            <input type="text" id="batchLng" placeholder="{{ __('app.longitude') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                            <input type="number" step="0.000001" id="batchMaxDeviation" placeholder="{{ __('app.max_coords_deviation') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none" />
                            <button id="openBatchMapPicker" type="button" class="mt-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm">{{ __('app.pick_on_map') }}</button>
                        </div>
                        <div id="batchMapPickerContainer" class="mt-3 hidden">
                            <div id="batchMapPicker" style="height: 300px;" class="rounded-md border"></div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <button id="closeBatchCreateModalFooter" class="px-4 py-2 rounded-md border">{{ __('app.cancel') }}</button>
                    <button id="submitBatchCreate" class="px-4 py-2 rounded-md bg-indigo-600 text-white">{{ __('app.create') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                    <i class="las la-mobile-alt la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.total_devices') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_devices'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <i class="las la-check-circle la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.active_devices') }}</p>
                                <p id="active-devices-counter" class="text-2xl font-semibold text-gray-900">{{ $statistics['active_devices'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <i class="las la-layer-group la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.groups_count') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $statistics['groups_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->isAdmin())
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <i class="las la-users la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.users_count') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $statistics['users_count'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Admin Controls -->
            @if($user->isAdmin())
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.device_management') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Create Group -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">{{ __('app.create_group') }}</h4>
                            <form action="{{ route('devices.createGroup') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="group_name" class="block text-sm font-medium text-gray-700">{{ __('app.group_name') }}</label>
                                    <input type="text" name="name" id="group_name" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="group_description" class="block text-sm font-medium text-gray-700">{{ __('app.group_description') }}</label>
                                    <textarea name="description" id="group_description" rows="3"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                                <div>
                                    <label for="group_gate_url" class="block text-sm font-medium text-gray-700">{{ __('app.gate_url') }}</label>
                                    <select name="gate_url" id="group_gate_url"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('app.select_gate_url') }}</option>
                                        @foreach($gateUrls as $gateUrl)
                                            <option value="{{ $gateUrl }}">{{ $gateUrl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    {{ __('app.create_group') }}
                                </button>
                            </form>
                        </div>

                        <!-- Assign Device -->
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-4">{{ __('app.assign_device') }}</h4>
                            <form action="{{ route('devices.assign') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="assign_group_id" class="block text-sm font-medium text-gray-700">{{ __('app.device_group') }}</label>
                                    <select name="group_id" id="assign_group_id" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('app.select_group') }}</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" data-gate-url="{{ $group->gate_url }}">{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700">{{ __('app.user') }}</label>
                                    <select name="user_id" id="user_id" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('app.select_user') }}</option>
                                        @foreach(\App\Models\User::all() as $userOption)
                                            <option value="{{ $userOption->id }}">{{ $userOption->name }} ({{ $userOption->role }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="device_id" class="block text-sm font-medium text-gray-700">{{ __('app.device_name') }}</label>
                                    <select name="device_id" id="device_id" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">{{ __('app.select_group_first') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                    {{ __('app.assign_device') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Device Group Limits Management (Admin Only) -->
            @if($user->isAdmin())
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.device_limit') }} {{ __('app.management') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach(\App\Models\DeviceGroup::all() as $group)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="text-md font-medium text-gray-900">{{ $group->name }}</h4>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($group->hasReachedLimit()) bg-red-100 text-red-800
                                        @elseif($group->getRemainingSlots() <= 2) bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $group->getRunningDevicesCount() }}/{{ $group->device_limit }}
                                    </span>
                                </div>
                                <div class="space-y-2 mb-4">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">{{ __('app.running_devices') }}:</span> {{ $group->getRunningDevicesCount() }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">{{ __('app.remaining_slots') }}:</span> {{ $group->getRemainingSlots() }}
                                    </div>
                                    @if($group->hasReachedLimit())
                                        <div class="text-sm text-red-600 font-medium">
                                            {{ __('app.limit_warning') }}
                                        </div>
                                    @endif
                                </div>
                                <form class="group-limit-form" data-group-id="{{ $group->id }}">
                                    <div class="flex space-x-2">
                                        <input type="number" 
                                            name="device_limit" 
                                            value="{{ $group->device_limit }}"
                                            min="1" 
                                            max="100"
                                            class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <button type="submit" 
                                            class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition-colors">
                                            {{ __('app.update_limit') }}
                                        </button>
                                    </div>
                                </form>
                                <form class="group-created-limit-form mt-3" data-group-id="{{ $group->id }}">
                                    <div class="flex space-x-2">
                                        <input type="number"
                                            name="created_device_limit"
                                            value="{{ $group->created_device_limit ?? 0 }}"
                                            min="0"
                                            max="10000"
                                            class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="{{ __('app.created_device_limit') }}">
                                        <button type="submit"
                                            class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition-colors">
                                            {{ __('app.update_created_limit') }}
                                        </button>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        <span class="font-medium">{{ __('app.created_devices') }}:</span> {{ $group->getCreatedDevicesCount() }}
                                        <span class="ml-2 font-medium">{{ __('app.remaining_created_slots') }}:</span> {{ $group->getRemainingCreatedSlots() }}
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- User Assignment Management (Admin Only) -->
            @if($user->isAdmin())
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.user_group_assignments') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Quick User Assignment -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('app.assign_user_to_group') }}</h4>
                            <form id="quickAssignmentForm" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="quick_user_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.select_user') }}</label>
                                    <select id="quick_user_id" name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('app.select_user') }}</option>
                                        @foreach(\App\Models\User::where('role', '!=', 'admin')->get() as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="quick_group_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.select_group') }}</label>
                                    <select id="quick_group_id" name="group_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('app.select_group') }}</option>
                                        @foreach(\App\Models\DeviceGroup::all() as $group)
                                            <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->gate_url ?? 'No Gate URL' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="quick_role" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.select_role') }}</label>
                                    <select id="quick_role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('app.select_role') }}</option>
                                        <option value="member">{{ __('app.member') }}</option>
                                        <option value="manager">{{ __('app.manager') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    {{ __('app.assign_user') }}
                                </button>
                            </form>
                        </div>

                        <!-- Current Assignments Summary -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('app.current_assignments') }}</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach(\App\Models\UserGroupAssignment::with(['user', 'deviceGroup'])->where('is_active', true)->get() as $assignment)
                                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                        <div class="text-sm">
                                            <span class="font-medium">{{ $assignment->user->name }}</span>
                                            <span class="text-gray-500">→ {{ $assignment->deviceGroup->name }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $assignment->role === 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($assignment->role) }}
                                            </span>
                                            <button onclick="removeQuickAssignment({{ $assignment->user_id }}, {{ $assignment->device_group_id }})" 
                                                    class="text-red-600 hover:text-red-900 text-xs">
                                                {{ __('app.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('user-assignments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    {{ __('app.view_all_assignments') }} →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Devices List -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                @if($user->isAdmin())
                                    {{ __('app.all_devices') }}
                                @elseif($user->isManager())
                                    {{ __('app.device_groups') }}
                                @else
                                    {{ __('app.devices') }}
                                @endif
                            </h3>
                            <div class="flex items-center gap-2">
                                <button id="openCreateDeviceModal" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                    {{ __('app.create_device') }}
                                </button>
                                <button id="openBatchCreateModal" type="button" class="inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md shadow-sm text-indigo-700 bg-white hover:bg-gray-50 focus:outline-none">
                                    {{ __('app.batch_create_devices') }}
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-col lg:flex-row lg:flex-wrap lg:items-center space-y-3 lg:space-y-0 lg:gap-x-4 lg:gap-y-2 filter-container">
                                <!-- View Toggle -->
                                <div class="flex flex-col items-start gap-2 lg:flex-row lg:items-center lg:space-x-2">
                                    <span class="text-sm text-gray-600 block lg:inline mb-1 lg:mb-0">{{ __('app.view') }}:</span>
                                    <div class="flex bg-gray-100 rounded-lg p-1">
                                        <button id="cardViewBtn" class="px-3 py-1 text-sm rounded-md bg-white text-gray-900 shadow-sm">
                                            <i class="las la-th-large la-lg inline mr-1"></i>
                                            {{ __('app.cards') }}
                                        </button>
                                        <button id="tableViewBtn" class="px-3 py-1 text-sm rounded-md text-gray-600 hover:text-gray-900">
                                            <i class="las la-table la-lg inline mr-1"></i>
                                            {{ __('app.table') }}
                                        </button>
                                    </div>
                                </div>
                                 <!-- Group Filter -->
                                @if($userGroups->count() > 0)
                                <div class="flex items-center lg:space-x-2 group-user-filter lg:flex-[1_1_100%]">
                                    <label for="groupFilter" class="text-sm text-gray-700 whitespace-nowrap">{{ __('app.filter_by_group') }}:</label>
                                    <form method="GET" action="{{ route('devices.index') }}" class="flex items-center lg:space-x-2 group-user-form lg:flex-wrap lg:gap-x-4 lg:gap-y-2">
                                        @if(request('online'))
                                            <input type="hidden" name="online" value="{{ request('online') }}">
                                        @endif
                                        @if(request('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                        @if(request('only_online'))
                                            <input type="hidden" name="only_online" value="{{ request('only_online') }}">
                                        @endif
                                        <input type="hidden" name="search" id="searchHidden" value="">
                                        <select name="group_id" id="groupFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 min-w-[240px]" onchange="updateSearchHidden(); this.form.elements['user_id'].value=''; this.form.submit();">
                                            <option value="">{{ __('app.all_groups') }}</option>
                                            @foreach($userGroups as $group)
                                                @php
                                                    $usersForGroup = \App\Models\UserGroupAssignment::with('user')
                                                        ->where('device_group_id', $group->id)
                                                        ->where('is_active', true)
                                                        ->get()
                                                        ->map(function($a){ return ['id' => $a->user->id, 'name' => $a->user->name]; })
                                                        ->values();
                                                @endphp
                                                <option value="{{ $group->id }}" data-users='@json($usersForGroup)' {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                                    {{ $group->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <label for="userFilter" class="text-sm text-gray-700 whitespace-nowrap lg:ml-4">{{ __('app.filter_by_user') }}</label>
                                        <select name="user_id" id="userFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 min-w-[160px] disabled:opacity-50" onchange="updateSearchHidden(); this.form.submit();" disabled>
                                            <option value="">{{ __('app.all_users') }}</option>
                                        </select>
                                    </form>
                                </div>
                                @endif
                                
                                <!-- Online-only filter -->
                                <label for="onlyOnlineToggle" class="inline-flex items-center space-x-2 cursor-pointer select-none">
                                    <input type="checkbox" id="onlyOnlineToggle" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        {{ ($onlineFilter ?? false) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ __('app.show_only_online') }}</span>
                                </label>
                                
                                <!-- Search -->
                                <input type="text" id="search" placeholder="{{ __('app.search_devices_placeholder') }}" value="{{ request('search') }}"
                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 w-full lg:w-auto search-input">
                        </div>
                        </div>
                <div class="p-6">
                    <div class="text-sm text-gray-700 mb-4">
                        {{ __('app.total_devices') }}: 
                        <span id="total-devices-inline">{{ $statistics['total_devices'] }}</span>
                        @if(isset($selectedGroupId) && $selectedGroupId && isset($statistics['device_limit']))
                            <span class="ml-2 text-gray-500">
                                ({{ __('app.limit') }}: {{ $statistics['running_devices'] ?? 0 }}/{{ $statistics['device_limit'] ?? 0 }}; 
                                {{ __('app.created') }}: {{ $statistics['created_devices'] ?? 0 }}/{{ $statistics['created_device_limit'] ?? 0 }})
                            </span>
                        @endif
                    </div>
                    @if($devices->count() > 0)
                        @php $initialDevices = $devices->slice(0, 20); @endphp
                         <div class="grid grid-cols-1 xl:grid-cols-2 gap-8" id="deviceGrid">
                            @foreach($initialDevices as $device)
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow device-card" 
                                     data-device-id="{{ $device->id }}" 
                                     data-device-status="{{ $device->deviceStatus }}"
                                     data-screen-hash="{{ $device->screenViewHash ?? '' }}"
                                     data-device-name="{{ strtolower($device->display_name ?? $device->deviceName ?? '') }}"
                                     data-original-name="{{ $device->deviceName ?? 'Unknown Device' }}"
                                     data-device-platform="{{ strtolower($device->devicePlatform ?? '') }}"
                                     data-device-os="{{ strtolower($device->deviceOs ?? '') }}"
                                     data-device-status-text="{{ strtolower($device->deviceStatus ?? '') }}"
                                     data-device-group="{{ strtolower($device->group->name ?? '') }}"
                                     data-device-port="{{ $device->port_number ?? '' }}">
                                    <div class="flex space-x-6 device-flex">
                                        <!-- Screenshot Section - Left Side -->
                                        <div class="flex-shrink-0">
                                            <div class="relative w-40 h-60 bg-gray-200 rounded-lg overflow-hidden screenshot-container">
                                                @if($device->deviceStatus !== 'online')
                                                    <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                                                        <div class="text-center">
                                                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                            </svg>
                                                            <p class="mt-1 text-xs text-gray-500">{{ __('app.device_offline') }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Device Info Section - Right Side -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Port Number Badge -->
                                            @if($device->port_number)
                                            <div class="mb-3">
                                                @if($device->deviceStatus === 'online')
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg port-badge">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" stroke="none" viewBox="0 0 494.45 494.45" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M395.225,277.325c-6.8,0-13.5-2.6-18.7-7.8c-71.4-71.3-187.4-71.3-258.8,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c92-92,241.6-92,333.6,0c10.3,10.3,10.3,27.1,0,37.4C408.725,274.725,401.925,277.325,395.225,277.325z"></path>
                                                        <path d="M323.625,348.825c-6.8,0-13.5-2.6-18.7-7.8c-15.4-15.4-36-23.9-57.8-23.9s-42.4,8.5-57.8,23.9 c-10.3,10.3-27.1,10.3-37.4,0c-10.3-10.3-10.3-27.1,0-37.4c25.4-25.4,59.2-39.4,95.2-39.4s69.8,14,95.2,39.5 c10.3,10.3,10.3,27.1,0,37.4C337.225,346.225,330.425,348.825,323.625,348.825z"></path>
                                                        <circle cx="247.125" cy="398.925" r="35.3"></circle>
                                                        <path d="M467.925,204.625c-6.8,0-13.5-2.6-18.7-7.8c-111.5-111.4-292.7-111.4-404.1,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c64-64,149-99.2,239.5-99.2s175.5,35.2,239.5,99.2c10.3,10.3,10.3,27.1,0,37.4 C481.425,202.025,474.625,204.625,467.925,204.625z"></path>
                                                    </svg>
                                                    <span class="port-number">{{ __('app.port') }}: {{ $device->port_number }}</span>
                                                </div>
                                                @else
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-400 text-gray-600 shadow port-badge">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" stroke="none" viewBox="0 0 494.45 494.45" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M395.225,277.325c-6.8,0-13.5-2.6-18.7-7.8c-71.4-71.3-187.4-71.3-258.8,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c92-92,241.6-92,333.6,0c10.3,10.3,10.3,27.1,0,37.4C408.725,274.725,401.925,277.325,395.225,277.325z"></path>
                                                        <path d="M323.625,348.825c-6.8,0-13.5-2.6-18.7-7.8c-15.4-15.4-36-23.9-57.8-23.9s-42.4,8.5-57.8,23.9 c-10.3,10.3-27.1,10.3-37.4,0c-10.3-10.3-10.3-27.1,0-37.4c25.4-25.4,59.2-39.4,95.2-39.4s69.8,14,95.2,39.5 c10.3,10.3,10.3,27.1,0,37.4C337.225,346.225,330.425,348.825,323.625,348.825z"></path>
                                                        <circle cx="247.125" cy="398.925" r="35.3"></circle>
                                                        <path d="M467.925,204.625c-6.8,0-13.5-2.6-18.7-7.8c-111.5-111.4-292.7-111.4-404.1,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c64-64,149-99.2,239.5-99.2s175.5,35.2,239.5,99.2c10.3,10.3,10.3,27.1,0,37.4 C481.425,202.025,474.625,204.625,467.925,204.625z"></path>
                                                    </svg>
                                                    <span class="port-number">{{ __('app.port') }}: -</span>
                                                </div>
                                                @endif
                                            </div>
                                            @endif
                                            
                                            <!-- Device Name and Role -->
                                            <div class="mb-4">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="device-name-container" data-device-id="{{ $device->id }}" data-original-name="{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}">
                                                            <h4 class="device-name-display text-lg font-medium text-gray-900 break-words cursor-pointer hover:text-blue-600 transition-colors" 
                                                                onclick="startEditDeviceName({{ $device->id }}, '{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}')">
                                                                {{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}
                                                                @if($device->has_custom_name ?? false)
                                                                    <svg class="w-4 h-4 inline ml-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                    </svg>
                                                                @endif
                                                            </h4>
                                                            <div class="device-name-edit hidden">
                                                                <input type="text" 
                                                                    class="device-name-input w-full px-2 py-1 text-lg font-medium border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                                    value="{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}"
                                                                    maxlength="255">
                                                                <div class="flex space-x-1 mt-1">
                                                                    <button onclick="saveDeviceName({{ $device->id }})" 
                                                                        class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs hover:bg-blue-700 transition-colors">
                                                                        {{ __('app.save_name') }}
                                                                    </button>
                                                                    <button onclick="cancelEditDeviceName({{ $device->id }})" 
                                                                        class="bg-gray-600 text-white px-2 py-0.5 rounded text-xs hover:bg-gray-700 transition-colors">
                                                                        {{ __('app.cancel_edit') }}
                                                                    </button>
                                                                    @if($device->has_custom_name ?? false)
                                                                        <button onclick="deleteCustomDeviceName({{ $device->id }})" 
                                                                            class="bg-red-600 text-white px-2 py-0.5 rounded text-xs hover:bg-red-700 transition-colors">
                                                                            {{ __('app.delete_custom_name') }}
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="text-sm text-gray-500 break-words">{{ $device->devicePlatform ?? 'Unknown Platform' }} - {{ $device->deviceOs ?? 'Unknown OS' }}</p>
                                                    </div>
                                                    <div class="flex-shrink-0 ml-3">
                                                        @if(isset($device->access_level))
                                                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                                                @if($device->access_level === 'owner') bg-red-100 text-red-800
                                                                @elseif($device->access_level === 'manager') bg-yellow-100 text-yellow-800
                                                                @else bg-green-100 text-green-800
                                                                @endif">
                                                                {{ __('app.' . $device->access_level) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <!-- Group Information -->
                                            @if(isset($device->group) && $device->group)
                                                <div class="mb-3">
                                                    <p class="text-sm text-gray-600">
                                                        <span class="font-medium">{{ __('app.device_group') }}:</span> 
                                                        <span class="break-words">{{ $device->group->name }}</span>
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            <!-- Device Status -->
                                            <div class="mb-3">
                                                <div class="text-xs text-gray-500">
                                                    {{ __('app.device_status') }}: 
                                                    @php
                                                        $statusValue = isset($device->deviceStatus) ? trim((string)$device->deviceStatus) : '';
                                                        $statusKey = (strpos($statusValue, 'app.') === 0) ? substr($statusValue, 4) : $statusValue;
                                                        $isBlankStatus = $statusKey === '' || $statusKey === null;
                                                    @endphp
                                                    <span class="device-status font-medium {{ $isBlankStatus ? 'text-gray-400' : (($statusKey === 'running' || $statusKey === 'online') ? 'text-green-600' : ($statusKey === 'starting' ? 'text-blue-600' : 'text-yellow-600')) }}">
                                                        @if($isBlankStatus)
                                                            -
                                                        @else
                                                            @php
                                                                // Normalize raw backend phrases to canonical keys
                                                                $lowerStatus = mb_strtolower($statusKey);
                                                                if ($lowerStatus === 'starting device...' || $lowerStatus === 'device starting...' || $lowerStatus === 'starting...') {
                                                                    $statusKey = 'starting';
                                                                } elseif ($lowerStatus === 'creating device...' || $lowerStatus === 'device creating...' || $lowerStatus === 'creating...') {
                                                                    $statusKey = 'creating';
                                                                } elseif ($lowerStatus === 'stopping device...' || $lowerStatus === 'device stopping...' || $lowerStatus === 'stopping...') {
                                                                    $statusKey = 'stopping';
                                                                }
                                                                $translatedStatus = __('app.' . $statusKey);
                                                            @endphp
                                                            {{ $translatedStatus }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                           <!-- Create Date -->
                                           <div class="mb-3">
                                               <div class="text-xs text-gray-500">
                                                   {{ __('app.create_date') }}:
                                                   <span class="font-medium">
                                                       @if(!empty($device->createDate))
                                                           {{ \Carbon\Carbon::createFromTimestamp($device->createDate)->format('Y-m-d H:i') }}
                                                       @else
                                                           -
                                                       @endif
                                                   </span>
                                               </div>
                                           </div>

                                            <!-- Group Limit Info (if device is in a group) -->
                                            @if(isset($device->group) && $device->group)
                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500">
                                                        {{ __('app.device_limit') }}: 
                                                        <span class="ml-1 text-xs 
                                                            @if($device->group->hasReachedLimit()) text-red-600 font-medium
                                                            @elseif($device->group->getRemainingSlots() <= 2) text-yellow-600 font-medium
                                                            @else text-green-600
                                                            @endif">
                                                            {{ $device->group->getRunningDevicesCount() }}/{{ $device->group->device_limit }}
                                                            @if($device->group->hasReachedLimit())
                                                                <span class="ml-1">({{ __('app.limit_reached') }})</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Automation Controls -->
                                            <div class="mb-3">
                                                <div class="automation-controls flex flex-wrap gap-1">
                                                    @if($device->deviceStatus === 'stopped')
                                                        <button onclick="startDevice({{ $device->id }})" 
                                                            class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                                                            {{ __('app.start_device') }}
                                                        </button>
                                                    @elseif($device->deviceStatus === 'online')
                                                        <button onclick="stopDevice({{ $device->id }})" 
                                                            class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700 transition-colors">
                                                            {{ __('app.stop_device') }}
                                                        </button>
                                                        <button onclick="refreshScreenshot({{ $device->id }})" 
                                                            class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 transition-colors">
                                                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                            </svg>
                                                        </button>
                                                    @elseif($device->deviceStatus === 'starting')
                                                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                                            {{ __('app.starting') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Action Links -->
                                            <div class="action-links flex flex-wrap gap-2">

                                                @if($user->isAdmin())
                                                    <form action="{{ route('devices.unassign', [$device->id, $device->assignment->user_id]) }}" 
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 text-xs font-medium"
                                                            onclick="return confirm('{{ __('app.unassign_device') }}?')">
                                                            {{ __('app.unassign_device') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($user->isAdmin() || $user->isManager())
                                                    <button onclick="cancelDeviceAssignment({{ $device->id }})" 
                                                            class="text-orange-600 hover:text-orange-900 text-xs font-medium">
                                                        {{ __('app.cancel_assignment') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Table View -->
                        <div id="deviceTable" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.port') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.device_name') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.device_group') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.platform') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.os') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.status') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.create_date') }}
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('app.actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="deviceTableBody">
                                        @foreach($initialDevices as $device)
                                        <tr class="hover:bg-gray-50 device-table-row" 
                                            data-device-id="{{ $device->id }}" 
                                            data-device-status="{{ $device->deviceStatus }}"
                                            data-screen-hash="{{ $device->screenViewHash ?? '' }}"
                                            data-device-name="{{ strtolower($device->display_name ?? $device->deviceName ?? '') }}"
                                             data-original-name="{{ $device->deviceName ?? 'Unknown Device' }}"
                                            data-device-platform="{{ strtolower($device->devicePlatform ?? '') }}"
                                            data-device-os="{{ strtolower($device->deviceOs ?? '') }}"
                                            data-device-status-text="{{ strtolower($device->deviceStatus ?? '') }}"
                                            data-device-group="{{ strtolower($device->group->name ?? '') }}"
                                            data-device-port="{{ $device->port_number ?? '' }}">
                                            
                                            <!-- Port Number -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($device->port_number)
                                                    @if($device->deviceStatus === 'online')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-blue-500 to-purple-600 text-white port-badge">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" stroke="none" viewBox="0 0 494.45 494.45">
                                                            <path d="M395.225,277.325c-6.8,0-13.5-2.6-18.7-7.8c-71.4-71.3-187.4-71.3-258.8,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c92-92,241.6-92,333.6,0c10.3,10.3,10.3,27.1,0,37.4C408.725,274.725,401.925,277.325,395.225,277.325z"></path>
                                                            <path d="M323.625,348.825c-6.8,0-13.5-2.6-18.7-7.8c-15.4-15.4-36-23.9-57.8-23.9s-42.4,8.5-57.8,23.9 c-10.3,10.3-27.1,10.3-37.4,0c-10.3-10.3-10.3-27.1,0-37.4c25.4-25.4,59.2-39.4,95.2-39.4s69.8,14,95.2,39.5 c10.3,10.3,10.3,27.1,0,37.4C337.225,346.225,330.425,348.825,323.625,348.825z"></path>
                                                            <circle cx="247.125" cy="398.925" r="35.3"></circle>
                                                            <path d="M467.925,204.625c-6.8,0-13.5-2.6-18.7-7.8c-111.5-111.4-292.7-111.4-404.1,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c64-64,149-99.2,239.5-99.2s175.5,35.2,239.5,99.2c10.3,10.3,10.3,27.1,0,37.4 C481.425,202.025,474.625,204.625,467.925,204.625z"></path>
                                                        </svg>
                                                        <span class="port-number">{{ $device->port_number }}</span>
                                                    </span>
                                                    @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-400 text-gray-600 port-badge">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" stroke="none" viewBox="0 0 494.45 494.45">
                                                            <path d="M395.225,277.325c-6.8,0-13.5-2.6-18.7-7.8c-71.4-71.3-187.4-71.3-258.8,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c92-92,241.6-92,333.6,0c10.3,10.3,10.3,27.1,0,37.4C408.725,274.725,401.925,277.325,395.225,277.325z"></path>
                                                                <path d="M323.625,348.825c-6.8,0-13.5-2.6-18.7-7.8c-15.4-15.4-36-23.9-57.8-23.9s-42.4,8.5-57.8,23.9 c-10.3,10.3-27.1,10.3-37.4,0c-10.3-10.3-10.3-27.1,0-37.4c25.4-25.4,59.2-39.4,95.2-39.4s69.8,14,95.2,39.5 c10.3,10.3,10.3,27.1,0,37.4C337.225,346.225,330.425,348.825,323.625,348.825z"></path>
                                                                <circle cx="247.125" cy="398.925" r="35.3"></circle>
                                                                <path d="M467.925,204.625c-6.8,0-13.5-2.6-18.7-7.8c-111.5-111.4-292.7-111.4-404.1,0c-10.3,10.3-27.1,10.3-37.4,0 s-10.3-27.1,0-37.4c64-64,149-99.2,239.5-99.2s175.5,35.2,239.5,99.2c10.3,10.3,10.3,27.1,0,37.4 C481.425,202.025,474.625,204.625,467.925,204.625z"></path>
                                                            </svg>
                                                            <span class="port-number">-</span>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Device Name -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="device-name-container-table" data-device-id="{{ $device->id }}" data-original-name="{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}">
                                                        <div class="device-name-display-table text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600" 
                                                             onclick="startEditDeviceNameTable({{ $device->id }}, '{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}')">
                                                            {{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}
                                                            @if($device->has_custom_name ?? false)
                                                                <svg class="w-3 h-3 inline ml-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div class="device-name-edit-table hidden">
                                                            <div class="flex items-center space-x-1">
                                                                <input type="text" class="device-name-input-table text-sm px-2 py-1 border border-gray-300 rounded focus:outline-none focus:border-blue-500" 
                                                                       value="{{ $device->display_name ?? $device->deviceName ?? 'Unknown Device' }}">
                                                                <button class="save-name-table text-green-600 hover:text-green-800 p-1" 
                                                                        onclick="saveDeviceNameTable({{ $device->id }})">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                </button>
                                                                <button class="cancel-name-table text-gray-600 hover:text-gray-800 p-1" 
                                                                        onclick="cancelEditDeviceNameTable({{ $device->id }})">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Group -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $device->group->name ?? 'N/A' }}
                                            </td>
                                            
                                            <!-- Platform -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $device->devicePlatform ?? 'N/A' }}
                                            </td>
                                            
                                            <!-- OS -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $device->deviceOs ?? 'N/A' }}
                                            </td>
                                            
                                            <!-- Status -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusValue = isset($device->deviceStatus) ? trim((string)$device->deviceStatus) : '';
                                                    $statusKey = (strpos($statusValue, 'app.') === 0) ? substr($statusValue, 4) : $statusValue;
                                                    $isBlankStatus = $statusKey === '' || $statusKey === null;
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium device-status
                                                    @if($isBlankStatus) bg-gray-100 text-gray-500
                                                    @elseif($statusKey === 'online') bg-green-100 text-green-800
                                                    @elseif($statusKey === 'starting') bg-blue-100 text-blue-800
                                                    @elseif($statusKey === 'failed') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800 @endif">
                                                    @if($isBlankStatus)
                                                        -
                                                    @else
                                                        @php
                                                            $translatedStatus = (strpos($statusKey, '.') !== false)
                                                                ? __($statusKey)
                                                                : __('app.' . $statusKey);
                                                        @endphp
                                                        {{ $translatedStatus }}
                                                    @endif
                                                </span>
                                            </td>
                                            <!-- Create Date -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if(!empty($device->createDate))
                                                    {{ \Carbon\Carbon::createFromTimestamp($device->createDate)->format('Y-m-d H:i') }}
                                               @else
                                                   -
                                               @endif
                                           </td>
                                            
                                            <!-- Actions -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2">
                                                    @if($device->deviceStatus === 'stopped')
                                                        <button onclick="startDevice({{ $device->id }})" 
                                                                class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-2 rounded">
                                                            {{ __('app.start') }}
                                                        </button>
                                                    @elseif($device->deviceStatus === 'online')
                                                        <button onclick="stopDevice({{ $device->id }})" 
                                                                class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded">
                                                            {{ __('app.stop') }}
                                                        </button>
                                                    @else
                                                        <button disabled 
                                                                class="bg-gray-400 text-white text-xs font-bold py-1 px-2 rounded cursor-not-allowed">
                                                            {{ __('app.starting') }}
                                                        </button>
                                                    @endif
                                                    
                                                    <button onclick="refreshScreenshot({{ $device->id }})" 
                                                            class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    @if($user->isAdmin() || $user->isManager())
                                                        <button onclick="cancelDeviceAssignment({{ $device->id }})" 
                                                                class="bg-orange-500 hover:bg-orange-700 text-white text-xs font-bold py-1 px-2 rounded" 
                                                                title="{{ __('app.cancel_assignment') }}">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div id="infiniteLoader" class="mt-6 text-center text-sm text-gray-500 hidden">

                            <svg width="100" height="100" viewBox="0 0 100 100" class="mx-auto h-25 w-25 animate-spin text-gray-400 inline">
                                <circle fill="none" stroke-opacity="1" stroke="#5E0EFF" stroke-width="1.25" cx="50" cy="50" r="0">
                                    <animate attributeName="r" calcMode="spline" dur="2" values="2.5;40" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate>
                                    <animate attributeName="stroke-width" calcMode="spline" dur="2" values="0;7.5" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate>
                                    <animate attributeName="stroke-opacity" calcMode="spline" dur="2" values="1;0" keyTimes="0;1" keySplines="0 .2 .5 1" repeatCount="indefinite"></animate>
                                </circle>
                            </svg>

                        </div>
                        
                        <!-- No Results Message -->
                        <div id="noResults" class="hidden text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_devices_found') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('app.try_different_search') }}</p>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('app.no_devices_assigned') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if($user->isAdmin())
                                    {{ __('app.no_devices_assigned') }}
                                @else
                                    {{ __('app.no_devices_assigned') }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ol@v9.2.4/dist/ol.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Create Device modal toggle
        const modal = document.getElementById('createDeviceModal');
        const openBtn = document.getElementById('openCreateDeviceModal');
        const closeBtn = document.getElementById('closeCreateDeviceModal');
        const closeBtnFooter = document.getElementById('closeCreateDeviceModalFooter');
        const mapBtn = document.getElementById('openMapPicker');
        const mapContainer = document.getElementById('mapPickerContainer');
        let mapInstance = null;
        let mapMarker = null;

        function openCreateModal() {
            if (modal) {
                modal.classList.remove('hidden');
            }
        }
        function closeCreateModal() {
            if (modal) {
                modal.classList.add('hidden');
            }
        }
        if (openBtn) openBtn.addEventListener('click', openCreateModal);
        if (closeBtn) closeBtn.addEventListener('click', closeCreateModal);
        if (closeBtnFooter) closeBtnFooter.addEventListener('click', closeCreateModal);

        // Map picker (OpenLayers)
        function initMapPicker() {
            if (mapInstance) return;
            if (!window.ol) return;
            const view = new ol.View({ center: ol.proj.fromLonLat([0, 0]), zoom: 2 });
            const raster = new ol.layer.Tile({ source: new ol.source.OSM() });
            const vectorSource = new ol.source.Vector();
            const vectorLayer = new ol.layer.Vector({ source: vectorSource });

            const controls = ol.control.defaults.defaults({ attribution: false });
            mapInstance = new ol.Map({ target: 'mapPicker', layers: [raster, vectorLayer], view, controls });
            mapInstance.addControl(new ol.control.Attribution({ collapsible: true, collapsed: true }));
            mapInstance.on('singleclick', function(evt) {
                const lonlat = ol.proj.toLonLat(evt.coordinate);
                const lng = lonlat[0].toFixed(6);
                const lat = lonlat[1].toFixed(6);
                document.getElementById('newDeviceLat').value = lat;
                document.getElementById('newDeviceLng').value = lng;
                vectorSource.clear();
                vectorSource.addFeature(new ol.Feature({ geometry: new ol.geom.Point(evt.coordinate) }));
            });
            // Try to center to user's location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lonLat = [pos.coords.longitude, pos.coords.latitude];
                    view.setCenter(ol.proj.fromLonLat(lonLat));
                    view.setZoom(12);
                });
            }
        }
        if (mapBtn) {
            mapBtn.addEventListener('click', function() {
                if (mapContainer) {
                    const isHidden = mapContainer.classList.contains('hidden');
                    if (isHidden) {
                        mapContainer.classList.remove('hidden');
                        setTimeout(() => { initMapPicker(); if (mapInstance) mapInstance.updateSize(); }, 50);
                    } else {
                        mapContainer.classList.add('hidden');
                    }
                }
            });
        }

        // Submit create device (placeholder - depends on backend API to actually create devices in organic DB)
        const submitBtn = document.getElementById('submitCreateDevice');
        if (submitBtn) {
            submitBtn.addEventListener('click', async function() {
                const proxy = document.getElementById('newDeviceProxy').value.trim();
                if (!proxy) {
                    showNotification('{{ __('validation.required', ['attribute' => __('validation.attributes.proxy')]) }}', 'error');
                    return;
                }
                const payload = {
                    name: document.getElementById('newDeviceName').value.trim() || null,
                    hardware_profile_id: document.getElementById('newDeviceHw').value || null,
                    os_image_id: document.getElementById('newDeviceOs').value || null,
                    proxy: proxy,
                    proxy_login: document.getElementById('newDeviceProxyUser').value || null,
                    proxy_pass: document.getElementById('newDeviceProxyPass').value || null,
                    gate_url: document.getElementById('newDeviceGateUrl')?.value || null,
                    latitude: document.getElementById('newDeviceLat').value || null,
                    longitude: document.getElementById('newDeviceLng').value || null,
                };

                try {
                    const resp = await fetch('{{ route('devices.create') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('{{ __('app.create') }}', 'success');
                        closeCreateModal();
                        setTimeout(() => { refreshDevices && refreshDevices(); }, 600);
                    } else {
                        showNotification(data.message || 'Error', 'error');
                    }
                } catch (e) {
                    showNotification('Request failed', 'error');
                }
            });
        }

        // Batch modal toggle and map
        const batchModal = document.getElementById('batchCreateModal');
        const openBatchBtn = document.getElementById('openBatchCreateModal');
        const closeBatchBtn = document.getElementById('closeBatchCreateModal');
        const closeBatchBtnFooter = document.getElementById('closeBatchCreateModalFooter');
        const batchMapBtn = document.getElementById('openBatchMapPicker');
        const batchMapContainer = document.getElementById('batchMapPickerContainer');
        let batchMapInstance = null;

        function openBatchModal() {
            if (batchModal) batchModal.classList.remove('hidden');
        }
        function closeBatchModal() {
            if (batchModal) batchModal.classList.add('hidden');
        }
        if (openBatchBtn) openBatchBtn.addEventListener('click', openBatchModal);
        if (closeBatchBtn) closeBatchBtn.addEventListener('click', closeBatchModal);
        if (closeBatchBtnFooter) closeBatchBtnFooter.addEventListener('click', closeBatchModal);

        function initBatchMapPicker() {
            if (batchMapInstance) return;
            if (!window.ol) return;
            const view = new ol.View({ center: ol.proj.fromLonLat([0, 0]), zoom: 2 });
            const raster = new ol.layer.Tile({ source: new ol.source.OSM() });
            const vectorSource = new ol.source.Vector();
            const vectorLayer = new ol.layer.Vector({ source: vectorSource });
            const controls = ol.control.defaults.defaults({ attribution: false });
            batchMapInstance = new ol.Map({ target: 'batchMapPicker', layers: [raster, vectorLayer], view, controls });
            batchMapInstance.addControl(new ol.control.Attribution({ collapsible: true, collapsed: true }));
            batchMapInstance.on('singleclick', function(evt) {
                const lonlat = ol.proj.toLonLat(evt.coordinate);
                const lng = lonlat[0].toFixed(6);
                const lat = lonlat[1].toFixed(6);
                document.getElementById('batchLat').value = lat;
                document.getElementById('batchLng').value = lng;
                vectorSource.clear();
                vectorSource.addFeature(new ol.Feature({ geometry: new ol.geom.Point(evt.coordinate) }));
            });
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lonLat = [pos.coords.longitude, pos.coords.latitude];
                    view.setCenter(ol.proj.fromLonLat(lonLat));
                    view.setZoom(12);
                });
            }
        }
        if (batchMapBtn) {
            batchMapBtn.addEventListener('click', function() {
                if (batchMapContainer) {
                    const isHidden = batchMapContainer.classList.contains('hidden');
                    if (isHidden) {
                        batchMapContainer.classList.remove('hidden');
                        setTimeout(() => { initBatchMapPicker(); if (batchMapInstance) batchMapInstance.updateSize(); }, 50);
                    } else {
                        batchMapContainer.classList.add('hidden');
                    }
                }
            });
        }

        // Submit batch create
        const submitBatchBtn = document.getElementById('submitBatchCreate');
        if (submitBatchBtn) {
            submitBatchBtn.addEventListener('click', async function() {
                const countVal = parseInt(document.getElementById('batchDeviceCount').value || '0', 10);
                if (!countVal || countVal < 1) {
                    showNotification('{{ __('validation.required', ['attribute' => __('app.devices_count')]) }}', 'error');
                    return;
                }
                const hwSel = Array.from(document.getElementById('batchHwProfiles').selectedOptions).map(o => o.value);
                const osSel = Array.from(document.getElementById('batchOsImages').selectedOptions).map(o => o.value);
                const payload = {
                    count: countVal,
                    hardware_profile_ids: hwSel,
                    os_image_ids: osSel,
                    proxy_list: document.getElementById('batchProxyList').value || '',
                    proxy_login: document.getElementById('batchProxyLogin').value || null,
                    proxy_pass: document.getElementById('batchProxyPass').value || null,
                    gate_url: document.getElementById('batchGateUrl')?.value || null,
                    latitude: document.getElementById('batchLat').value || null,
                    longitude: document.getElementById('batchLng').value || null,
                    max_deviation: document.getElementById('batchMaxDeviation').value || null,
                };

                try {
                    const resp = await fetch('{{ route('devices.batchCreate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('{{ __('app.devices_created_successfully') }}', 'success');
                        closeBatchModal();
                        setTimeout(() => { refreshDevices && refreshDevices(); }, 600);
                    } else {
                        showNotification(data.message || 'Error', 'error');
                    }
                } catch (e) {
                    showNotification('Request failed', 'error');
                }
            });
        }

        // Start device automation
        function startDevice(deviceId) {
            if (!confirm('{{ __('app.start_device') }}?')) {
                return;
            }

            fetch(`/devices/${deviceId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Refresh devices to update UI instead of full reload
                    setTimeout(() => { refreshDevices(); }, 500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.device_start_failed') }}', 'error');
            });
        }

        // Stop device automation
        function stopDevice(deviceId) {
            if (!confirm('{{ __('app.stop_device') }}?')) {
                return;
            }

            fetch(`/devices/${deviceId}/stop`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Refresh devices to update UI instead of full reload
                    setTimeout(() => { refreshDevices(); }, 500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.device_stop_failed') }}', 'error');
            });
        }

        // Refresh screenshot
        function refreshScreenshot(deviceId) {
            // Show loading state
            const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (deviceCard) {
                const screenshotContainer = deviceCard.querySelector('.screenshot-container');
                if (screenshotContainer) {
                    screenshotContainer.innerHTML = `
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">{{ __('app.screenshot_requested') }}</p>
                            </div>
                        </div>
                    `;
                }
                // Force next refresh to treat screenshot as changed
                deviceCard.setAttribute('data-screen-hash', '');
            }

            // Request screenshot
            fetch(`/devices/${deviceId}/request-screenshot`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    
                    // Wait 5 seconds then try to get the new screenshot
                    setTimeout(() => {
                        // Trigger a background refresh to get the new screenshot
                        refreshDevices();
                    }, 5000);
                } else {
                    showNotification(data.message, 'error');
                    // Restore original screenshot display
                    if (deviceCard) {
                        const screenshotContainer = deviceCard.querySelector('.screenshot-container');
                        if (screenshotContainer) {
                            screenshotContainer.innerHTML = `
                                <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('app.screenshot_unavailable') }}</p>
                                    </div>
                                </div>
                            `;
                        }
                    }
                }
            })
            .catch(error => {
                showNotification('{{ __('app.screenshot_request_failed') }}', 'error');
                // Restore original screenshot display
                if (deviceCard) {
                    const screenshotContainer = deviceCard.querySelector('.screenshot-container');
                    if (screenshotContainer) {
                        screenshotContainer.innerHTML = `
                            <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('app.screenshot_unavailable') }}</p>
                                </div>
                            </div>
                        `;
                    }
                }
            });
        }

        // Cancel device assignment
        function cancelDeviceAssignment(deviceId) {
            if (!confirm('{{ __('app.confirm_cancel_assignment') }}')) {
                return;
            }

            fetch(`/devices/${deviceId}/cancel-assignment`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Reload page to update device list
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.assignment_cancel_failed') }}', 'error');
            });
        }

        // Background refresh functionality
        let refreshInterval;
        let lastRefreshTime = 0;
        const REFRESH_INTERVAL = 5000; // 5 seconds

        // Initialize background refresh
        function initBackgroundRefresh() {
            // Start the refresh interval
            refreshInterval = setInterval(() => {
                refreshDevices();
            }, REFRESH_INTERVAL);
            
            // Also refresh immediately on page load
            setTimeout(() => {
                refreshDevices();
            }, 1000);
        }

        // Device group limit management
        document.querySelectorAll('.group-limit-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const groupId = this.getAttribute('data-group-id');
                const limitInput = this.querySelector('input[name="device_limit"]');
                const newLimit = limitInput.value;

                if (!newLimit || newLimit < 1 || newLimit > 100) {
                    showNotification('{{ __('app.invalid_limit_value') }}', 'error');
                    return;
                }

                fetch(`/devices/groups/${groupId}/limit`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ device_limit: parseInt(newLimit) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        // Reload page to update all limit displays
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('{{ __('app.limit_update_failed') }}', 'error');
                });
            });
        });

        // Created device limit management
        document.querySelectorAll('.group-created-limit-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const groupId = this.getAttribute('data-group-id');
                const limitInput = this.querySelector('input[name="created_device_limit"]');
                const newLimit = limitInput.value;

                if (newLimit === '' || newLimit < 0 || newLimit > 10000) {
                    alert('{{ __('app.invalid_limit_value') }}');
                    return;
                }

                fetch(`/devices/groups/${groupId}/created-limit`, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ created_device_limit: parseInt(newLimit) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('{{ __('app.group_created_limit_updated_successfully') }}');
                        // Optionally refresh counts
                        window.location.reload();
                    } else {
                        alert(data.message || '{{ __('app.limit_update_failed') }}');
                    }
                })
                .catch(() => {
                    alert('{{ __('app.limit_update_failed') }}');
                });
            });
        });

        // Dynamic device loading based on group selection (only for admin users)
        const assignGroupSelect = document.getElementById('assign_group_id');
        if (assignGroupSelect) {
            assignGroupSelect.addEventListener('change', function() {
            const groupSelect = this;
            const deviceSelect = document.getElementById('device_id');
            const selectedOption = groupSelect.options[groupSelect.selectedIndex];
            const gateUrl = selectedOption.getAttribute('data-gate-url');

            // Clear device select
            deviceSelect.innerHTML = '<option value="">{{ __('app.select_group_first') }}</option>';

            if (gateUrl) {
                // Show loading state
                deviceSelect.innerHTML = '<option value="">{{ __('app.loading_devices') }}</option>';

                // Fetch devices for this gate URL
                fetch(`/devices/by-gate-url/${encodeURIComponent(gateUrl)}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.devices) {
                        deviceSelect.innerHTML = '<option value="">{{ __('app.select_device') }}</option>';
                        data.devices.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.textContent = `${device.deviceName} (${device.devicePlatform} - ${device.deviceOs})`;
                            deviceSelect.appendChild(option);
                        });
                    } else {
                        deviceSelect.innerHTML = '<option value="">{{ __('app.no_devices_found') }}</option>';
                    }
                })
                .catch(error => {
                    deviceSelect.innerHTML = '<option value="">{{ __('app.error_loading_devices') }}</option>';
                });
            }
        });
        }
        

        // Refresh all devices data
        async function refreshDevices() {
            // Don't refresh if chunk loading is in progress
            if (typeof isLoadingChunk !== 'undefined' && isLoadingChunk) {
                return;
            }
            
            try {
                const response = await fetch('{{ route("devices.refresh.all") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    credentials: 'same-origin' // Include cookies
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                
                if (data.success && data.devices) {
                    updateDevicesFromData(data.devices);
                    
                    // Update statistics if available
                    if (data.statistics) {
                        updateStatistics(data.statistics);
                    }
                    
                    lastRefreshTime = data.timestamp;
                }
            } catch (error) {
                // Silent error handling
            }
        }

        // Update devices from refresh data
        function updateDevicesFromData(devices) {
            devices.forEach(deviceData => {
                // Update card view
                const deviceCard = document.querySelector(`div[data-device-id="${deviceData.id}"][data-status-ready="true"]`);
                if (deviceCard) {
                    // Update device status
                    const currentStatus = deviceCard.getAttribute('data-device-status');
                    if (currentStatus !== deviceData.deviceStatus) {
                        deviceCard.setAttribute('data-device-status', deviceData.deviceStatus);
                        updateDeviceStatus(deviceCard, deviceData);
                    }

                    // Update screenshot if changed
                    const currentHash = deviceCard.getAttribute('data-screen-hash');
                    if (currentHash !== deviceData.screenViewHash) {
                        deviceCard.setAttribute('data-screen-hash', deviceData.screenViewHash);
                        updateDeviceScreenshot(deviceCard, deviceData);
                    }
                }

                // Update table view
                const deviceRow = document.querySelector(`tr[data-device-id="${deviceData.id}"][data-status-ready="true"]`);
                if (deviceRow) {
                    // Update device status
                    const currentStatus = deviceRow.getAttribute('data-device-status');
                    if (currentStatus !== deviceData.deviceStatus) {
                        deviceRow.setAttribute('data-device-status', deviceData.deviceStatus);
                        updateTableDeviceStatus(deviceRow, deviceData);
                    }
                }
            });
        }

        // Update statistics counters
        function updateStatistics(statistics) {
            // Update active devices counter
            const activeDevicesCounter = document.getElementById('active-devices-counter');
            if (activeDevicesCounter && statistics.active_devices !== undefined) {
                activeDevicesCounter.textContent = statistics.active_devices;
            }
            const totalDevicesInline = document.getElementById('total-devices-inline');
            if (totalDevicesInline && statistics.total_devices !== undefined) {
                totalDevicesInline.textContent = statistics.total_devices;
            }
        }

        // Translate device status to current language
        function translateDeviceStatus(status) {
            const translations = {
                'online': '{{ __('app.online') }}',
                'offline': '{{ __('app.offline') }}',
                'starting': '{{ __('app.starting') }}',
                'stopped': '{{ __('app.stopped') }}',
                'running': '{{ __('app.running') }}',
                'failed': '{{ __('app.failed') }}',
                'creating': '{{ __('app.creating') }}',
                // Canonical keys for creation timeline
                'creating_device': '{{ __('app.creating_device') }}',
                'device_created': '{{ __('app.device_created') }}',
                'installing_gapps': '{{ __('app.installing_gapps') }}',
                'opengapps_zip_not_found': '{{ __('app.opengapps_zip_not_found') }}',
                'zip_file_push_failed': '{{ __('app.zip_file_push_failed') }}',
                'gapps_install_failed': '{{ __('app.gapps_install_failed') }}',
                'gapps_install_success': '{{ __('app.gapps_install_success') }}',
                'installing_chrome': '{{ __('app.installing_chrome') }}',
                'no_chrome_apps_available': '{{ __('app.no_chrome_apps_available') }}',
                'chrome_install_failed': '{{ __('app.chrome_install_failed') }}',
                'chrome_install_success': '{{ __('app.chrome_install_success') }}',
                // Raw phrases possibly coming from backend
                'Creating device...': '{{ __('app.creating_device') }}',
                'Device created': '{{ __('app.device_created') }}',
                'Installing gapps...': '{{ __('app.installing_gapps') }}',
                'OpenGapps zip not found': '{{ __('app.opengapps_zip_not_found') }}',
                'Zip file push failed': '{{ __('app.zip_file_push_failed') }}',
                'Gapps installation failed': '{{ __('app.gapps_install_failed') }}',
                'Gapps installation success': '{{ __('app.gapps_install_success') }}',
                'Installing chrome': '{{ __('app.installing_chrome') }}',
                'No chrome apps available': '{{ __('app.no_chrome_apps_available') }}',
                'Chrome install failed': '{{ __('app.chrome_install_failed') }}',
                'Chrome install success': '{{ __('app.chrome_install_success') }}',
                // Historic variant observed in data stream
                'app.Installing gapps...': '{{ __('app.installing_gapps') }}',
                'unknown': '{{ __('app.unknown') }}'
            };
            // Normalize incoming status
            let key = (status === undefined || status === null || status === '') ? 'creating' : String(status);
            if (key.startsWith('app.')) {
                key = key.slice(4);
            }
            // Try exact, then lowercase fallback for canonical states
            let translated = translations[key];
            if (!translated) {
                const lower = key.toLowerCase();
                if (['online','offline','starting','stopped','running','failed','creating','unknown'].includes(lower)) {
                    translated = translations[lower];
                }
            }
            translated = translated || key;
            return translated;
        }

        // Update device status and controls
        function updateDeviceStatus(deviceCard, deviceData) {
            // Update status display with retry mechanism
            let statusElement = deviceCard.querySelector('.device-status');
            
            if (statusElement) {
                const translatedStatus = translateDeviceStatus(deviceData.deviceStatus);
                statusElement.textContent = translatedStatus;
                statusElement.className = `device-status font-medium ${
                    deviceData.deviceStatus === 'running' || deviceData.deviceStatus === 'online' 
                        ? 'text-green-600' 
                        : (deviceData.deviceStatus === 'starting' ? 'text-blue-600' : (deviceData.deviceStatus === 'failed' ? 'text-red-600' : 'text-yellow-600'))
                }`;
            } else {
                const allElements = deviceCard.querySelectorAll('*');
                allElements.forEach(el => {
                    if (el.className && el.className.includes('status')) {
                        // Element found but no logging needed
                    }
                });
                
                // Try to find the status element by looking for the text pattern
                const statusTextPattern = /{{ __('app.device_status') }}:/;
                const textNodes = Array.from(deviceCard.querySelectorAll('*')).filter(el => 
                    el.textContent && statusTextPattern.test(el.textContent)
                );
                
                if (textNodes.length > 0) {
                    // Look for the span element that should contain the status
                    const potentialStatusSpan = textNodes[0].querySelector('span');
                    if (potentialStatusSpan) {
                        // Add the device-status class if it's missing
                        if (!potentialStatusSpan.classList.contains('device-status')) {
                            potentialStatusSpan.classList.add('device-status');
                        }
                        statusElement = potentialStatusSpan;
                        
                        // Now update the status
                        const translatedStatus = translateDeviceStatus(deviceData.deviceStatus);
                        statusElement.textContent = translatedStatus;
                        statusElement.className = `device-status font-medium ${
                            deviceData.deviceStatus === 'running' || deviceData.deviceStatus === 'online' 
                                ? 'text-green-600' 
                                : (deviceData.deviceStatus === 'starting' ? 'text-blue-600' : (deviceData.deviceStatus === 'failed' ? 'text-red-600' : 'text-yellow-600'))
                        }`;
                    }
                }
            }

            // Update control buttons
            const controlsContainer = deviceCard.querySelector('.automation-controls');
            if (controlsContainer) {
                controlsContainer.innerHTML = generateControlButtons(deviceData);
            }

            // Update action links
            updateActionLinks(deviceCard, deviceData);
            
            // Update port number display
            updatePortDisplay(deviceCard, deviceData);
            
            // Try to update status again after other updates (in case it was recreated)
            setTimeout(() => {
                const newStatusElement = deviceCard.querySelector('.device-status');
                if (newStatusElement && newStatusElement.textContent !== translateDeviceStatus(deviceData.deviceStatus)) {
                    const translatedStatus = translateDeviceStatus(deviceData.deviceStatus);
                    newStatusElement.textContent = translatedStatus;
                    newStatusElement.className = `device-status font-medium ${
                        deviceData.deviceStatus === 'running' || deviceData.deviceStatus === 'online' 
                            ? 'text-green-600' 
                            : (deviceData.deviceStatus === 'starting' ? 'text-blue-600' : (deviceData.deviceStatus === 'failed' ? 'text-red-600' : 'text-yellow-600'))
                    }`;
                }
            }, 10);
        }

        // Update port number display based on device status
        function updatePortDisplay(deviceCard, deviceData) {
            const portBadge = deviceCard.querySelector('.port-badge');
            if (!portBadge) return;

            const portNumberSpan = portBadge.querySelector('.port-number');
            if (!portNumberSpan) return;

            if (deviceData.deviceStatus === 'online' && deviceData.port_number) {
                // Online device - show actual port number with blue-purple gradient
                portBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg port-badge';
                
                // Update the icon to WiFi icon for online devices
                const svgElement = portBadge.querySelector('svg');
                if (svgElement) {
                    svgElement.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.5 9.5a13 13 0 0119 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13a9 9 0 0114 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.5 16.5a5.5 5.5 0 017 0" /><circle cx="12" cy="20" r="1.8" fill="currentColor" stroke="none" />';
                }
                
                portNumberSpan.textContent = `{{ __('app.port') }}: ${deviceData.port_number}`;
            } else if (deviceData.port_number) {
                // Offline device - show masked port with gray styling
                portBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-400 text-gray-600 shadow port-badge';
                
                // Update the icon to WiFi icon for offline devices as well
                const svgElement = portBadge.querySelector('svg');
                if (svgElement) {
                    svgElement.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.5 9.5a13 13 0 0119 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13a9 9 0 0114 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.5 16.5a5.5 5.5 0 717 0" /><circle cx="12" cy="20" r="1.8" fill="currentColor" stroke="none" />';
                }
                
                portNumberSpan.textContent = `{{ __('app.port') }}: -`;
            }
        }

        // Update action links based on device status (disabled: do not render assign/unassign controls dynamically)
        function updateActionLinks(deviceCard, deviceData) {
            const actionLinksContainer = deviceCard.querySelector('.action-links');
            if (actionLinksContainer) {
                actionLinksContainer.innerHTML = '';
            }
        }

        // Update table view device status and controls
        function updateTableDeviceStatus(deviceRow, deviceData) {
            // Update status cell
            // Columns: 1 Port, 2 Name, 3 Group, 4 Platform, 5 OS, 6 Status, 7 Create date, 8 Actions
            const statusCell = deviceRow.querySelector('td:nth-child(6)'); // Status column
            if (statusCell) {
                const statusSpan = statusCell.querySelector('span');
                if (statusSpan) {
                    const translatedStatus = translateDeviceStatus(deviceData.deviceStatus);
                    statusSpan.textContent = translatedStatus;
                    statusSpan.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                        deviceData.deviceStatus === 'running' || deviceData.deviceStatus === 'online' 
                            ? 'bg-green-100 text-green-800' 
                            : (deviceData.deviceStatus === 'starting' ? 'bg-blue-100 text-blue-800' : (deviceData.deviceStatus === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'))
                    }`;
                }
            }

            // Update action buttons
            const actionsCell = deviceRow.querySelector('td:nth-child(8)'); // Actions column
            if (actionsCell) {
                const buttonsContainer = actionsCell.querySelector('div');
                if (buttonsContainer) {
                    buttonsContainer.innerHTML = generateTableControlButtons(deviceData);
                }
            }

            // Update port display
            updateTablePortDisplay(deviceRow, deviceData);
        }

        // Generate control buttons HTML for table view
        function generateTableControlButtons(deviceData) {
            let buttonsHTML = '';
            
            if (deviceData.deviceStatus === 'stopped') {
                buttonsHTML += `
                    <button onclick="startDevice(${deviceData.id})" 
                        class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-2 rounded">
                        {{ __('app.start') }}
                    </button>
                `;
            } else if (deviceData.deviceStatus === 'online') {
                buttonsHTML += `
                    <button onclick="stopDevice(${deviceData.id})" 
                        class="bg-red-500 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded">
                        {{ __('app.stop') }}
                    </button>
                `;
            } else {
                buttonsHTML += `
                    <button disabled 
                        class="bg-gray-400 text-white text-xs font-bold py-1 px-2 rounded cursor-not-allowed">
                        {{ __('app.starting') }}
                    </button>
                `;
            }
            
            // Always show refresh button
            buttonsHTML += `
                <button onclick="refreshScreenshot(${deviceData.id})" 
                    class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded ml-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            `;
            
            // No cancel assignment/unassign buttons in chunk or dynamic updates
            return buttonsHTML;
        }

        // Update port display for table view
        function updateTablePortDisplay(deviceRow, deviceData) {
            const portCell = deviceRow.querySelector('td:nth-child(1)'); // Port column
            if (!portCell) return;

            const portBadge = portCell.querySelector('.port-badge');
            if (!portBadge) return;

            const portNumberSpan = portBadge.querySelector('.port-number');
            if (!portNumberSpan) return;

            if (deviceData.deviceStatus === 'online' && deviceData.port_number) {
                // Online device - show actual port number with blue-purple gradient
                portBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-blue-500 to-purple-600 text-white port-badge';
                portNumberSpan.textContent = deviceData.port_number;
            } else if (deviceData.port_number) {
                // Offline device - show masked port with gray styling
                portBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-400 text-gray-600 port-badge';
                portNumberSpan.textContent = '-';
            }
        }

        // Update device screenshot
        function updateDeviceScreenshot(deviceCard, deviceData) {
            const screenshotContainer = deviceCard.querySelector('.screenshot-container');
            if (!screenshotContainer) return;

            if (deviceData.deviceStatus !== 'online') {
                screenshotContainer.innerHTML = `
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                        <div class="text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="mt-1 text-xs text-gray-500">{{ __('app.device_offline') }}</p>
                        </div>
                    </div>
                `;
                return;
            }

            // Online: fetch on demand
            fetch(`/devices/${deviceData.id}/screenshot`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success && data.screenView) {
                        screenshotContainer.innerHTML = `
                            <img src="data:image/png;base64,${data.screenView}" 
                                 alt="{{ __('app.device_screenshot') }}" 
                                 class="w-full h-full object-contain md:object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="absolute inset-0 flex items-center justify-center bg-gray-300" style="display: none;">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('app.screenshot_unavailable') }}</p>
                                </div>
                            </div>
                        `;
                    } else {
                        screenshotContainer.innerHTML = `
                            <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="mt-1 text-xs text-gray-500">{{ __('app.no_screenshot_available') }}</p>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(() => {
                    screenshotContainer.innerHTML = `
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-1 text-xs text-gray-500">{{ __('app.screenshot_unavailable') }}</p>
                            </div>
                        </div>
                    `;
                });
        }

        // Generate control buttons HTML
        function generateControlButtons(deviceData) {
            let buttonsHTML = '';
            
            if (deviceData.deviceStatus === 'stopped') {
                buttonsHTML = `
                    <button onclick="startDevice(${deviceData.id})" 
                        class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 transition-colors">
                        {{ __('app.start_device') }}
                    </button>
                `;
            } else if (deviceData.deviceStatus === 'online') {
                buttonsHTML = `
                    <button onclick="stopDevice(${deviceData.id})" 
                        class="bg-red-600 text-white px-2 py-1 rounded text-xs hover:bg-red-700 transition-colors">
                        {{ __('app.stop_device') }}
                    </button>
                    <button onclick="refreshScreenshot(${deviceData.id})" 
                        class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700 transition-colors">
                        <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                `;
            } else if (deviceData.deviceStatus === 'starting') {
                buttonsHTML = `
                    <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                        {{ __('app.starting') }}
                    </span>
                `;
            }
            
            // Always add cancel assignment button for admins and managers
            
            
            return buttonsHTML;
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-md text-white z-50 ${
                type === 'success' ? 'bg-green-600' : 'bg-red-600'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }



        // Clean up interval when page is unloaded
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        // Device name editing functionality
        function startEditDeviceName(deviceId, currentName) {
            const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (!deviceCard) return;

            const displayElement = deviceCard.querySelector('.device-name-display');
            const editElement = deviceCard.querySelector('.device-name-edit');
            const inputElement = deviceCard.querySelector('.device-name-input');

            if (displayElement && editElement && inputElement) {
                displayElement.classList.add('hidden');
                editElement.classList.remove('hidden');
                inputElement.value = currentName;
                inputElement.focus();
                inputElement.select();
            }
        }

        function cancelEditDeviceName(deviceId) {
            const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (!deviceCard) return;

            const displayElement = deviceCard.querySelector('.device-name-display');
            const editElement = deviceCard.querySelector('.device-name-edit');

            if (displayElement && editElement) {
                displayElement.classList.remove('hidden');
                editElement.classList.add('hidden');
            }
        }

        function saveDeviceName(deviceId) {
            const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
            if (!deviceCard) return;

            const inputElement = deviceCard.querySelector('.device-name-input');
            const customName = inputElement.value.trim();

            if (!customName) {
                showNotification('{{ __('app.custom_name_save_failed') }}', 'error');
                return;
            }

            fetch(`/devices/${deviceId}/custom-name`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ custom_name: customName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateDeviceNameDisplay(deviceCard, customName, true);
                    cancelEditDeviceName(deviceId);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.custom_name_save_failed') }}', 'error');
            });
        }

        function deleteCustomDeviceName(deviceId) {
            if (!confirm('{{ __('app.delete_custom_name') }}?')) {
                return;
            }

            fetch(`/devices/${deviceId}/custom-name`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Get the original device name from the data attribute or fallback
                    const deviceCard = document.querySelector(`[data-device-id="${deviceId}"]`);
                    if (deviceCard) {
                        const originalName = deviceCard.getAttribute('data-original-name') || 'Unknown Device';
                        updateDeviceNameDisplay(deviceCard, originalName, false);
                        cancelEditDeviceName(deviceId);
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.custom_name_delete_failed') }}', 'error');
            });
        }

        function updateDeviceNameDisplay(deviceCard, name, hasCustomName) {
            const displayElement = deviceCard.querySelector('.device-name-display');
            if (displayElement) {
                let html = name;
                if (hasCustomName) {
                    html += '<svg class="w-4 h-4 inline ml-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
                }
                displayElement.innerHTML = html;
            }
        }

        // Table view device name editing functionality
        function startEditDeviceNameTable(deviceId, currentName) {
            const deviceRow = document.querySelector(`tr[data-device-id="${deviceId}"]`);
            if (!deviceRow) return;

            const displayElement = deviceRow.querySelector('.device-name-display-table');
            const editElement = deviceRow.querySelector('.device-name-edit-table');
            const inputElement = deviceRow.querySelector('.device-name-input-table');

            if (displayElement && editElement && inputElement) {
                displayElement.classList.add('hidden');
                editElement.classList.remove('hidden');
                inputElement.value = currentName;
                inputElement.focus();
                inputElement.select();
            }
        }

        function cancelEditDeviceNameTable(deviceId) {
            const deviceRow = document.querySelector(`tr[data-device-id="${deviceId}"]`);
            if (!deviceRow) return;

            const displayElement = deviceRow.querySelector('.device-name-display-table');
            const editElement = deviceRow.querySelector('.device-name-edit-table');

            if (displayElement && editElement) {
                displayElement.classList.remove('hidden');
                editElement.classList.add('hidden');
            }
        }

        function saveDeviceNameTable(deviceId) {
            const deviceRow = document.querySelector(`tr[data-device-id="${deviceId}"]`);
            if (!deviceRow) return;

            const inputElement = deviceRow.querySelector('.device-name-input-table');
            const customName = inputElement.value.trim();

            if (!customName) {
                showNotification('{{ __('app.custom_name_save_failed') }}', 'error');
                return;
            }

            fetch(`/devices/${deviceId}/custom-name`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ custom_name: customName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateDeviceNameDisplayTable(deviceRow, customName, true);
                    cancelEditDeviceNameTable(deviceId);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('{{ __('app.custom_name_save_failed') }}', 'error');
            });
        }

        function updateDeviceNameDisplayTable(deviceRow, name, hasCustomName) {
            const displayElement = deviceRow.querySelector('.device-name-display-table');
            if (displayElement) {
                let html = name;
                if (hasCustomName) {
                    html += '<svg class="w-3 h-3 inline ml-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>';
                }
                displayElement.innerHTML = html;
            }
        }

        // Cookie helper functions
        function setCookie(name, value, days = 365) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // View toggle functionality
        let currentView = getCookie('deviceViewType') || 'cards'; // Get from cookie or default to cards view
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tag-style selects using Select2
            const tagSelects = document.querySelectorAll('select.tag-select');
            if (tagSelects.length && window.$ && $.fn.select2) {
                $(tagSelects).select2({
                    width: '100%',
                    tags: false,
                    multiple: true,
                    dropdownParent: $('#batchCreateModal'),
                    theme: 'default',
                });
            }
            // Initialize user filter options based on selected group without reloading
            (function initUserFilter() {
                const groupSelect = document.getElementById('groupFilter');
                const userSelect = document.getElementById('userFilter');
                if (!groupSelect || !userSelect) return;
                function populateUsers() {
                    const selected = groupSelect.options[groupSelect.selectedIndex];
                    const usersData = selected ? selected.getAttribute('data-users') : null;
                    userSelect.innerHTML = `<option value=\"\">{{ __('app.all_users') }}</option>`;
                    if (!usersData) {
                        userSelect.disabled = true;
                        return;
                    }
                    try {
                        const users = JSON.parse(usersData);
                        users.forEach(u => {
                            const opt = document.createElement('option');
                            opt.value = u.id;
                            opt.textContent = u.name;
                            userSelect.appendChild(opt);
                        });
                        const currentUserId = "{{ request('user_id') }}";
                        if (currentUserId) {
                            userSelect.value = currentUserId;
                        }
                        userSelect.disabled = false;
                    } catch(e) {
                        userSelect.disabled = true;
                    }
                }
                populateUsers();
                groupSelect.addEventListener('change', populateUsers);
            })();
            // Initialize background refresh
            initBackgroundRefresh();
            
            const cardViewBtn = document.getElementById('cardViewBtn');
            const tableViewBtn = document.getElementById('tableViewBtn');
            const deviceGrid = document.getElementById('deviceGrid');
            const deviceTable = document.getElementById('deviceTable');

            // View toggle functions
            function showCardView() {
                currentView = 'cards';
                setCookie('deviceViewType', 'cards'); // Save to cookie
                if (deviceGrid) deviceGrid.style.display = 'grid';
                if (deviceTable) deviceTable.style.display = 'none';
                cardViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                cardViewBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
                tableViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                tableViewBtn.classList.add('text-gray-600', 'hover:text-gray-900');
                
                // Re-run search to maintain filter state
                performSearch();
            }

            function showTableView() {
                currentView = 'table';
                setCookie('deviceViewType', 'table'); // Save to cookie
                if (deviceGrid) deviceGrid.style.display = 'none';
                if (deviceTable) deviceTable.style.display = 'block';
                tableViewBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                tableViewBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
                cardViewBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                cardViewBtn.classList.add('text-gray-600', 'hover:text-gray-900');
                
                // Re-run search to maintain filter state
                performSearch();
            }

            // Add event listeners
            cardViewBtn.addEventListener('click', showCardView);
            tableViewBtn.addEventListener('click', showTableView);

            // Initialize view based on saved preference
            if (currentView === 'table') {
                showTableView();
            } else {
                showCardView();
            }

            // Initialize hidden search input with current search term
            updateSearchHidden();

            // Search functionality
            const searchInput = document.getElementById('search');
            if (searchInput) {
                searchInput.addEventListener('input', performSearch);
            }

            // Online-only toggle (client-side filter)
            const onlyOnlineToggle = document.getElementById('onlyOnlineToggle');
            if (onlyOnlineToggle) {
                onlyOnlineToggle.addEventListener('change', function() {
                    performSearch();
                });
            }

            function performSearch() {
                const searchInput = document.getElementById('search');
                const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const deviceCards = document.querySelectorAll('.device-card');
                const deviceRows = document.querySelectorAll('.device-table-row');
                const deviceGrid = document.getElementById('deviceGrid');
                const deviceTable = document.getElementById('deviceTable');
                const noResults = document.getElementById('noResults');
                const onlyOnline = (document.getElementById('onlyOnlineToggle')?.checked) || false;
                let visibleCount = 0;

                if (currentView === 'cards') {
                    deviceCards.forEach(card => {
                        const deviceStatus = (card.getAttribute('data-device-status-text') || '').toLowerCase();
                        
                        const allowedByStatus = !onlyOnline || deviceStatus === 'online';
                        const matchesSearch = searchTerm === '' ? true : checkDeviceMatch(card, searchTerm);
                        
                        if (matchesSearch && allowedByStatus) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Show/hide no results message for cards
                    if (visibleCount === 0 && (searchTerm !== '' || onlyOnline)) {
                        if (deviceGrid) deviceGrid.style.display = 'none';
                        if (noResults) noResults.classList.remove('hidden');
                    } else {
                        if (deviceGrid) deviceGrid.style.display = 'grid';
                        if (noResults) noResults.classList.add('hidden');
                    }
                } else {
                    deviceRows.forEach(row => {
                        const deviceStatus = (row.getAttribute('data-device-status-text') || '').toLowerCase();
                        
                        const allowedByStatus = !onlyOnline || deviceStatus === 'online';
                        const matchesSearch = searchTerm === '' ? true : checkDeviceMatch(row, searchTerm);
                        
                        if (matchesSearch && allowedByStatus) {
                            row.style.display = 'table-row';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Show/hide no results message for table
                    if (visibleCount === 0 && (searchTerm !== '' || onlyOnline)) {
                        if (deviceTable) deviceTable.style.display = 'none';
                        if (noResults) noResults.classList.remove('hidden');
                    } else {
                        if (deviceTable) deviceTable.style.display = 'block';
                        if (noResults) noResults.classList.add('hidden');
                    }
                }
            }

            function checkDeviceMatch(element, searchTerm) {
                const deviceName = (element.getAttribute('data-device-name') || '').toLowerCase();
                const originalName = (element.getAttribute('data-original-name') || '').toLowerCase();
                const devicePlatform = (element.getAttribute('data-device-platform') || '').toLowerCase();
                const deviceOs = (element.getAttribute('data-device-os') || '').toLowerCase();
                const deviceStatus = (element.getAttribute('data-device-status-text') || '').toLowerCase();
                const deviceGroup = (element.getAttribute('data-device-group') || '').toLowerCase();
                const devicePort = (element.getAttribute('data-device-port') || '').toLowerCase();

                return deviceName.includes(searchTerm) ||
                       originalName.includes(searchTerm) ||
                       devicePlatform.includes(searchTerm) ||
                       deviceOs.includes(searchTerm) ||
                       deviceStatus.includes(searchTerm) ||
                       deviceGroup.includes(searchTerm) ||
                       devicePort.includes(searchTerm);
            }

            // Infinite scroll loader
            let chunkOffset = 20;
            const chunkLimit = 20;
            let isLoadingChunk = false;
            let hasMoreChunks = {{ $devices->count() > 20 ? 'true' : 'false' }};
            const devicesOrderToken = @json($devicesOrderToken ?? null);

            const infiniteLoader = document.getElementById('infiniteLoader');
            const searchInputEl = document.getElementById('search');
            const onlyOnlineEl = document.getElementById('onlyOnlineToggle');

            async function loadNextChunk() {
                if (isLoadingChunk || !hasMoreChunks) return;
                isLoadingChunk = true;
                if (infiniteLoader) infiniteLoader.classList.remove('hidden');

                const params = new URLSearchParams();
                params.set('offset', String(chunkOffset));
                params.set('limit', String(chunkLimit));
                params.set('view', currentView);
                if (devicesOrderToken) params.set('token', devicesOrderToken);
                const groupId = '{{ request('group_id') }}';
                const userId = '{{ request('user_id') }}';
                if (groupId) params.set('group_id', groupId);
                if (userId) params.set('user_id', userId);
                const searchVal = (searchInputEl?.value || '').trim();
                if (searchVal) params.set('search', searchVal);
                if (onlyOnlineEl?.checked) params.set('only_online', '1');

                try {
                    const res = await fetch(`/devices/chunk?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        // If no HTML returned, stop further loading
                        if (!data.html || data.html.trim() === '') {
                            hasMoreChunks = false;
                            return;
                        }
                        if (currentView === 'cards') {
                            const grid = document.getElementById('deviceGrid');
                            if (grid && data.html) {
                                const temp = document.createElement('div');
                                temp.innerHTML = data.html;
                                Array.from(temp.children).forEach(child => grid.appendChild(child));
                                
                                // Add a small delay to ensure DOM is fully ready before background refresh
                                setTimeout(() => {
                                    // Mark newly loaded devices as ready for status updates
                                    const newCards = grid.querySelectorAll('.device-card:not([data-status-ready])');
                                    newCards.forEach(card => {
                                        // Ensure the device-status class exists
                                        let statusElement = card.querySelector('.device-status');
                                        if (!statusElement) {
                                            // Look for the status text pattern and add the class if missing
                                            const statusTextPattern = /{{ __('app.device_status') }}:/;
                                            const textNodes = Array.from(card.querySelectorAll('*')).filter(el => 
                                                el.textContent && statusTextPattern.test(el.textContent)
                                            );
                                            
                                            if (textNodes.length > 0) {
                                                const potentialStatusSpan = textNodes[0].querySelector('span');
                                                if (potentialStatusSpan && !potentialStatusSpan.classList.contains('device-status')) {
                                                    potentialStatusSpan.classList.add('device-status');
                                                }
                                            }
                                        }
                                        card.setAttribute('data-status-ready', 'true');
                                    });
                                }, 100);
                            }
                        } else {
                            const body = document.getElementById('deviceTableBody');
                            if (body && data.html) {
                                const temp = document.createElement('tbody');
                                temp.innerHTML = data.html;
                                Array.from(temp.children).forEach(child => body.appendChild(child));
                                
                                // Add a small delay to ensure DOM is fully ready before background refresh
                                setTimeout(() => {
                                    // Mark newly loaded rows as ready for status updates
                                    const newRows = body.querySelectorAll('.device-table-row:not([data-status-ready])');
                                    newRows.forEach(row => {
                                        // Ensure the device-status class exists for table rows
                                        let statusElement = row.querySelector('.device-status');
                                        if (!statusElement) {
                                            // Look for the status text pattern and add the class if missing
                                            const statusTextPattern = /{{ __('app.device_status') }}:/;
                                            const textNodes = Array.from(row.querySelectorAll('*')).filter(el => 
                                                el.textContent && statusTextPattern.test(el.textContent)
                                            );
                                            
                                            if (textNodes.length > 0) {
                                                const potentialStatusSpan = textNodes[0].querySelector('span');
                                                if (potentialStatusSpan && !potentialStatusSpan.classList.contains('device-status')) {
                                                    potentialStatusSpan.classList.add('device-status');
                                                }
                                            }
                                        }
                                        row.setAttribute('data-status-ready', 'true');
                                    });
                                }, 100);
                            }
                        }
                        chunkOffset = data.nextOffset;
                        hasMoreChunks = !!data.hasMore;
                        // Re-apply client-side filter to newly appended items
                        if (typeof performSearch === 'function') {
                            performSearch();
                        }
                        
                        // Add a small delay to ensure background refresh doesn't interfere
                        setTimeout(() => {
                            // Chunk loading completed
                        }, 200);
                    }
                } catch (e) {
                    // Failed to load next chunk
                    try { showNotification('{{ __('app.chunk_preload_failed') }}', 'error'); } catch (_) {}
                } finally {
                    if (infiniteLoader) infiniteLoader.classList.add('hidden');
                    isLoadingChunk = false;
                }
            }

            function onScrollLoadMore() {
                const scrollY = window.scrollY || window.pageYOffset;
                const viewport = window.innerHeight || document.documentElement.clientHeight;
                const full = document.documentElement.scrollHeight;
                // Start loading when user is within 600px of bottom
                if (full - (scrollY + viewport) < 600) {
                    loadNextChunk();
                }
            }

            window.addEventListener('scroll', onScrollLoadMore);
            // Reset chunk loading state when filters change
            function resetChunkState() {
                chunkOffset = 20;
                hasMoreChunks = true;
                // Optionally remove previously appended items beyond first 20
                const grid = document.getElementById('deviceGrid');
                const body = document.getElementById('deviceTableBody');
                if (grid && currentView === 'cards') {
                    // Keep first 20 cards
                    const cards = grid.querySelectorAll('.device-card');
                    for (let i = 20; i < cards.length; i++) cards[i].remove();
                } else if (body && currentView === 'table') {
                    const rows = body.querySelectorAll('.device-table-row');
                    for (let i = 20; i < rows.length; i++) rows[i].remove();
                }
                // Immediately try loading a chunk for new filters
                loadNextChunk();
            }
            if (searchInputEl) searchInputEl.addEventListener('input', () => { resetChunkState(); });
            if (onlyOnlineEl) onlyOnlineEl.addEventListener('change', () => { resetChunkState(); });
            // Also trigger once after initial render
            setTimeout(onScrollLoadMore, 500);

            // Helper to detect if there are any currently visible results
            function hasAnyVisibleResult() {
                if (currentView === 'cards') {
                    const cards = document.querySelectorAll('.device-card');
                    for (const c of cards) { if (c.style.display !== 'none') return true; }
                    return false;
                } else {
                    const rows = document.querySelectorAll('.device-table-row');
                    for (const r of rows) { if (r.style.display !== 'none') return true; }
                    return false;
                }
            }

            // If after search there are no visible results, auto-fetch a few more chunks
            if (typeof performSearch === 'function') {
                const originalPerformSearch = performSearch;
                window.performSearch = function() {
                    originalPerformSearch();
                    const hasFilter = ((searchInputEl?.value || '').trim() !== '') || (onlyOnlineEl?.checked === true);
                    // Auto-fetch more only when there are active filters and no visible results
                    if (hasFilter && !hasAnyVisibleResult()) {
                        (async () => {
                            let attempts = 0;
                            // Try up to 5 chunks to find matches under current filters
                            while (!hasAnyVisibleResult() && hasMoreChunks && attempts < 5) {
                                await loadNextChunk();
                                attempts++;
                            }
                        })();
                    }
                };
            }

            // Mark initially loaded devices as ready for status updates
            setTimeout(() => {
                const initialCards = document.querySelectorAll('.device-card');
                initialCards.forEach(card => {
                    card.setAttribute('data-status-ready', 'true');
                });
                // Immediately load screenshots for online devices on initial render
                const onlineCards = document.querySelectorAll('.device-card[data-device-status="online"]');
                onlineCards.forEach(card => {
                    const deviceId = card.getAttribute('data-device-id');
                    if (deviceId) {
                        updateDeviceScreenshot(card, { id: deviceId, deviceStatus: 'online' });
                    }
                });
                
                const initialRows = document.querySelectorAll('.device-table-row');
                initialRows.forEach(row => {
                    row.setAttribute('data-status-ready', 'true');
                });
            }, 100);
        });

        // Also try initializing immediately if DOM is already loaded
        if (document.readyState !== 'loading') {
            initBackgroundRefresh();
        }

        // Quick assignment form functionality
        @if($user->isAdmin())
        document.getElementById('quickAssignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/user-assignments/assign', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while assigning user to group');
            });
        });

        function removeQuickAssignment(userId, groupId) {
            if (!confirm('Are you sure you want to remove this user from the group?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('group_id', groupId);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch('/user-assignments/remove', {
                method: 'DELETE',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred while removing user from group');
            });
        }
        @endif

        // Function to update search hidden input
        function updateSearchHidden() {
            const searchInput = document.getElementById('search');
            const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const searchHidden = document.getElementById('searchHidden');
            if (searchHidden) {
                searchHidden.value = searchTerm;
            }
        }
    </script>
    @endpush
</x-app-layout>
