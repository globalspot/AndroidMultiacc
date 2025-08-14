<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors border border-gray-300 hover:border-gray-400">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
        </svg>
        <span class="text-gray-700">{{ $availableLocales[$currentLocale] ?? 'English' }}</span>
        <svg class="w-4 h-4 text-gray-600 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open" @click.away="open = !open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-1/2 transform -translate-x-1/2 mt-2 w-48 bg-white rounded-md shadow-xl py-1 z-[9999] border border-gray-200">
        @foreach($availableLocales as $locale => $name)
            <a href="{{ route('language.switch', $locale) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $locale === $currentLocale ? 'bg-indigo-50 text-indigo-700' : '' }}">
                <div class="flex items-center space-x-2">
                    <span class="w-4 h-4 rounded-full {{ $locale === 'en' ? 'bg-blue-500' : 'bg-red-500' }}"></span>
                    <span>{{ $name }}</span>
                    @if($locale === $currentLocale)
                        <svg class="w-4 h-4 ml-auto text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
