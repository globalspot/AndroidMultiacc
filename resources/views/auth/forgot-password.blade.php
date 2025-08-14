<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('app.forgot_password_description') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('app.email_address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('app.email_password_reset_link') }}
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
