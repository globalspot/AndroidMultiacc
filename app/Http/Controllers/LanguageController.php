<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switchLanguage(Request $request, string $locale): RedirectResponse
    {
        // Validate locale
        $availableLocales = ['en', 'ru'];
        
        if (!in_array($locale, $availableLocales)) {
            $locale = 'en';
        }

        // Store locale in session
        session(['locale' => $locale]);

        // Redirect back to previous page
        return redirect()->back();
    }

    /**
     * Get current locale
     */
    public static function getCurrentLocale(): ?string
    {
        return session('locale');
    }

    /**
     * Get available locales
     */
    public static function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'ru' => 'Русский'
        ];
    }
}
