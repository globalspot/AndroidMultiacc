<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Http\Controllers\LanguageController;

class LanguageSwitcherForm extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $currentLocale = LanguageController::getCurrentLocale();
        $availableLocales = LanguageController::getAvailableLocales();
        
        // If no locale is set, use the current application locale
        if ($currentLocale === null) {
            $currentLocale = app()->getLocale();
        }
        
        return view('components.language-switcher-form', [
            'currentLocale' => $currentLocale,
            'availableLocales' => $availableLocales,
        ]);
    }
}
