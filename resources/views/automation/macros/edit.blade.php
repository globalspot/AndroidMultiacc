<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.edit_automation_macro') }}: {{ $macro->name }}
            </h2>
            <a href="{{ route('automation.macros.index') }}" class="text-gray-300 hover:text-white">
                <i class="las la-arrow-left la-lg"></i>
            </a>
        </div>
    </x-slot>

    <div class="h-screen bg-gray-50">
        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 px-6 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.actions') }}</h3>
                    <button class="text-gray-400 hover:text-gray-600" title="{{ __('app.help') }}">
                        <i class="las la-question-circle la-lg"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600" title="{{ __('app.pin') }}">
                        <i class="las la-thumbtack la-lg"></i>
                    </button>
                    <button class="text-gray-400 hover:text-gray-600" title="{{ __('app.close') }}">
                        <i class="las la-times la-lg"></i>
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <input type="text" value="{{ $macro->name }}" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        {{ __('app.save') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="h-full relative">
            <!-- ReactFlow Editor -->
            <div id="react-macro-editor" 
                 class="w-full h-full" 
                 data-macro='@json($macro)' 
                 data-nodes='@json($macro->nodes)' 
                 data-connections='@json($macro->connections)' 
                 data-variables='@json($macro->variables)' 
                 data-timers='@json($macro->timers)'></div>
        </div>
    </div>

    @vite('resources/js/macro-editor.jsx')
</x-app-layout>
