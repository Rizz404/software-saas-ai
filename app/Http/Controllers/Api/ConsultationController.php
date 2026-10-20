<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveCompany;
use App\Http\Requests\Api\QuoteConsultationRequest;
use App\Http\Requests\Api\StoreConsultationRequest;
use App\Models\Company;
use App\Models\Consultant;
use App\Models\Conversation;
use App\Modules\Consultation\Actions\CreateConsultationRequest;
use App\Modules\Consultation\Actions\ResolveConsultationTarget;
use App\Modules\Consultation\Queries\FindAlternatives;
use App\Modules\Consultation\Support\ConsultationAvailability;
use App\Modules\Consultation\Support\ConsultationHandover;
use App\Modules\Consultation\Validation\ConsultationRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationAvailability $availability,
        private readonly ResolveConsultationTarget $resolveTarget,
        private readonly FindAlternatives $findAlternatives,
    ) {}

    public function quote(QuoteConsultationRequest $request): JsonResponse
    {
        $company = ResolveCompany::company($request);
        $locale = ResolveCompany::locale($request);

        $data = $request->validated();
        [$area, $date, $service] = $this->resolveTarget->handle($company, $data, $locale);

        $quote = $this->availability->quote($area, $date, $data['time_slot']);

        if (! $quote) {
            return $this->unavailable($company, $area, $date, $data, $locale);
        }

        return response()->json([
            'available' => true,
            'service_estimate' => $service ? ['name' => $service->translatedName($locale), 'estimated_price' => (float) $service->price, 'currency' => $company->currency, 'selected_plan' => collect($service->subscription_plans)->firstWhere('key', $data['plan_key'] ?? '')] : null,
            'date' => $quote['date'],
            'time_slot' => $quote['time_slot'],
            'time_range' => $quote['time_range'],
            'available_sessions' => $quote['available_sessions'],
            'fee' => $quote['fee'],
            'project_budget_from' => $quote['project_budget_from'],
            'currency' => $company->currency,
        ]);
    }

    public function store(
        StoreConsultationRequest $request,
        CreateConsultationRequest $create,
        ConsultationHandover $handover,
    ): JsonResponse {
        $company = ResolveCompany::company($request);
        $locale = ResolveCompany::locale($request);

        $data = $request->validated();
        $isEmail = $request->isEmailContact();

        [$area, $date, $service] = $this->resolveTarget->handle($company, $data, $locale);

        $conversation = isset($data['guest_token'])
            ? Conversation::where('company_id', $company->id)->where('guest_token', $data['guest_token'])->first()
            : null;

        $consultation = $create->handle($company, $area, [
            'product_slug' => $service?->slug,
            'plan_key' => $data['plan_key'] ?? null,
            'consultation_date' => $date,
            'time_slot' => $data['time_slot'],
            'participants' => (int) $data['participants'],
            'goal' => $data['goal'] ?? null,
            'client_name' => $data['client_name'],
            'client_email' => $isEmail ? $data['contact_value'] : null,
            'client_phone' => $isEmail ? null : $data['contact_value'],
            'contact_type' => $data['contact_type'],
            'notes' => $data['special_request'] ?? null,
        ], $conversation);

        if (! $consultation) {
            return $this->unavailable($company, $area, $date, $data, $locale);
        }

        return response()->json([
            'reference' => $consultation->reference,
            'product_snapshot' => $consultation->product_snapshot,
            'status' => $consultation->status,
            'fee' => (float) $consultation->consultation_fee_total,
            'currency' => $company->currency,
            'handover' => $handover->forConsultation($company, $consultation, $locale),
        ], 201);
    }

    /**
     * No consultation is available in that area and slot: say so, and offer what is.
     *
     * @param  array<string, mixed>  $data
     */
    private function unavailable(Company $company, Consultant $requested, CarbonImmutable $date, array $data, string $locale): JsonResponse
    {
        $message = ConsultationRules::message('unavailable', $locale);

        return response()->json([
            'message' => $message,
            'errors' => ['consultant_slug' => [$message]],
            'alternatives' => $this->findAlternatives->handle($company, $requested, $date, $data['time_slot'], (int) $data['participants'], $locale),
            'currency' => $company->currency,
        ], 422);
    }
}
