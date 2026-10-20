<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateConsultationStatusRequest;
use App\Models\Consultation;
use App\Modules\Consultation\Actions\ChangeConsultationStatus;
use App\Modules\Consultation\Enums\ConsultationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    use ResolvesCurrentCompany;

    public function index(Request $request): View
    {
        $company = $this->currentCompany($request);

        $status = in_array($request->query('status'), ConsultationStatus::values(), true)
            ? $request->query('status')
            : null;

        $consultations = $company->consultations()
            ->with('consultant')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.consultations.index', compact('company', 'consultations', 'status'));
    }

    public function updateStatus(UpdateConsultationStatusRequest $request, Consultation $consultation, ChangeConsultationStatus $changeStatus): RedirectResponse
    {
        $this->currentCompany($request);

        $status = $request->validated('status');

        $changeStatus->handle($consultation, $status);

        return back()->with('status', "Consultation {$consultation->reference} marked as {$status}.");
    }
}
