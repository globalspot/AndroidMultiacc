<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('app.secure_area_message') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('app.password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('app.confirm') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Language Switcher -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex justify-center">
            <x-language-switcher-form />
        </div>
    </div>
</x-guest-layout>
