<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.device_assignment_management') }}
            </h2>
            <x-language-switcher />
        </div>
    </x-slot>
    
    <style>
        .current-user-option {
            font-weight: 600;
            color: #2563eb;
            background-color: #eff6ff;
        }
        .current-user-option:hover {
            background-color: #dbeafe;
        }
    </style>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('app.device_assignment_management') }}</h1>
        <p class="text-gray-600">{{ __('app.device_assignment_description') }}</p>
        
        <!-- Current User Info -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">
                        {{ __('app.logged_in_as') }}: <span class="font-semibold">{{ $user->name }}</span> 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">
                            {{ __('app.manager') }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Assignment Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.assign_device_to_user') }}</h2>
        
        <!-- Information for managers -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>{{ __('app.manager_note_title') }}:</strong> {{ __('app.manager_note_text') }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <div id="messageArea" class="hidden mb-4">
            <div id="successMessage" class="hidden bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p id="successText" class="text-sm text-green-700"></p>
                    </div>
                </div>
            </div>
            <div id="errorMessage" class="hidden bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p id="errorText" class="text-sm text-red-700"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <form id="deviceAssignmentForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.group') }}</label>
                    <select id="group_id" name="group_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadGroupUsers(this.value)">
                        <option value="">{{ __('app.select_group') }}</option>
                        @foreach($managerGroups as $assignment)
                            <option value="{{ $assignment->device_group_id }}">{{ $assignment->deviceGroup->name }} ({{ $assignment->deviceGroup->gate_url ?? __('app.no_gate_url') }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.user') }}</label>
                    <select id="user_id" name="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                        <option value="">{{ __('app.select_group_first') }}</option>
                    </select>
                </div>
                <div>
                    <label for="device_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.device') }}</label>
                    <select id="device_id" name="device_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                        <option value="">{{ __('app.select_group_first') }}</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('app.assign_device') }}
                </button>
            </div>
        </form>
        
        

        <!-- Group Invite Link (for selected group) -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-800 mb-3">{{ __('app.group_invite_links') }}</h3>
            <p class="text-sm text-gray-600 mb-3">{{ __('app.group_invite_links_hint') }}</p>
            <div class="flex gap-2">
                <button id="selectedGenerateBtn" type="button" onclick="generateInviteForSelected()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{ __('app.generate_invite_link') }}
                </button>
                <input id="selectedInviteInput" type="text" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md" placeholder="{{ __('app.invite_link_will_appear_here') }}" />
                <button type="button" onclick="copySelectedInvite()" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">{{ __('app.copy') }}</button>
            </div>
        </div>
    </div>

    <!-- Group Device Assignment (bulk) -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.group_device_assignment') }}</h2>
        <p class="text-gray-600 mb-4">{{ __('app.group_device_assignment_hint') }}</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="bulk_group_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.group') }}</label>
                <select id="bulk_group_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('app.select_group') }}</option>
                    @foreach($managerGroups as $assignment)
                        <option value="{{ $assignment->device_group_id }}">{{ $assignment->deviceGroup->name }} ({{ $assignment->deviceGroup->gate_url ?? __('app.no_gate_url') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.free_devices') }}</label>
                <div class="p-2 bg-gray-50 border border-gray-200 rounded-md"><span id="freeCount">-</span></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.assigned_devices') }}</label>
                <div class="p-2 bg-gray-50 border border-gray-200 rounded-md"><span id="assignedCount">-</span></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label for="bulk_user_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.group_member') }}</label>
                <select id="bulk_user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                    <option value="">{{ __('app.select_group_first') }}</option>
                </select>
            </div>
            <div>
                <label for="bulk_count" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.devices_to_assign') }}</label>
                <input id="bulk_count" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0" disabled />
            </div>
            <div class="flex flex-col justify-end">
                <label class="block text-sm font-medium text-transparent mb-2 select-none">&nbsp;</label>
                <button id="bulk_assign_btn" type="button" class="w-full bg-blue-600 text-white px-6 h-11 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50" disabled>
                    {{ __('app.assign') }}
                </button>
            </div>
        </div>
        <p id="bulk_count_hint" class="text-xs text-gray-500 mt-2"></p>
    </div>

    <!-- Current Device Assignments -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.current_device_assignments') }}</h2>
        <!-- Assigned user filter (only users from selected group) -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="assigned_user_filter" class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.user') }}</label>
                <select id="assigned_user_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('app.select_group_first') }}</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <div class="max-h-[600px] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.user') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.device') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.group') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="assignmentsTable" class="bg-white divide-y divide-gray-200">
                        <!-- Assignments will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- My Device Assignments -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.my_device_assignments') }}</h2>
        <div class="overflow-x-auto">
            <div class="h-[600px] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.device') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.group') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="myAssignmentsTable" class="bg-white divide-y divide-gray-200">
                    <!-- My assignments will be loaded here -->
                </tbody>
            </table>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <p>{{ __('app.my_device_assignments_description') }}</p>
        </div>
    </div>

    <!-- Group Information -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.your_managed_groups') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($managerGroups as $assignment)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $assignment->deviceGroup->name }}</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><strong>{{ __('app.gate_url') }}:</strong> {{ $assignment->deviceGroup->gate_url ?? __('app.no_gate_url') }}</p>
                        <p><strong>{{ __('app.device_limit') }}:</strong> {{ $assignment->deviceGroup->device_limit ?? __('app.unlimited') }}</p>
                        <p><strong>{{ __('app.running_devices') }}:</strong> {{ $assignment->deviceGroup->getRunningDevicesCount() }}</p>
                        <p><strong>{{ __('app.members') }}:</strong> {{ $assignment->deviceGroup->members()->count() }}</p>
                    </div>
                    <div class="mt-3">
                        <button id="generateBtn-{{ $assignment->device_group_id }}" onclick="generateInvite({{ $assignment->device_group_id }})" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 w-full">
                            {{ __('app.generate_invite_link') }}
                        </button>
                        <div id="inviteBox-{{ $assignment->device_group_id }}" class="hidden mt-2">
                            <div class="flex gap-2">
                                <input id="inviteInput-{{ $assignment->device_group_id }}" type="text" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-md" />
                                <button onclick="copyInvite({{ $assignment->device_group_id }})" class="bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700">{{ __('app.copy') }}</button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.share_invite_hint') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Manager Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('app.manager_summary') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="text-2xl font-bold text-blue-600" id="totalGroups">{{ count($managerGroups) }}</div>
                <div class="text-sm text-blue-600">{{ __('app.managed_groups') }}</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="text-2xl font-bold text-green-600" id="totalDevices">-</div>
                <div class="text-sm text-green-600">{{ __('app.available_devices') }}</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg border border-purple-200">
                <div class="text-2xl font-bold text-purple-600" id="myDevices">-</div>
                <div class="text-sm text-purple-600">{{ __('app.my_assignments') }}</div>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg border border-orange-200">
                @php
                    $totalMembers = 0;
                    foreach ($managerGroups as $mg) {
                        $totalMembers += $mg->deviceGroup ? $mg->deviceGroup->members()->count() : 0;
                    }
                @endphp
                <div class="text-2xl font-bold text-orange-600" id="totalMembers">{{ $totalMembers }}</div>
                <div class="text-sm text-orange-600">{{ __('app.total_members') }}</div>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 text-center">
            <p>{{ __('app.manager_summary_description') }}</p>
        </div>
    </div>
</div>

<!-- Group Details Modal -->
<div id="groupDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">{{ __('app.group_details') }}</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Cookie helpers (global)
function setCookie(name, value, days) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}
function getCookie(name) {
    return document.cookie.split('; ').reduce((acc, cookie) => {
        const [k, v] = cookie.split('=');
        return k === name ? decodeURIComponent(v) : acc;
    }, null);
}
// Pre-populate existing invites and disable generate buttons where applicable
document.addEventListener('DOMContentLoaded', () => {
    const existingInvites = @json($existingInvites ?? []);
    Object.keys(existingInvites).forEach(groupId => {
        const invite = existingInvites[groupId];
        const input = document.getElementById(`inviteInput-${groupId}`);
        const box = document.getElementById(`inviteBox-${groupId}`);
        const btn = document.getElementById(`generateBtn-${groupId}`);
        if (input && box && invite && invite.token) {
            input.value = `${window.location.origin}/invites/${invite.token}`;
            box.classList.remove('hidden');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    });

    // If a group is selected and has an invite, also fill the selected input
    const groupSelect = document.getElementById('group_id');
    const selectedInput = document.getElementById('selectedInviteInput');
    const selectedGenerateBtn = document.getElementById('selectedGenerateBtn');
    function syncSelectedInvite() {
        const gid = groupSelect.value;
        if (gid && existingInvites[gid] && existingInvites[gid].token) {
            selectedInput.value = `${window.location.origin}/invites/${existingInvites[gid].token}`;
            if (selectedGenerateBtn) {
                selectedGenerateBtn.disabled = true;
                selectedGenerateBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        } else {
            // No existing invite for selected group
            selectedInput.value = '';
            if (selectedGenerateBtn) {
                selectedGenerateBtn.disabled = false;
                selectedGenerateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    if (groupSelect) {
        groupSelect.addEventListener('change', () => {
            // Persist selection
            if (groupSelect.value) {
                setCookie('last_selected_group_id', groupSelect.value, 365);
            }
            syncSelectedInvite();
            // Load users and devices for the selected group
            if (groupSelect.value) {
                loadGroupUsers(groupSelect.value);
            }
        });

        // Try to restore from cookie first
        const savedGroupId = getCookie('last_selected_group_id');
        if (savedGroupId && Array.from(groupSelect.options).some(o => o.value == savedGroupId)) {
            groupSelect.value = savedGroupId;
        } else if (!groupSelect.value) {
            // If no saved value or invalid, auto-select first available group
            const firstOption = Array.from(groupSelect.options).find(o => o.value);
            if (firstOption) {
                groupSelect.value = firstOption.value;
            }
        }

        // Initial population for selected group
        syncSelectedInvite();
        if (groupSelect.value) {
            loadGroupUsers(groupSelect.value);
        }
    }
});
function loadGroupUsers(groupId) {
    if (!groupId) {
        document.getElementById('user_id').innerHTML = '<option value="">{{ __('app.select_group_first') }}</option>';
        document.getElementById('device_id').innerHTML = '<option value="">{{ __('app.select_group_first') }}</option>';
        document.getElementById('user_id').disabled = true;
        document.getElementById('device_id').disabled = true;
        return;
    }

    // Load users in the group
    fetch(`/device-assignments/group-users/${groupId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const userSelect = document.getElementById('user_id');
                userSelect.innerHTML = '<option value="">{{ __('app.select_user') }}</option>';
                
                // Get current user info from the page
                const currentUser = @json($user);
                
                data.users.forEach(user => {
                    const isCurrentUser = user.id === currentUser.id;
                    const userLabel = isCurrentUser ? 
                        `${user.name} (${user.email}) - {{ __('app.you') }}` : 
                        `${user.name} (${user.email})`;
                    
                    userSelect.innerHTML += `<option value="${user.id}" ${isCurrentUser ? 'class="current-user-option"' : ''}>${userLabel}</option>`;
                });
                userSelect.disabled = false;

                // Also populate the assigned user filter in the table section
                const filterSelect = document.getElementById('assigned_user_filter');
                if (filterSelect) {
                    let html = `<option value="">${@json(__('app.all'))}</option>`;
                    data.users.forEach(u => {
                        html += `<option value="${u.id}">${u.name} (${u.email})</option>`;
                    });
                    filterSelect.innerHTML = html;
                    filterSelect.disabled = false;
                    filterSelect.removeAttribute('disabled');
                    // Apply table filter right after refresh
                    applyAssignmentsFilter();
                }
            }
        });

    // Show loading state
    const deviceSelect = document.getElementById('device_id');
    deviceSelect.innerHTML = '<option value="">{{ __('app.loading_devices') }}</option>';
    deviceSelect.disabled = true;

    // Load devices from gate URL
    fetch(`/device-assignments/gate-devices/${groupId}`)
        .then(response => response.json())
        .then(data => {
            // Debug logging
            console.log('Device assignment data received:', data);
            if (data.debug) {
                console.log('Debug info:', data.debug);
            }
            
            if (data.success) {
                const deviceSelect = document.getElementById('device_id');
                
                // Build HTML string first for better performance
                let optionsHtml = '<option value="">{{ __('app.select_device') }}</option>';
                data.devices.forEach((device, index) => {
                    // Debug first few devices
                    if (index < 3) {
                        console.log('Device sample:', device);
                    }
                    
                    // Use custom name if available, otherwise use device name
                    const deviceName = device.custom_name || device.deviceName || `{{ __('app.device') }} ${device.id}`;
                    const deviceOs = device.deviceOs || '{{ __('app.unknown_os') }}';
                    const displayName = `${deviceName} - ${deviceOs}`;
                    
                    optionsHtml += `<option value="${device.id}">${displayName}</option>`;
                });
                
                // Set innerHTML once instead of multiple times
                deviceSelect.innerHTML = optionsHtml;
                deviceSelect.disabled = false;
            } else {
                const deviceSelect = document.getElementById('device_id');
                deviceSelect.innerHTML = '<option value="">{{ __('app.no_devices_available') }}</option>';
                deviceSelect.disabled = true;
            }
        });
}

function loadGroupDetails(groupId) {
    // Load group details and show modal
    fetch(`/device-assignments/group-users/${groupId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('groupDetailsModal');
                const modalTitle = document.getElementById('modalTitle');
                const modalContent = document.getElementById('modalContent');
                
                // Get group info  
                const managerGroups = @json($managerGroups);
                const currentAssignment = managerGroups.find(assignment => assignment.device_group_id == groupId);
                const currentGroup = currentAssignment ? currentAssignment.deviceGroup : null;
                
                if (!currentGroup) {
                    alert(@json(__('app.group_not_found')));
                    return;
                }
                
                modalTitle.textContent = `${currentGroup.name} - {{ __('app.details') }}`;
                
                let content = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ __('app.group_information') }}</h4>
                                <p class="text-sm text-gray-600">{{ __('app.gate_url') }}: ${currentGroup.gate_url || '{{ __('app.no_gate_url') }}'}</p>
                                <p class="text-sm text-gray-600">{{ __('app.device_limit') }}: ${currentGroup.device_limit || '{{ __('app.unlimited') }}'}</p>
                                <p class="text-sm text-gray-600">{{ __('app.running_devices') }}: ${currentGroup.getRunningDevicesCount ? currentGroup.getRunningDevicesCount() : 'N/A'}</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ __('app.members') }} (${data.users.length})</h4>
                                <div class="max-h-40 overflow-y-auto">
                `;
                
                data.users.forEach(user => {
                    content += `<p class="text-sm text-gray-600">• ${user.name} (${user.email})</p>`;
                });
                
                content += `
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                modalContent.innerHTML = content;
                modal.classList.remove('hidden');
            }
        });
}

function closeModal() {
    document.getElementById('groupDetailsModal').classList.add('hidden');
}

document.getElementById('deviceAssignmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    // Preserve currently selected group before reset
    const groupSelect = document.getElementById('group_id');
    const lastGroupId = groupSelect ? groupSelect.value : '';
    
    fetch('/device-assignments/assign', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('success', data.message);
            this.reset();
            // Restore last selected group and persist it
            if (groupSelect && lastGroupId) {
                groupSelect.value = lastGroupId;
                setCookie('last_selected_group_id', lastGroupId, 365);
                // Trigger change to reload users/devices and sync invite UI
                groupSelect.dispatchEvent(new Event('change'));
            }
            document.getElementById('user_id').disabled = true;
            document.getElementById('device_id').disabled = true;
            loadAssignments();
            updateManagerSummary(); // Update summary after successful assignment
        } else {
            showMessage('error', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', 'An error occurred while assigning device to user');
    });
});

function loadAssignments() {
    // Load both general assignments and current user's assignments
    const assignmentsTable = document.getElementById('assignmentsTable');
    const currentUser = @json($user);

    // Fetch all device assignments for groups managed by the current manager
    fetch(`/device-assignments/managed-assignments`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let tableContent = '';

                if (!data.assignments || data.assignments.length === 0) {
                    tableContent = `
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                {{ __('app.device_assignments_placeholder') }}
                            </td>
                        </tr>
                    `;
                } else {
                    data.assignments.forEach(assignment => {
                        const device = assignment.device_info || {};
                        const deviceName = (device.custom_name || device.deviceName || `{{ __('app.device') }}`) + ` (${device.id || assignment.device_id})`;
                        const deviceOs = device.deviceOs || '{{ __('app.unknown_os') }}';
                        const groupName = assignment.device_group && assignment.device_group.name ? assignment.device_group.name : (assignment.deviceGroup && assignment.deviceGroup.name ? assignment.deviceGroup.name : '{{ __('app.no_group') }}');
                        const userName = assignment.user && assignment.user.name ? assignment.user.name : '{{ __('app.unknown_user') }}';

                        tableContent += `
                            <tr data-user-id="${assignment.user_id}" data-group-id="${assignment.device_group_id}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${userName}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${deviceName}</div>
                                    <div class="text-sm text-gray-500">${deviceOs}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${groupName}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('app.active') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="removeAssignment('${assignment.device_id}', ${assignment.user_id})" class="text-red-600 hover:text-red-900">
                                        {{ __('app.remove') }}
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                assignmentsTable.innerHTML = tableContent;
                // Apply filter after rendering
                applyAssignmentsFilter();
            } else {
                assignmentsTable.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">{{ __('app.error_loading_assignments') }}</td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading assignments:', error);
            assignmentsTable.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-red-500">{{ __('app.error_loading_assignments_try_again') }}</td>
                </tr>
            `;
        });

    // Also refresh the user's own assignments
    loadMyAssignments();
}

function loadMyAssignments() {
    const currentUser = @json($user);
    const myAssignmentsTable = document.getElementById('myAssignmentsTable');
    
    // Get current user's device assignments
    fetch(`/device-assignments/my-assignments`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let tableContent = '';
                
                // Update the summary count
                document.getElementById('myDevices').textContent = data.assignments.length;
                
                if (data.assignments.length === 0) {
                    tableContent = `
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                {{ __('app.no_device_assignments_yet') }}
                            </td>
                        </tr>
                    `;
                } else {
                    data.assignments.forEach(assignment => {
                        const device = assignment.device_info || {};
                        const deviceName = device.custom_name || device.deviceName || `{{ __('app.device') }} ${assignment.device_id}`;
                        const deviceOs = device.deviceOs || '{{ __('app.unknown_os') }}';
                        const groupName = assignment.device_group ? assignment.device_group.name : '{{ __('app.no_group') }}';
                        
                        tableContent += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${deviceName}</div>
                                    <div class="text-sm text-gray-500">${deviceOs} (ID: ${assignment.device_id})</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${groupName}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('app.active') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="removeMyAssignment('${assignment.device_id}')" 
                                            class="text-red-600 hover:text-red-900">
                                        {{ __('app.remove') }}
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                myAssignmentsTable.innerHTML = tableContent;
            } else {
                myAssignmentsTable.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-red-500">
                            {{ __('app.error_loading_assignments') }}: ${data.message}
                        </td>
                    </tr>
                `;
                // Set count to 0 on error
                document.getElementById('myDevices').textContent = '0';
            }
        })
        .catch(error => {
            console.error('Error loading my assignments:', error);
            myAssignmentsTable.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-red-500">
                        {{ __('app.error_loading_assignments_try_again') }}
                    </td>
                </tr>
            `;
            // Set count to 0 on error
            document.getElementById('myDevices').textContent = '0';
        });
}

function removeMyAssignment(deviceId) {
    if (!confirm('{{ __('app.confirm_remove_device_assignment') }}')) {
        return;
    }
    
    const currentUser = @json($user);
    
    fetch('/device-assignments/remove', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            device_id: deviceId,
            user_id: currentUser.id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('success', data.message);
            loadMyAssignments();
            updateManagerSummary(); // Update summary after successful removal
        } else {
            showMessage('error', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('error', 'An error occurred while removing the device assignment');
    });
}

function removeAssignment(deviceId, userId) {
    if (!confirm('{{ __('app.confirm_remove_device_assignment') }}')) {
        return;
    }

    fetch('/device-assignments/remove', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ device_id: deviceId, user_id: userId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message);
                loadAssignments();
                updateManagerSummary();
            } else {
                showMessage('error', 'Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'An error occurred while removing the device assignment');
        });
}

// Quick assignment actions removed per request

// Close modal when clicking outside
(function() {
    const modalEl = document.getElementById('groupDetailsModal');
    if (modalEl) {
        modalEl.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
})();

// Load assignments when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadAssignments();
    updateManagerSummary();
    // React to filter changes
    const filterSelect = document.getElementById('assigned_user_filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', applyAssignmentsFilter);
    }

    // Bulk block wiring
    const bulkGroup = document.getElementById('bulk_group_id');
    const bulkUser = document.getElementById('bulk_user_id');
    const bulkCount = document.getElementById('bulk_count');
    const bulkBtn = document.getElementById('bulk_assign_btn');
    const hint = document.getElementById('bulk_count_hint');

    function refreshStats(groupId) {
        if (!groupId) {
            document.getElementById('freeCount').textContent = '-';
            document.getElementById('assignedCount').textContent = '-';
            bulkUser.innerHTML = '<option value="">{{ __('app.select_group_first') }}</option>';
            bulkUser.disabled = true;
            bulkCount.value = '';
            bulkCount.disabled = true;
            bulkBtn.disabled = true;
            hint.textContent = '';
            return;
        }
        fetch(`/device-assignments/free-stats/${groupId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                document.getElementById('freeCount').textContent = data.free;
                document.getElementById('assignedCount').textContent = data.assigned;
                hint.textContent = `{{ __('app.max_free_devices') }}: ${data.free}`;
                bulkCount.max = String(data.free || 0);
                bulkCount.disabled = !(data.free > 0);
                bulkBtn.disabled = true;
            });

        // Populate members for selected group
        fetch(`/device-assignments/group-users/${groupId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const users = data.users || [];
                let html = '<option value="">{{ __('app.select_user') }}</option>';
                users.forEach(u => { html += `<option value="${u.id}">${u.name} (${u.email})</option>`; });
                bulkUser.innerHTML = html;
                bulkUser.disabled = users.length === 0;
            });
    }

    bulkGroup && bulkGroup.addEventListener('change', e => refreshStats(e.target.value));
    bulkUser && bulkUser.addEventListener('change', () => {
        bulkBtn.disabled = !(bulkGroup.value && bulkUser.value && Number(bulkCount.value) > 0);
    });
    bulkCount && bulkCount.addEventListener('input', () => {
        const max = Number(bulkCount.max || 0);
        let val = Number(bulkCount.value || 0);
        if (val > max) { val = max; bulkCount.value = String(max); }
        bulkBtn.disabled = !(bulkGroup.value && bulkUser.value && val > 0);
    });
    bulkBtn && bulkBtn.addEventListener('click', () => {
        const payload = new FormData();
        payload.append('group_id', bulkGroup.value);
        payload.append('user_id', bulkUser.value);
        payload.append('count', bulkCount.value);
        fetch('/device-assignments/assign-free', {
            method: 'POST',
            body: payload,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.message || '{{ __('app.assigned_successfully') }}');
                    bulkCount.value = '';
                    refreshStats(bulkGroup.value);
                    loadAssignments();
                    updateManagerSummary();
                } else {
                    showMessage('error', data.message || 'Error');
                }
            })
            .catch(() => showMessage('error', 'Error'));
    });

    // Initialize from the main group selection if present
    const mainGroup = document.getElementById('group_id');
    if (mainGroup && mainGroup.value && Array.from(bulkGroup.options).some(o => o.value == mainGroup.value)) {
        bulkGroup.value = mainGroup.value;
        refreshStats(mainGroup.value);
    }
});

// Message display functions
function showMessage(type, text) {
    const messageArea = document.getElementById('messageArea');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successText = document.getElementById('successText');
    const errorText = document.getElementById('errorText');
    
    // Hide all messages first
    successMessage.classList.add('hidden');
    errorMessage.classList.add('hidden');
    
    // Show the appropriate message
    if (type === 'success') {
        successText.textContent = text;
        successMessage.classList.remove('hidden');
        messageArea.classList.remove('hidden');
    } else if (type === 'error') {
        errorText.textContent = text;
        errorMessage.classList.remove('hidden');
        messageArea.classList.remove('hidden');
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageArea.classList.add('hidden');
    }, 5000);
}

function hideMessages() {
    document.getElementById('messageArea').classList.add('hidden');
}

function updateManagerSummary() {
    const managerGroups = @json($managerGroups);
    document.getElementById('totalGroups').textContent = managerGroups.length;
    // totalDevices left as placeholder unless later aggregated
    // myDevices is updated by loadMyAssignments()
}

// Filter rows in the assignments table by selected group and assigned user
function applyAssignmentsFilter() {
    const groupSelect = document.getElementById('group_id');
    const selectedGroupId = groupSelect ? groupSelect.value : '';
    const userFilter = document.getElementById('assigned_user_filter');
    const selectedUserId = userFilter && !userFilter.disabled ? userFilter.value : '';

    const tbody = document.getElementById('assignmentsTable');
    if (!tbody) return;
    Array.from(tbody.querySelectorAll('tr')).forEach(row => {
        const rowGroupId = row.getAttribute('data-group-id') || '';
        const rowUserId = row.getAttribute('data-user-id') || '';
        let visible = true;
        if (selectedGroupId) {
            visible = visible && (rowGroupId == selectedGroupId);
        }
        if (selectedUserId) {
            visible = visible && (rowUserId == selectedUserId);
        }
        row.style.display = visible ? '' : 'none';
    });
}

// (deduped) DOM ready handled above
// Invites handling
function generateInviteForSelected() {
    const groupId = document.getElementById('group_id').value;
    if (!groupId) {
        showMessage('error', '{{ __('app.select_group_first') }}');
        return;
    }
    generateInvite(groupId).then(() => {
        const input = document.getElementById('selectedInviteInput');
        const perGroupInput = document.getElementById(`inviteInput-${groupId}`);
        if (perGroupInput && perGroupInput.value) {
            input.value = perGroupInput.value;
        }
        // Disable selected generate button
        const selectedBtn = document.getElementById('selectedGenerateBtn');
        if (selectedBtn) {
            selectedBtn.disabled = true;
            selectedBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    });
}

function copySelectedInvite() {
    const input = document.getElementById('selectedInviteInput');
    if (!input.value) {
        showMessage('error', '{{ __('app.generate_invite_first') }}');
        return;
    }
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    showMessage('success', '{{ __('app.copied_to_clipboard') }}');
}
async function generateInvite(groupId) {
    try {
        const response = await fetch(`/device-assignments/${groupId}/invites`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        if (data.success) {
            const box = document.getElementById(`inviteBox-${groupId}`);
            const input = document.getElementById(`inviteInput-${groupId}`);
            input.value = data.invite_url;
            box.classList.remove('hidden');
            // Disable generate button if invite exists
            const btn = document.getElementById(`generateBtn-${groupId}`);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            showMessage('success', '{{ __('app.invite_link_generated') }}');
        } else {
            showMessage('error', data.message || 'Failed to generate invite');
        }
    } catch (e) {
        showMessage('error', 'Failed to generate invite');
    }
}

function copyInvite(groupId) {
    const input = document.getElementById(`inviteInput-${groupId}`);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    showMessage('success', '{{ __('app.copied_to_clipboard') }}');
}
</script>
</x-app-layout>
