@foreach($devices as $device)
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow device-card" 
         data-device-id="{{ $device->id }}" 
         data-device-status="{{ $device->deviceStatus }}"
         data-screen-hash="{{ md5($device->screenView ?? '') }}"
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
                    @if($device->deviceStatus === 'online' && !empty($device->screenView))
                        <img src="data:image/png;base64,{{ $device->screenView }}" 
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
                    @else
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-300">
                            <div class="text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="mt-1 text-xs text-gray-500">
                                    @if($device->deviceStatus === 'online')
                                        {{ __('app.no_screenshot_available') }}
                                    @else
                                        {{ __('app.device_offline') }}
                                    @endif
                                </p>
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
                        <span class="device-status font-medium {{ $device->deviceStatus === 'running' || $device->deviceStatus === 'online' ? 'text-green-600' : ($device->deviceStatus === 'starting' ? 'text-blue-600' : 'text-yellow-600') }}">
                            {{ __('app.' . ($device->deviceStatus ?? 'unknown')) }}
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

                <!-- Action Links intentionally excluded in chunked view -->
            </div>
        </div>
    </div>
@endforeach


