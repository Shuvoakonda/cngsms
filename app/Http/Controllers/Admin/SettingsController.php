<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Pump;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'company' => Company::current(),
            'pumpsCount' => Pump::query()->count(),
            'vehiclesCount' => Vehicle::query()->count(),
            'driversCount' => Driver::query()->count(),
        ]);
    }

    public function updateCompany(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = Company::current();
        $data = $request->safe()->except(['logo', 'remove_logo']);

        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        $company->update($data);

        return redirect()
            ->route('admin.settings')
            ->with('status', 'Company settings saved successfully.');
    }
}
