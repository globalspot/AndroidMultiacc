<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\LanguageController;

class LanguageDetectionTest extends TestCase
{
    public function test_detects_russian_language_from_header()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8'
        ])->get('/');

        $this->assertEquals('ru', app()->getLocale());
        $this->assertEquals('ru', session('locale'));
    }

    public function test_detects_english_language_for_non_russian_headers()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8'
        ])->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }

    public function test_detects_english_language_for_french_header()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8'
        ])->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }

    public function test_detects_english_language_for_spanish_header()
    {
        $response = $this->withHeaders([
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8'
        ])->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }

    public function test_detects_english_language_when_no_header()
    {
        $response = $this->get('/');

        $this->assertEquals('en', app()->getLocale());
        $this->assertEquals('en', session('locale'));
    }

    public function test_preserves_user_selected_language()
    {
        // First, set a language explicitly
        $this->get(route('language.switch', 'ru'));
        $this->assertEquals('ru', session('locale'));

        // Then make a request with different Accept-Language header
        $response = $this->withHeaders([
            'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8'
        ])->get('/');

        // Should still use Russian as it was explicitly selected
        $this->assertEquals('ru', app()->getLocale());
        $this->assertEquals('ru', session('locale'));
    }

    public function test_language_controller_returns_null_when_no_locale_set()
    {
        // Clear session
        session()->flush();
        
        $this->assertNull(LanguageController::getCurrentLocale());
    }

    public function test_language_controller_returns_locale_when_set()
    {
        session(['locale' => 'ru']);
        
        $this->assertEquals('ru', LanguageController::getCurrentLocale());
    }
}
