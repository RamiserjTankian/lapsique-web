<?php

namespace Tests\Feature;

use App\Filament\Pages\MetaAdsPerformanceDashboard;
use App\Filament\Pages\MetaAdsSettingsPage;
use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\PageContentBlocks\PageContentBlockResource;
use App\Filament\Resources\PortfolioItems\PortfolioItemResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Widgets\TrascendentalContentCoverageWidget;
use App\Models\Customer;
use App\Models\Dj;
use App\Models\Event;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrascendentalAdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('trascendental.enabled_as_primary', true);
        $this->actingAs(User::factory()->create());
    }

    public function test_customer_editor_preserves_every_operational_source(): void
    {
        $sources = [
            'trascendental_join_list',
            'trascendental_contact',
            'guestlist',
            'ticketing',
            'content_booking',
            'popup',
            'legacy_import',
        ];

        foreach ($sources as $index => $source) {
            $customer = Customer::query()->create([
                'name' => "Lead {$index}",
                'email' => "lead{$index}@example.com",
                'source' => $source,
                'status' => 'lead',
                'subscribed_newsletter' => true,
            ]);

            Livewire::test(EditCustomer::class, ['record' => $customer->getRouteKey()])
                ->fillForm(['notes' => 'CRUD auditado'])
                ->call('save')
                ->assertHasNoFormErrors();

            $this->assertDatabaseHas('customers', [
                'id' => $customer->id,
                'source' => $source,
                'notes' => 'CRUD auditado',
            ]);
        }
    }

    public function test_site_settings_use_a_single_data_state_and_save_without_losing_hidden_values(): void
    {
        SiteSetting::query()->create([
            'meta_pixel_id' => '111111111111111',
            'booking_title' => 'Valor heredado',
            'booking_og_image' => 'images/og/booking.webp',
            'djset_og_image' => 'images/og/djset.webp',
            'booking_price' => 4000,
            'home_hero_proof_1_source' => 'youtube',
            'home_hero_proof_1_reference' => 'abcdefghijk',
        ]);

        Livewire::test(SiteSettings::class)
            ->assertSet('data.meta_pixel_id', '111111111111111')
            ->fillForm(['meta_pixel_id' => '222222222222222'])
            ->call('save')
            ->assertSet('data.meta_pixel_id', '222222222222222')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_settings', [
            'meta_pixel_id' => '222222222222222',
            'booking_title' => 'Valor heredado',
            'booking_og_image' => 'images/og/booking.webp',
            'djset_og_image' => 'images/og/djset.webp',
            'home_hero_proof_1_source' => 'youtube',
            'home_hero_proof_1_reference' => 'abcdefghijk',
        ]);
    }

    public function test_meta_ads_settings_page_uses_the_registered_dashboard_url(): void
    {
        $this->get(MetaAdsSettingsPage::getUrl())
            ->assertOk()
            ->assertSee('Estado de la integración')
            ->assertSee(MetaAdsPerformanceDashboard::getUrl());
    }

    public function test_primary_site_hides_inherited_content_cruds_and_redirects_public_legacy_routes(): void
    {
        $this->assertFalse(PostResource::shouldRegisterNavigation());
        $this->assertFalse(VideoResource::shouldRegisterNavigation());
        $this->assertFalse(PortfolioItemResource::shouldRegisterNavigation());
        $this->assertFalse(PageContentBlockResource::shouldRegisterNavigation());

        $this->get('/djs')->assertRedirect('/tours-routing');
        $this->get('/dj-set')->assertRedirect('/tours-routing');
        $this->get('/portafolio')->assertRedirect('/casos');
        $this->get('/blog')->assertRedirect('/casos');
        $this->get('/videos')->assertRedirect('/casos');
    }

    public function test_primary_site_marks_customer_portal_for_trascendental_layout(): void
    {
        $this->get(route('customers.login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Customer/Login')
                ->where('site.isTrascendental', true)
            );
    }

    public function test_dashboard_exposes_only_the_content_that_reaches_trascendental_pages(): void
    {
        Event::query()->create([
            'title' => 'Evento público',
            'slug' => 'evento-publico',
            'trascendental_visible' => true,
            'is_case_study' => true,
        ]);
        Dj::query()->create([
            'name' => 'Artista público',
            'slug' => 'artista-publico',
            'trascendental_roster' => true,
        ]);
        Customer::query()->create([
            'name' => 'Lead público',
            'email' => 'publico@example.com',
            'source' => 'trascendental_contact',
        ]);

        Livewire::test(TrascendentalContentCoverageWidget::class)
            ->assertSee('Eventos públicos')
            ->assertSee('Artistas del roster')
            ->assertSee('Casos publicados')
            ->assertSee('Leads Trascendental');
    }
}
