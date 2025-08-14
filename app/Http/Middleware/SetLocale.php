<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\LanguageController;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from session first (user's explicit choice)
        $locale = LanguageController::getCurrentLocale();
        
        // If no explicit choice, detect from HTTP header
        if ($locale === null) {
            $locale = $this->detectLanguageFromHeader($request);
            // Store the detected language in session
            session(['locale' => $locale]);
        }
        
        // Set application locale
        app()->setLocale($locale);
        
        return $next($request);
    }
    
    /**
     * Detect language from Accept-Language HTTP header
     * Returns 'ru' for Russian, 'en' for all other languages
     */
    private function detectLanguageFromHeader(Request $request): string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return 'en';
        }
        
        // Parse Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $lang = trim(explode(';', $part)[0]);
            $languages[] = strtolower(substr($lang, 0, 2));
        }
        
        // Check if Russian is in the list
        if (in_array('ru', $languages)) {
            return 'ru';
        }
        
        // Default to English for all other languages
        return 'en';
    }
}
