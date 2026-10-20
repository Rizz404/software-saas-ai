<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Consultant;
use App\Models\ConsultationSlot;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyStageTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Product $web;

    private Consultant $terrace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->company = Company::create([
            'name' => 'Demo Company', 'slug' => 'demo', 'public_status' => 'published', 'city' => 'Jakarta', 'country' => 'Indonesia', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '08:00', 'closing_time' => '22:00', 'phone' => '+62 21 1234', 'whatsapp' => '6281234567890',
            'description' => 'Perusahaan IT di Jakarta.',
        ]);

        $this->web = $this->company->products()->create([
            'name' => 'Web Platform', 'slug' => 'web-platform', 'category' => 'web', 'price' => 32000, 'description' => 'Platform web untuk bisnis.',
            'translations' => ['en' => ['name' => 'Web Platform', 'description' => 'A web platform for business.']], 'tags' => ['secure', 'scalable'], 'prerequisites' => ['api_access'], 'delivery_model' => 'remote',
            'image_url' => 'images/scenes/lab.png', 'is_active' => true, 'sort_order' => 0,
        ]);
        $this->company->products()->create(['name' => 'Managed Support', 'slug' => 'managed-support', 'category' => 'support', 'price' => 40000, 'is_unavailable' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->company->products()->create(['name' => 'Retired Service', 'slug' => 'retired', 'category' => 'web', 'price' => 1000, 'is_active' => false]);

        $this->terrace = $this->company->consultants()->create([
            'name' => 'Cloud Consultant', 'slug' => 'cloud-consultant', 'specialty' => 'cloud', 'min_participants' => 1, 'max_participants' => 6, 'features' => ['video_call'],
            'consultation_fee' => 0, 'is_active' => true,
        ]);
        $this->terrace->images()->create(['image_url' => 'images/scenes/meeting.png', 'alt_text' => 'Terrace', 'sort_order' => 0]);
        ConsultationSlot::create([
            'consultant_id' => $this->terrace->id, 'consultation_date' => CarbonImmutable::now('Asia/Jakarta')->addDays(3)->toDateString(), 'time_slot' => '13:00',
            'total_sessions' => 1, 'reserved_sessions' => 0,
        ]);

        $this->company->knowledgeItems()->create(['category' => 'facilities', 'title' => 'Integrasi API', 'body' => 'Integrasi API untuk kebutuhan bisnis.', 'tags' => ['wifi'], 'is_active' => true]);
        $this->company->knowledgeItems()->create(['category' => 'policies', 'title' => 'Kebijakan konsultasi', 'body' => 'Pembatalan melepaskan kapasitas konsultasi.', 'is_active' => true]);
        $this->company->knowledgeItems()->create(['category' => 'faq', 'title' => 'Bagaimana memulai proyek?', 'body' => 'Mulai dengan konsultasi kebutuhan.', 'is_active' => true]);
    }

    public function test_the_opening_screen_lists_published_companies(): void
    {
        Company::create(['name' => 'Draft Company', 'slug' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Company')
            ->assertDontSee('Draft Company')
            ->assertSee(route('company.products', ['companySlug' => 'demo', 'lang' => 'id']), false);
    }

    public function test_the_counter_greets_participants_in_the_requested_language(): void
    {
        $this->get('/demo')->assertOk()->assertSee('Demo Company')->assertSee('Selamat datang di');
        $this->get('/demo?lang=en')->assertOk()->assertSee('Welcome to')->assertSee('lang="en"', false);
        $this->get('/demo?lang=ja')->assertOk()->assertSee('ようこそ');
        $this->get('/demo?lang=xx')->assertOk()->assertSee('Selamat datang di');
    }

    public function test_every_scene_renders_and_carries_the_solution_consultant_chat(): void
    {
        $routes = [
            'company.show', 'company.products', 'company.consultants', 'company.facilities', 'company.info', 'company.staff', 'company.consultation',
        ];

        foreach ($routes as $route) {
            $this->get(route($route, ['companySlug' => 'demo']))
                ->assertOk()
                ->assertSee('id="solution_consultant-app"', false)
                ->assertSee('data-scene=', false);
        }
    }

    public function test_unpublished_and_unknown_companies_are_not_found(): void
    {
        Company::create(['name' => 'Draft', 'slug' => 'draft']);

        $this->get('/draft')->assertNotFound();
        $this->get('/draft/products')->assertNotFound();
        $this->get('/nowhere')->assertNotFound();
    }

    public function test_the_products_scene_narrates_from_stored_data_and_lists_active_items(): void
    {
        $this->get('/demo/products?lang=en')
            ->assertOk()
            ->assertSee('Explore 2 SaaS products with estimates from IDR 32.000')
            ->assertSee('Web Platform')
            ->assertSee('Managed Support')
            ->assertDontSee('Retired Service');
    }

    public function test_a_product_shows_its_price_prerequisites_and_tags(): void
    {
        $this->get('/demo/products/web-platform?lang=en')
            ->assertOk()
            ->assertSee('Web Platform')
            ->assertSee('IDR 32.000')
            ->assertSee('A web platform for business.')
            ->assertSee('API access')
            ->assertSee('Scalable')
            ->assertSee('Prerequisites: API access.')
            ->assertSee('images/scenes/lab.png', false);

        $this->get('/demo/products/managed-support?lang=en')->assertOk()->assertSee('Temporarily unavailable');

        $this->get('/demo/products/retired')->assertNotFound();
        $this->get('/demo/products/nothing-here')->assertNotFound();
    }

    public function test_the_consultants_scenes_show_capacity_and_fees(): void
    {
        $this->get('/demo/consultants?lang=en')->assertOk()->assertSee('Cloud Consultant')->assertSee('Discuss your needs with Cloud Consultant in a 60-minute session.');

        $this->get('/demo/consultants/cloud-consultant?lang=en')
            ->assertOk()
            ->assertSee('Cloud Consultant')
            ->assertSee('Video call')
            ->assertSee('No fee for the initial consultation.')
            ->assertSee('area=cloud-consultant', false);

        $this->get('/demo/consultants/nowhere')->assertNotFound();
    }

    public function test_the_facility_scenes_show_stored_products(): void
    {
        $facility = $this->company->knowledgeItems()->where('category', 'facilities')->firstOrFail();

        $this->get('/demo/facilities')->assertOk()->assertSee('Integrasi API')->assertDontSee('Kebijakan konsultasi');
        $this->get('/demo/facilities/'.$facility->id)->assertOk()->assertSee('Integrasi API untuk kebutuhan bisnis.');
        $this->get('/demo/facilities/99999')->assertNotFound();
    }

    public function test_the_info_scene_shows_opening_hours_policies_and_faq(): void
    {
        $this->get('/demo/info?lang=en')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('22:00')
            ->assertSee('We are open from 08:00 to 22:00.')
            ->assertSee('Kebijakan konsultasi')
            ->assertSee('Bagaimana memulai proyek?')
            ->assertSee('Perusahaan IT di Jakarta.');
    }

    public function test_the_staff_scene_offers_whatsapp_and_phone(): void
    {
        $this->get('/demo/staff')
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+62211234', false);
    }

    public function test_the_consultation_scene_offers_slots_and_consultants(): void
    {
        $this->get('/demo/consultation?area=cloud-consultant&lang=en')
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('data-preselect-area="cloud-consultant"', false)
            ->assertSee('13:00–14:00')
            ->assertSee('Cloud Consultant');
    }
}
