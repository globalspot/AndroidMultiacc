<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('app.group_invite') }}
            </h2>
            <x-language-switcher />
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('app.joined_group_title') }}</h1>
            <p class="text-gray-700 mb-6">{{ __('app.joined_group_text', ['group' => $group->name]) }}</p>
            <a href="{{ route('device-assignments.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ __('app.go_to_assignments') }}
            </a>
        </div>
    </div>
</x-app-layout>


