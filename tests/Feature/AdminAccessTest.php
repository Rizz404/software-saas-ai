<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Consultation;
use App\Models\ConsultationSlot;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\HandoverRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Company $otherCompany;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->company = Company::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $this->otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'public_status' => 'published']);

        $this->staff = User::factory()->create();
        $this->company->users()->attach($this->staff->id, ['role' => 'owner', 'status' => 'active']);
    }

    private function foreignConsultation(): Consultation
    {
        $area = $this->otherCompany->consultants()->create(['name' => 'Foreign', 'slug' => 'foreign', 'specialty' => 'solution', 'is_active' => true]);

        return Consultation::create([
            'consultant_name_snapshot' => 'Demo Consultant', 'currency_snapshot' => 'IDR', 'duration_minutes' => 60,
            'reference' => 'IT-FOREIGN', 'company_id' => $this->otherCompany->id, 'consultant_id' => $area->id, 'client_name' => 'Foreign Guest',
            'consultation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '11:00', 'participants' => 2,
        ]);
    }

    private function foreignHandover(): HandoverRequest
    {
        $conversation = Conversation::create(['company_id' => $this->otherCompany->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        return HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'complaint', 'summary' => 'Foreign complaint']);
    }

    public function test_visitors_are_sent_to_the_login_page_for_every_admin_screen(): void
    {
        foreach (['admin.dashboard', 'admin.products.index', 'admin.consultants.index', 'admin.knowledge-items.index', 'admin.consultations.index', 'admin.handovers.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_staff_only_see_consultations_of_their_own_company(): void
    {
        $area = $this->company->consultants()->create(['name' => 'Lounge', 'slug' => 'lounge', 'specialty' => 'solution', 'is_active' => true]);
        Consultation::create([
            'consultant_name_snapshot' => 'Demo Consultant', 'currency_snapshot' => 'IDR', 'duration_minutes' => 60,
            'reference' => 'IT-MINE', 'company_id' => $this->company->id, 'consultant_id' => $area->id, 'client_name' => 'My Guest',
            'consultation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '13:00', 'participants' => 2,
        ]);
        $this->foreignConsultation();

        $this->actingAs($this->staff)->get(route('admin.consultations.index'))
            ->assertOk()
            ->assertSee('IT-MINE')
            ->assertDontSee('IT-FOREIGN');
    }

    public function test_staff_cannot_change_another_companies_consultation(): void
    {
        $consultation = $this->foreignConsultation();

        $this->actingAs($this->staff)
            ->patch(route('admin.consultations.status', $consultation), ['status' => 'confirmed'])
            ->assertNotFound();

        $this->assertSame(Consultation::STATUS_PENDING, $consultation->fresh()->status);
    }

    public function test_cancelling_a_consultation_releases_its_table(): void
    {
        $area = $this->company->consultants()->create(['name' => 'Lounge', 'slug' => 'lounge', 'specialty' => 'solution', 'is_active' => true]);
        $date = now()->addDays(3)->toDateString();
        ConsultationSlot::create(['consultant_id' => $area->id, 'consultation_date' => $date, 'time_slot' => '13:00', 'total_sessions' => 1, 'reserved_sessions' => 1]);
        $consultation = Consultation::create([
            'consultant_name_snapshot' => 'Demo Consultant', 'currency_snapshot' => 'IDR', 'duration_minutes' => 60,
            'reference' => 'IT-MINE', 'company_id' => $this->company->id, 'consultant_id' => $area->id, 'client_name' => 'My Guest',
            'consultation_date' => $date, 'time_slot' => '13:00', 'participants' => 2,
        ]);

        $this->actingAs($this->staff)
            ->patch(route('admin.consultations.status', $consultation), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame(Consultation::STATUS_CANCELLED, $consultation->fresh()->status);
        $this->assertSame(0, ConsultationSlot::firstOrFail()->reserved_sessions);
    }

    public function test_staff_cannot_open_or_answer_another_companies_handover(): void
    {
        $handover = $this->foreignHandover();

        $this->actingAs($this->staff)->get(route('admin.handovers.show', $handover))->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Hi'])->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertNotFound();
    }

    public function test_resolving_a_handover_hands_the_conversation_back_to_the_solution_consultant(): void
    {
        $conversation = Conversation::create(['company_id' => $this->company->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en', 'status' => Conversation::STATUS_HANDED_OVER]);
        $handover = HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'scope_review', 'summary' => 'Birthday for 20']);

        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Happy to help!'])->assertRedirect();
        $this->assertSame('Happy to help!', ConversationMessage::where('role', ConversationMessage::ROLE_STAFF)->firstOrFail()->content);

        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertRedirect(route('admin.handovers.index'));

        $this->assertSame(HandoverRequest::STATUS_RESOLVED, $handover->fresh()->status);
        $this->assertSame(Conversation::STATUS_ACTIVE, $conversation->fresh()->status);
    }

    public function test_staff_manage_the_products_of_their_own_company_only(): void
    {
        $this->actingAs($this->staff)->post(route('admin.products.store'), [
            'category' => 'web', 'name' => 'Flat White', 'price' => 34000, 'tags' => 'Managed, Secure', 'prerequisites' => 'api_access', 'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $item = $this->company->products()->firstOrFail();
        $this->assertSame('flat-white', $item->slug);
        $this->assertSame(['managed', 'secure'], $item->tags);
        $this->assertTrue($item->is_active);

        $foreign = $this->otherCompany->products()->create(['category' => 'web', 'name' => 'Foreign', 'slug' => 'foreign', 'price' => 1]);

        $this->actingAs($this->staff)->get(route('admin.products.edit', $foreign))->assertNotFound();
        $this->actingAs($this->staff)->delete(route('admin.products.destroy', $foreign))->assertNotFound();
        $this->assertNotNull($foreign->fresh());
    }

    public function test_consultant_capacity_is_validated(): void
    {
        $this->actingAs($this->staff)->post(route('admin.consultants.store'), [
            'name' => 'Patio', 'specialty' => 'cloud', 'min_participants' => 6, 'max_participants' => 2, 'consultation_fee' => 0,
        ])->assertSessionHasErrors('max_participants');

        $this->actingAs($this->staff)->post(route('admin.consultants.store'), [
            'name' => 'Patio', 'specialty' => 'cloud', 'min_participants' => 1, 'max_participants' => 6, 'consultation_fee' => 0, 'features' => 'Video Call, discovery', 'is_active' => '1',
        ])->assertRedirect(route('admin.consultants.index'));

        $this->assertSame(['video_call', 'discovery'], $this->company->consultants()->firstOrFail()->features);
    }

    public function test_the_dashboard_counts_only_the_staffs_own_company(): void
    {
        $this->foreignHandover();
        $this->foreignConsultation();

        $this->actingAs($this->staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['open_handovers'] === 0 && $stats['pending_consultations'] === 0);
    }

    public function test_an_account_without_a_company_is_turned_away(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }
}
