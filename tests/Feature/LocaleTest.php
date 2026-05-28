<?php

namespace Tests\Feature;

use App\Support\LocaleResolver;
use App\Support\LocalizedBookingCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_spanish_from_accept_language(): void
    {
        $this->withHeaders(['Accept-Language' => 'es-MX,es;q=0.9,en;q=0.8'])
            ->get('/');

        $this->assertEquals('es', app()->getLocale());
    }

    public function test_resolves_english_from_accept_language(): void
    {
        $this->withHeaders(['Accept-Language' => 'en-US,en;q=0.9'])
            ->get('/');

        $this->assertEquals('en', app()->getLocale());
    }

    public function test_locale_switch_sets_session_and_cookie(): void
    {
        $response = $this->get('/locale/en');

        $response->assertRedirect();
        $response->assertCookie('locale', 'en');
        $this->assertEquals('en', session('locale'));
    }

    public function test_cookie_locale_overrides_accept_language(): void
    {
        $this->withCookie('locale', 'en')
            ->withHeaders(['Accept-Language' => 'es-MX,es;q=0.9'])
            ->get('/');

        $this->assertEquals('en', app()->getLocale());
    }

    public function test_inertia_shares_translations_for_current_locale(): void
    {
        $this->withHeaders(['Accept-Language' => 'en-US,en;q=0.9'])
            ->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'en')
                ->has('translations.common.nav.portfolio')
                ->where('translations.common.nav.portfolio', 'Portfolio'));
    }

    public function test_locale_resolver_normalizes_tags(): void
    {
        $this->assertSame('es', LocaleResolver::normalize('es-MX'));
        $this->assertSame('en', LocaleResolver::normalize('en-US'));
        $this->assertSame('es', LocaleResolver::fromAcceptLanguage('es-MX,en;q=0.8'));
        $this->assertSame('en', LocaleResolver::fromAcceptLanguage('en-US,es;q=0.5'));
    }

    public function test_booking_hero_title_uses_english_copy_when_locale_is_en(): void
    {
        app()->setLocale('en');

        $title = LocalizedBookingCopy::title('Reels cinematográficos para negocios');

        $this->assertSame('Cinematic reels for businesses', $title);
    }

    public function test_booking_hero_title_keeps_admin_copy_in_spanish(): void
    {
        app()->setLocale('es');

        $title = LocalizedBookingCopy::title('Mi título personalizado');

        $this->assertSame('Mi título personalizado', $title);
    }
}
