<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('app.automation_tasks') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-lg font-medium">{{ __('app.create_manage_tasks') }}</div>
                        <a href="/automation/action-types" class="text-blue-600 hover:underline">Catalog</a>
                    </div>
                    <p class="text-sm text-gray-600">This is the constructor authoring page. Use the API endpoints to create tasks and steps; UI authoring widgets can be added here later.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>




