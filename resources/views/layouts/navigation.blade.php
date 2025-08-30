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
                    <i class="las la-tachometer-alt la-lg {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.dashboard') }}</span>
                </a>
            </li>
        </ul>

        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('app.discover') }}</div>
        <ul class="px-2 space-y-1 mb-6">
            <li>
                <a href="{{ route('devices.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('devices.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-mobile-alt la-lg {{ request()->routeIs('devices.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.devices') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('apps.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('apps.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-th-large la-lg {{ request()->routeIs('apps.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.applications') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <i class="las la-book la-lg text-gray-400"></i>
                    <span class="ml-3">{{ __('app.library') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('automation.macros.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('automation.macros.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-cogs la-lg {{ request()->routeIs('automation.macros.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.automation') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <i class="las la-code la-lg text-gray-400"></i>
                    <span class="ml-3">{{ __('app.api') }}</span>
                </a>
            </li>
        </ul>

        <div class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('app.team') }}</div>
        <ul class="px-2 space-y-1">
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <i class="las la-credit-card la-lg text-gray-400"></i>
                    <span class="ml-3">{{ __('app.billing') }}</span>
                </a>
            </li>
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <i class="las la-users la-lg text-gray-400"></i>
                    <span class="ml-3">{{ __('app.members') }}</span>
                </a>
            </li>
            @if(Auth::check() && Auth::user()->role === 'manager')
            <li>
                <a href="{{ route('device-assignments.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('device-assignments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-tasks la-lg {{ request()->routeIs('device-assignments.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.device_assignment') }}</span>
                </a>
            </li>
            @endif
            @if(Auth::check() && Auth::user()->role === 'admin')
            <li>
                <a href="{{ route('user-assignments.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('user-assignments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-users-cog la-lg {{ request()->routeIs('user-assignments.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.user_administration') }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.apks.index') }}" class="flex items-center px-3 py-2 rounded-md {{ request()->routeIs('admin.apks.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="las la-box-open la-lg {{ request()->routeIs('admin.apks.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="ml-3">{{ __('app.app_edit') }}</span>
                </a>
            </li>
            @endif
            <li>
                <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-50">
                    <i class="las la-clipboard-list la-lg text-gray-400"></i>
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
