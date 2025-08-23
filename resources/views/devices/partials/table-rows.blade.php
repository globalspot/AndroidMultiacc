@foreach($devices as $device)
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
        @php
            // Normalize raw backend phrases and app-prefixed localization keys
            $statusKey = (strpos($statusKey, 'app.') === 0) ? substr($statusKey, 4) : $statusKey;
            $lowerStatus = mb_strtolower($statusKey);
            if ($lowerStatus === 'starting device...' || $lowerStatus === 'device starting...' || $lowerStatus === 'starting...') {
                $statusKey = 'starting';
            } elseif ($lowerStatus === 'creating device...' || $lowerStatus === 'device creating...' || $lowerStatus === 'creating...') {
                $statusKey = 'creating';
            } elseif ($lowerStatus === 'stopping device...' || $lowerStatus === 'device stopping...' || $lowerStatus === 'stopping...') {
                $statusKey = 'stopping';
            } else {
                $statusKey = $lowerStatus;
            }
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
                {{ __('app.' . $statusKey) }}
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
        </div>
    </td>
</tr>
@endforeach


