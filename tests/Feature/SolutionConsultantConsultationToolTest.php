<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\RunsSolutionConsultantTools;
use Tests\TestCase;

class SolutionConsultantConsultationToolTest extends TestCase
{
    use RefreshDatabase, RunsSolutionConsultantTools;

    public function test_ai_consultation_tool_creates_a_reference_and_holds_one_table(): void
    {
        $company = Company::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $area = $company->consultants()->create(['name' => 'Security Consultant', 'slug' => 'security-consultant', 'specialty' => 'security', 'min_participants' => 1, 'max_participants' => 6, 'consultation_fee' => 150000, 'is_active' => true]);
        $conversation = Conversation::create(['company_id' => $company->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $date = CarbonImmutable::now($company->timezone)->addDays(5);
        foreach (['11:00', '13:00'] as $slot) {
            ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => $slot, 'total_sessions' => 1, 'reserved_sessions' => 0]);
        }

        $input = [
            'consultant_slug' => 'security-consultant', 'date' => $date->toDateString(), 'time_slot' => '11:00',
            'participants' => 6, 'client_name' => 'Ayu', 'client_phone' => '+62 811 111 222',
        ];

        $quote = $this->runTool($company, $conversation, 'check_consultation_availability', $input);
        $this->assertTrue($quote['ui']['available']);
        $this->assertSame(150000.0, $quote['ui']['quote']['consultation_fee']);

        $result = $this->runTool($company, $conversation, 'create_consultation_request', $input);

        $consultation = Consultation::firstOrFail();
        $this->assertSame($consultation->reference, $result['ui']['consultation']['consultation_reference']);
        $this->assertSame(6, $consultation->participants);
        $this->assertSame('11:00', $consultation->time_slot);
        $this->assertSame(150000.0, (float) $consultation->consultation_fee_total);
        $this->assertSame([1, 0], ConsultationSlot::orderBy('time_slot')->pluck('reserved_sessions')->all());

        $repeated = $this->runTool($company, $conversation, 'create_consultation_request', $input);
        $this->assertSame($consultation->reference, $repeated['ui']['consultation']['consultation_reference']);
        $this->assertSame(1, Consultation::count());

        $taken = $this->runTool($company, $conversation, 'create_consultation_request', [...$input, 'client_name' => 'Budi']);
        $this->assertNull($taken['ui']);
        $this->assertSame(1, Consultation::count());

        $full = $this->runTool($company, $conversation, 'check_consultation_availability', $input);
        $this->assertFalse($full['ui']['available']);
        $this->assertStringContainsString('13:00', $full['text']);
    }

    public function test_the_tools_refuse_a_party_that_does_not_fit_and_a_slot_that_does_not_exist(): void
    {
        $company = Company::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $company->consultants()->create(['name' => 'Bar', 'slug' => 'ai', 'specialty' => 'ai', 'min_participants' => 1, 'max_participants' => 2, 'is_active' => true]);
        $conversation = Conversation::create(['company_id' => $company->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($company->timezone)->addDays(2);
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_sessions' => 1, 'reserved_sessions' => 0]);

        $base = ['consultant_slug' => 'ai', 'date' => $date->toDateString(), 'client_name' => 'Ayu', 'client_phone' => '+62 811 111 222'];

        $tooMany = $this->runTool($company, $conversation, 'create_consultation_request', [...$base, 'time_slot' => '15:00', 'participants' => 5]);
        $this->assertNull($tooMany['ui']);

        $unknownSlot = $this->runTool($company, $conversation, 'check_consultation_availability', [...$base, 'time_slot' => '03:00', 'participants' => 2]);
        $this->assertStringContainsString('15:00', $unknownSlot['text']);

        $past = $this->runTool($company, $conversation, 'check_consultation_availability', [...$base, 'date' => '2001-01-01', 'time_slot' => '15:00', 'participants' => 2]);
        $this->assertStringContainsString('past', $past['text']);

        $this->assertSame(0, Consultation::count());
    }

    public function test_the_consultation_tool_needs_a_usable_name_and_contact_before_it_books(): void
    {
        $company = Company::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $company->consultants()->create(['name' => 'Bar', 'slug' => 'ai', 'specialty' => 'ai', 'min_participants' => 1, 'max_participants' => 4, 'is_active' => true]);
        $conversation = Conversation::create(['company_id' => $company->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($company->timezone)->addDays(2);
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date->toDateString(), 'time_slot' => '15:00', 'total_sessions' => 1, 'reserved_sessions' => 0]);

        $base = ['consultant_slug' => 'ai', 'date' => $date->toDateString(), 'time_slot' => '15:00', 'participants' => 2];

        foreach ([
            ['client_phone' => '+62 811 111 222'],
            ['client_name' => 'Ayu'],
            ['client_name' => 'Ayu', 'client_phone' => 'call me'],
            ['client_name' => 'Ayu', 'client_email' => 'not-an-email'],
        ] as $guest) {
            $this->assertNull($this->runTool($company, $conversation, 'create_consultation_request', [...$base, ...$guest])['ui']);
        }

        $this->assertSame(0, Consultation::count());

        $byEmail = $this->runTool($company, $conversation, 'create_consultation_request', [...$base, 'client_name' => 'Ayu', 'client_email' => 'ayu@example.com']);

        $this->assertNotNull($byEmail['ui']);
        $this->assertSame('email', Consultation::firstOrFail()->contact_type);
    }

    public function test_products_search_filters_by_diet_and_budget_and_hides_unpublished_items(): void
    {
        $company = Company::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $company->products()->create(['name' => 'Cloud Foundation', 'slug' => 'cloud-foundation', 'category' => 'cloud', 'price' => 48000, 'tags' => ['automation'], 'is_active' => true]);
        $company->products()->create(['name' => 'Managed Cloud', 'slug' => 'managed-cloud', 'category' => 'cloud', 'price' => 52000, 'tags' => ['managed'], 'is_active' => true]);
        $company->products()->create(['name' => 'Hidden Service', 'slug' => 'secret', 'category' => 'cloud', 'price' => 10000, 'tags' => ['automation'], 'is_active' => false]);
        $conversation = Conversation::create(['company_id' => $company->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $vegan = $this->runTool($company, $conversation, 'search_products', ['solution' => 'automation']);
        $this->assertSame(['cloud-foundation'], collect($vegan['ui']['items'])->pluck('product_slug')->all());

        $cheap = $this->runTool($company, $conversation, 'search_products', ['category' => 'cloud', 'max_price' => 50000]);
        $this->assertSame(['cloud-foundation'], collect($cheap['ui']['items'])->pluck('product_slug')->all());

        $none = $this->runTool($company, $conversation, 'search_products', ['query' => 'sushi']);
        $this->assertNull($none['ui']);
    }
}
