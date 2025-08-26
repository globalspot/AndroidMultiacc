<nav class="h-full flex flex-col">
    <!-- Brand -->
    <div class="h-16 px-4 border-b border-gray-200 flex items-center">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <x-application-logo class="block h-8 w-auto fill-current text-blue-600" />
            <span class="font-semibold text-gray-800">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                </div>

    <!-- Sections -->
    <div class="flex-1 overflow-y-auto py-4">
        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('app.primary') }}</div>
        <ul class="px-2 space-y-1 mb-6">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="ml-3">{{ __('app.dashboard') }}</span>
                </a>
            </li>
        </ul>

        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('app.discover') }}</div>
        <ul class="px-2 space-y-1 mb-6">
            <li>
                <a href="{{ route('devices.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('devices.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('devices.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h10zm4 10h2a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
                    <span class="ml-3">{{ __('app.devices') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2zm0 0v12m-7-6a7 7 0 1114 0 7 7 0 01-14 0z"/></svg>
                    <span class="ml-3">{{ __('app.applications') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8"/></svg>
                    <span class="ml-3">{{ __('app.library') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l3 10 4-18 3 8h4"/></svg>
                    <span class="ml-3">{{ __('app.automation') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                    <span class="ml-3">{{ __('app.api') }}</span>
                </a>
            </li>
        </ul>

        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('app.team') }}</div>
        <ul class="px-2 space-y-1">
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4M7 7h10a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                    <span class="ml-3">{{ __('app.billing') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2"/></svg>
                    <span class="ml-3">{{ __('app.members') }}</span>
                </a>
            </li>
            @if(Auth::check() && Auth::user()->role === 'manager')
            <li>
                <a href="{{ route('device-assignments.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('device-assignments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('device-assignments.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 4a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="ml-3">{{ __('app.device_assignment') }}</span>
                </a>
            </li>
            @endif
            @if(Auth::check() && Auth::user()->role === 'admin')
            <li>
                <a href="{{ route('user-assignments.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('user-assignments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('user-assignments.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2m5-11a3 3 0 110-6 3 3 0 010 6z"/></svg>
                    <span class="ml-3">{{ __('app.user_administration') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.apks.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('admin.apks.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('admin.apks.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v6m-3-3h6M8 8h8m-9 4h10M7 4h10a2 2 0 012 2v12"/></svg>
                    <span class="ml-3">{{ __('app.app_edit') }}</span>
                </a>
            </li>
            @endif
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                    <span class="ml-3">{{ __('app.logs') }}</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Footer (compact) -->
    <div class="px-4 py-3 border-t border-gray-200 text-xs text-gray-500">
        <div class="flex items-center justify-between">
            <span>{{ __('app.logged_in_as') }}</span>
            <span class="font-medium text-gray-700">{{ Auth::user()->name }}</span>
        </div>
    </div>
</nav>
