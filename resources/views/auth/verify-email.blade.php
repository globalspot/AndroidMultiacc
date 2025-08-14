<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('app.verify_email_description') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('app.verification_link_sent') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('app.resend_verification_email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('app.log_out') }}
            </button>
        </form>
    </div>

    <!-- Language Switcher -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex justify-center">
            <x-language-switcher-form />
        </div>
    </div>
</x-guest-layout>
