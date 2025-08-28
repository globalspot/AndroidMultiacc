<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">{{ __('app.apk_management') }}</h2>
            <div class="flex items-center space-x-4">
                <x-language-switcher />
                <x-user-menu />
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-800">{{ __('app.overview') }}</h3>
                        @if (session('status'))
                            <div class="text-green-600">{{ session('status') }}</div>
                        @endif
                    </div>

                    @if(empty($groups))
                        <div class="text-gray-600">{{ __('app.no_apks_found') }}</div>
                    @else
                        <div class="space-y-4">
                            @foreach($groups as $groupName => $files)
                                <div x-data="{ open: false }" class="border border-gray-200 rounded-md overflow-hidden">
                                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 hover:bg-gray-100">
                                        <div class="flex items-center space-x-2">
                                            @if(!empty($groupIcons[$groupName]))
                                                <img src="{{ $groupIcons[$groupName] }}" alt="icon" class="h-6 w-6 rounded" />
                                            @endif
                                            <h4 class="text-md font-medium text-gray-800">{{ $groupName }}</h4>
                                        </div>
                                        <svg :class="{'transform rotate-180': open}" class="h-5 w-5 text-gray-500 transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                                    </button>
                                    <div x-show="open" x-transition.origin.top.duration.200ms x-cloak class="px-4 pb-4">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.filename') }}</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.version') }}</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('app.added') }}</th>
                                                        <th class="px-4 py-2"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($files as $file)
                                                        <tr>
                                                            <td class="px-4 py-2 whitespace-nowrap">
                                                                <a href="{{ $file['url'] }}" class="text-blue-600 hover:underline" target="_blank">{{ $file['filename'] }}</a>
                                                            </td>
                                                            <td class="px-4 py-2 whitespace-nowrap">{{ $file['version'] ?? '-' }}</td>
                                                            <td class="px-4 py-2 whitespace-nowrap">{{ $file['add_date'] ? \Carbon\Carbon::parse($file['add_date'])->toDayDateTimeString() : '-' }}</td>
                                                            <td class="px-4 py-2 text-right">
                                                                @if($file['enabled'])
                                                                    <form method="POST" action="{{ route('admin.apks.disable') }}" class="inline">
                                                                        @csrf
                                                                        <input type="hidden" name="app_name" value="{{ $groupName }}">
                                                                        <input type="hidden" name="filename" value="{{ $file['filename'] }}">
                                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700 text-sm">{{ __('app.disable') }}</button>
                                                                    </form>
                                                                @elseif(empty($availableLibs[$groupName] ?? []))
                                                                    <form method="POST" action="{{ route('admin.apks.enable') }}" class="inline">
                                                                        @csrf
                                                                        <input type="hidden" name="app_name" value="{{ $groupName }}">
                                                                        <input type="hidden" name="filename" value="{{ $file['filename'] }}">
                                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm">{{ __('app.enable') }}</button>
                                                                    </form>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $libsForGroup = $availableLibs[$groupName] ?? [];
                                                        @endphp
                                                        @if(!empty($libsForGroup))
                                                        <tr>
                                                            <td colspan="4" class="px-4 pb-3">
                                                                <form method="POST" action="{{ route('admin.apks.enable') }}" class="flex flex-wrap items-center gap-3">
                                                                    @csrf
                                                                    <input type="hidden" name="app_name" value="{{ $groupName }}">
                                                                    <input type="hidden" name="filename" value="{{ $file['filename'] }}">
                                                                    <select name="lib_filename" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                                        <option value="">{{ __('app.none') }}</option>
                                                                        @foreach($libsForGroup as $lib)
                                                                            <option value="{{ $lib['value'] }}" {{ ($file['lib_filename'] ?? '') === $lib['value'] ? 'selected' : '' }}>{{ $lib['label'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="lib_install_order" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                                        <option value="before" {{ ($file['lib_install_order'] ?? '') === 'before' ? 'selected' : '' }}>{{ __('app.before') }}</option>
                                                                        <option value="after" {{ ($file['lib_install_order'] ?? '') === 'after' ? 'selected' : '' }}>{{ __('app.after') }}</option>
                                                                    </select>
                                                                    <label class="text-sm text-gray-600 inline-flex items-center space-x-2">
                                                                        <input type="checkbox" name="offline_required" value="1" {{ !empty($file['offline_required']) ? 'checked' : '' }}>
                                                                        <span>{{ __('app.offline_required') }}</span>
                                                                    </label>
                                                                    @if(!$file['enabled'])
                                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm">{{ __('app.enable') }}</button>
                                                                    @else
                                                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">{{ __('app.update') }}</button>
                                                                    @endif
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


