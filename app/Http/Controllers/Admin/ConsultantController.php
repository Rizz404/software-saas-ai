<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConsultantRequest;
use App\Models\Consultant;
use App\Modules\Consultants\Actions\SaveConsultant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ConsultantController extends Controller
{
    use ResolvesCurrentCompany;

    public function index(Request $request): View
    {
        $company = $this->currentCompany($request);

        $consultants = $company->consultants()->withCount('images')->orderBy('sort_order')->get();

        return view('admin.consultants.index', compact('company', 'consultants'));
    }

    public function create(Request $request): View
    {
        $company = $this->currentCompany($request);

        return view('admin.consultants.form', [
            'company' => $company,
            'consultant' => new Consultant(['specialty' => Consultant::SPECIALTY_SOLUTION, 'min_participants' => 1, 'max_participants' => 4, 'consultation_fee' => 0, 'is_active' => true]),
        ]);
    }

    public function store(ConsultantRequest $request, SaveConsultant $save): RedirectResponse
    {
        $consultant = $save->handle(
            $this->currentCompany($request),
            $request->consultantAttributes(),
            $request->imageIdsToDelete(),
            $request->newImages(),
        );

        return redirect()->route('admin.consultants.index')->with('status', "Consultant \"{$consultant->name}\" created.");
    }

    public function edit(Request $request, Consultant $consultant): View
    {
        $company = $this->currentCompany($request);
        Gate::authorize('update', $consultant);

        $consultant->load('images');

        return view('admin.consultants.form', compact('company', 'consultant'));
    }

    public function update(ConsultantRequest $request, Consultant $consultant, SaveConsultant $save): RedirectResponse
    {
        $save->handle($this->currentCompany($request), $request->consultantAttributes(), $request->imageIdsToDelete(), $request->newImages(), $consultant);

        return redirect()->route('admin.consultants.index')->with('status', "Consultant \"{$consultant->name}\" updated.");
    }

    public function destroy(Request $request, Consultant $consultant): RedirectResponse
    {
        $this->currentCompany($request);
        Gate::authorize('delete', $consultant);

        $consultant->delete();

        return redirect()->route('admin.consultants.index')->with('status', 'Consultant deleted.');
    }
}
