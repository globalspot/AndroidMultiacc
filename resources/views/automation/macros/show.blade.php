<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ $macro->name }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('automation.macros.edit', $macro) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="las la-edit la-sm mr-2"></i>
                    {{ __('app.edit') }}
                </a>
                <a href="{{ route('automation.macros.index') }}" class="text-gray-300 hover:text-white">
                    <i class="las la-arrow-left la-lg"></i>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Macro Header -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $macro->name }}</h3>
                            @if($macro->description)
                                <p class="text-gray-600 mb-4">{{ $macro->description }}</p>
                            @endif
                            <div class="flex items-center space-x-6 text-sm text-gray-500">
                                <span><i class="las la-calendar la-sm mr-1"></i>{{ __('app.created') }}: {{ $macro->created_at->format('M d, Y H:i') }}</span>
                                @if($macro->last_executed_at)
                                    <span><i class="las la-clock la-sm mr-1"></i>{{ __('app.last_run') }}: {{ $macro->last_executed_at->format('M d, Y H:i') }}</span>
                                @endif
                                <span><i class="las la-project-diagram la-sm mr-1"></i>{{ count($macro->nodes) }} {{ __('app.nodes') }}</span>
                                <span><i class="las la-link la-sm mr-1"></i>{{ count($macro->connections) }} {{ __('app.connections') }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col space-y-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full {{ $macro->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                <span class="text-sm {{ $macro->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ $macro->is_active ? __('app.active') : __('app.inactive') }}
                                </span>
                            </div>
                            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium">
                                <i class="las la-play la-sm mr-2"></i>
                                {{ __('app.execute_macro') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Macro Flow Visualization -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.macro_flow') }}</h3>
                            <div class="bg-gray-50 rounded-lg p-4 min-h-[400px] flex items-center justify-center">
                                <div class="text-center text-gray-500">
                                    <i class="las la-project-diagram la-3x mb-4"></i>
                                    <p>{{ __('app.flow_visualization_coming_soon') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Variables, Timers, etc. -->
                <div class="space-y-6">
                    <!-- Variables -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('app.variables') }}</h3>
                                <button class="text-blue-600 hover:text-blue-700 text-sm">
                                    <i class="las la-plus la-sm mr-1"></i>
                                    {{ __('app.add') }}
                                </button>
                            </div>
                            
                            @if($macro->variables->count() > 0)
                                <div class="space-y-3">
                                    @foreach($macro->variables as $variable)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $variable->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $variable->type }} 
                                                    @if($variable->default_value)
                                                        • {{ __('app.default') }}: {{ $variable->default_value }}
                                                    @endif
                                                </div>
                                            </div>
                                            <button class="text-gray-400 hover:text-red-600">
                                                <i class="las la-trash la-sm"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-500">
                                    <i class="las la-tag la-2x mb-2"></i>
                                    <p>{{ __('app.no_variables_defined') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Timers -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('app.timers') }}</h3>
                                <button class="text-blue-600 hover:text-blue-700 text-sm">
                                    <i class="las la-plus la-sm mr-1"></i>
                                    {{ __('app.add') }}
                                </button>
                            </div>
                            
                            @if($macro->timers->count() > 0)
                                <div class="space-y-3">
                                    @foreach($macro->timers as $timer)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $timer->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $timer->delay }} {{ $timer->unit }}
                                                    @if($timer->is_repeating)
                                                        • {{ __('app.repeating') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <button class="text-gray-400 hover:text-red-600">
                                                <i class="las la-trash la-sm"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-500">
                                    <i class="las la-clock la-2x mb-2"></i>
                                    <p>{{ __('app.no_timers_defined') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Execution History -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.execution_history') }}</h3>
                            <div class="text-center py-6 text-gray-500">
                                <i class="las la-history la-2x mb-2"></i>
                                <p>{{ __('app.no_executions_yet') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Macro Actions -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg mt-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.macro_actions') }}</h3>
                    <div class="flex flex-wrap gap-4">
                        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-md font-medium">
                            <i class="las la-play la-sm mr-2"></i>
                            {{ __('app.execute_now') }}
                        </button>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium">
                            <i class="las la-clock la-sm mr-2"></i>
                            {{ __('app.schedule_execution') }}
                        </button>
                        <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-md font-medium">
                            <i class="las la-copy la-sm mr-2"></i>
                            {{ __('app.duplicate_macro') }}
                        </button>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-medium">
                            <i class="las la-trash la-sm mr-2"></i>
                            {{ __('app.delete_macro') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

