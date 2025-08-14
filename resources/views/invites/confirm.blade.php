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
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            @if(!$isValid)
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('app.invite_invalid_title') }}</h1>
                    <p class="text-gray-600">{{ __('app.invite_invalid_text') }}</p>
                </div>
            @else
                <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ __('app.join_group_question') }}</h1>
                <p class="text-gray-700 mb-6">
                    {{ __('app.join_group_prompt', ['group' => $group->name, 'manager' => $manager->name]) }}
                </p>

                <div class="flex justify-center gap-4">
                    <form method="POST" action="{{ route('group-invites.accept', ['token' => $invite->token]) }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ __('app.yes_join') }}
                        </button>
                    </form>
                    <a href="{{ route('dashboard') }}" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-md hover:bg-gray-300">
                        {{ __('app.no_go_back') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>


