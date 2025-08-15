<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.welcome') }}, {{ $user->name }}! ({{ __('app.' . $role) }})
        </h2>
            <div class="flex items-center space-x-4">
                <span class="px-3 py-1 text-xs font-medium rounded-full 
                    @if($isAdmin) bg-white bg-opacity-20 text-white border border-white border-opacity-30
                    @elseif($isManager) bg-white bg-opacity-20 text-white border border-white border-opacity-30
                    @else bg-white bg-opacity-20 text-white border border-white border-opacity-30
                    @endif">
                    {{ __('app.' . $role) }}
                </span>
                <x-language-switcher />
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Role-based Welcome Section -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg mb-8">
                <div class="px-6 py-8 text-white">
                    <h3 class="text-2xl font-bold mb-2">
                        @if($isAdmin)
                            🛡️ {{ __('app.admin_dashboard') }}
                        @elseif($isManager)
                            👨‍💼 {{ __('app.manager_dashboard') }}
                        @else
                            👤 {{ __('app.user_dashboard') }}
                        @endif
                    </h3>
                    <p class="text-blue-100">
                        @if($isAdmin)
                            {{ __('app.admin_description') }}
                        @elseif($isManager)
                            {{ __('app.manager_description') }}
                        @else
                            {{ __('app.user_description') }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" width="137px" height="137px" viewBox="0 0 1024 1024" fill="#ffffff" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff" stroke-width="37.888"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M962.4 1012.8s0 0.8 0 0h25.6-25.6zM704 338.4C704 195.2 588.8 78.4 445.6 78.4S187.2 195.2 187.2 338.4s116 260 258.4 260S704 481.6 704 338.4z m-472 0c0-118.4 96-214.4 213.6-214.4s213.6 96 213.6 214.4-96 214.4-213.6 214.4S232 456.8 232 338.4z" fill=""></path><path d="M456.8 621.6c196.8 0 361.6 136 394.4 324h45.6C863.2 732 677.6 576.8 456 576.8c-221.6 0-406.4 155.2-440.8 368.8h45.6C96 756.8 260 621.6 456.8 621.6z" fill=""></path><path d="M770.4 578.4l-24-8.8 20.8-14.4c65.6-46.4 104.8-122.4 103.2-202.4-1.6-128-102.4-232.8-228-241.6v47.2c100 8.8 180 92.8 180.8 194.4 0.8 52.8-19.2 102.4-56 140.8-36.8 37.6-86.4 59.2-139.2 60-24.8 0-50.4 0-75.2 1.6-15.2 1.6-41.6 0-54.4 9.6-1.6 0.8-3.2 0-4.8 0l-9.6 12c-0.8 1.6-2.4 3.2-4 4.8 0.8 1.6-0.8 16 0 17.6 12 4 71.2 0 156.8 2.4 179.2 1.6 326.4 160.8 340.8 338.4l47.2 3.2c-9.6-156-108-310.4-254.4-364.8z" fill=""></path></g></svg>
                            </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.total_accounts') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    @if($isAdmin) 1,247 @elseif($isManager) 156 @else 12 @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.active_tasks') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    @if($isAdmin) 89 @elseif($isManager) 23 @else 3 @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.performance') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    @if($isAdmin) 98% @elseif($isManager) 94% @else 87% @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Group Assignments: full-width row below the quick stats -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.active_group_assignments') }}</h3>
                    <span class="text-sm text-gray-500">{{ $activeGroupAssignments->count() }}</span>
                </div>
                <div class="p-6">
                    @if($activeGroupAssignments->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('app.no_active_group_assignments') }}</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach($activeGroupAssignments as $assignment)
                                <li class="py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $assignment->deviceGroup->name ?? __('app.group') . ' #' . $assignment->device_group_id }}</p>
                                        <p class="text-xs text-gray-500">{{ __('app.role') }}: {{ __('app.' . $assignment->role) }}</p>
                                    </div>
                                    @if($isManager)
                                        <a href="{{ route('device-assignments.index') }}" class="text-blue-600 text-sm hover:underline">{{ __('app.manage') }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Role-specific Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg lg:col-span-1">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('app.quick_actions') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @if($isAdmin)
                                <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.system_settings') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.configure_global_settings') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('devices.index') }}" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.all_devices') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.manage_all_users') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('user-assignments.index') }}" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.user_group_assignments') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.manage_user_assignments') }}</p>
                                    </div>
                                </a>
                                <a href="https://multiacc.cmd.rest/scrcpy.zip" target="_blank" rel="noopener" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-teal-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.download_connector') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.download_connector_description') }}</p>
                                    </div>
                                </a>
                            @elseif($isManager)
                                <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.team_reports') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.view_team_performance') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('devices.index') }}" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.devices') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.list_group_assigned_devices') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('device-assignments.index') }}" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.device_assignment_management') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.assign_devices_to_users') }}</p>
                                    </div>
                                </a>
                                <a href="https://multiacc.cmd.rest/scrcpy.zip" target="_blank" rel="noopener" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-teal-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.download_connector') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.download_connector_description') }}</p>
                                    </div>
                                </a>
                            @else
                                <a href="{{ route('devices.index') }}" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.my_devices') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.manage_android_accounts') }}</p>
                                    </div>
                                </a>
                                <a href="#" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.automation_tasks') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.create_manage_tasks') }}</p>
                                    </div>
                                </a>
                                <a href="https://multiacc.cmd.rest/scrcpy.zip" target="_blank" rel="noopener" class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-teal-100 rounded-md flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ __('app.download_connector') }}</p>
                                        <p class="text-sm text-gray-500">{{ __('app.download_connector_description') }}</p>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg lg:col-span-1">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('app.recent_activity') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ __('app.account_automation_completed') }}</p>
                                    <p class="text-xs text-gray-500">2 {{ __('app.minutes_ago') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ __('app.new_account_added') }}</p>
                                    <p class="text-xs text-gray-500">15 {{ __('app.minutes_ago') }}</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mt-2"></div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ __('app.task_scheduled') }}</p>
                                    <p class="text-xs text-gray-500">1 {{ __('app.hours_ago') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
