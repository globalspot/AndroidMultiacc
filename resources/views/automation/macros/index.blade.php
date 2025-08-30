<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.automation_macros') }}
            </h2>
            <a href="{{ route('automation.macros.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                <i class="las la-plus la-sm mr-2"></i>
                {{ __('app.create_macro') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <i class="las la-cogs la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.total_macros') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $macros->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <i class="las la-play la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.active_macros') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $macros->where('is_active', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <i class="las la-clock la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.scheduled_macros') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $macros->where('last_executed_at', '!=', null)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <i class="las la-chart-line la-lg text-white"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('app.execution_rate') }}</p>
                                <p class="text-2xl font-semibold text-gray-900">98%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Macros List -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('app.your_macros') }}</h3>
                        <div class="flex space-x-2">
                            <button class="text-gray-500 hover:text-gray-700">
                                <i class="las la-filter la-lg"></i>
                            </button>
                            <button class="text-gray-500 hover:text-gray-700">
                                <i class="las la-sort la-lg"></i>
                            </button>
                        </div>
                    </div>

                    @if($macros->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($macros as $macro)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-3 h-3 rounded-full {{ $macro->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                            <h4 class="font-medium text-gray-900">{{ $macro->name }}</h4>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button class="text-gray-400 hover:text-blue-600" title="{{ __('app.edit') }}">
                                                <i class="las la-edit la-sm"></i>
                                            </button>
                                            <button class="text-gray-400 hover:text-red-600" title="{{ __('app.delete') }}">
                                                <i class="las la-trash la-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    @if($macro->description)
                                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($macro->description, 100) }}</p>
                                    @endif
                                    
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                        <span>{{ __('app.created') }}: {{ $macro->created_at->diffForHumans() }}</span>
                                        @if($macro->last_executed_at)
                                            <span>{{ __('app.last_run') }}: {{ $macro->last_executed_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 text-xs text-gray-500 mb-4">
                                        <span><i class="las la-tag la-sm mr-1"></i>{{ $macro->variables->count() }} {{ __('app.variables') }}</span>
                                        <span><i class="las la-clock la-sm mr-1"></i>{{ $macro->timers->count() }} {{ __('app.timers') }}</span>
                                        <span><i class="las la-project-diagram la-sm mr-1"></i>{{ count($macro->nodes) }} {{ __('app.nodes') }}</span>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('automation.macros.edit', $macro) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded text-sm font-medium">
                                            {{ __('app.edit') }}
                                        </a>
                                        <button class="flex-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium">
                                            {{ __('app.execute') }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="las la-cogs la-2x text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('app.no_macros_yet') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('app.create_your_first_macro') }}</p>
                            <a href="{{ route('automation.macros.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium">
                                {{ __('app.create_macro') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

