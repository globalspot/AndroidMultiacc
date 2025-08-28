<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-white hover:text-white hover:bg-white hover:bg-opacity-20 rounded-md transition-colors border border-white border-opacity-30">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 17h8a4 4 0 012.879 1.096M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>{{ auth()->user()->name ?? 'User' }}</span>
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open" @click.away="open = !open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl py-1 z-[9999] border border-gray-200">
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            {{ __('app.profile') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                {{ __('app.log_out') }}
            </button>
        </form>
    </div>
</div>

