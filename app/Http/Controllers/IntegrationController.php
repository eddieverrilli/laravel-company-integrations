<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Integration;
use App\Rules\AcceptedByProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function create(Company $company): View
    {
        Gate::authorize('create', [Integration::class, $company]);

        return view('integrations.form', ['company' => $company, 'integration' => new Integration]);
    }

    public function store(Request $request, Company $company): Response
    {
        Gate::authorize('create', [Integration::class, $company]);

        return $this->save($request, $company, new Integration);
    }

    public function edit(Company $company, Integration $integration): View
    {
        Gate::authorize('update', $integration);

        return view('integrations.form', ['company' => $company, 'integration' => $integration]);
    }

    public function update(Request $request, Company $company, Integration $integration): Response
    {
        Gate::authorize('update', $integration);

        return $this->save($request, $company, $integration);
    }

    /**
     * The form posts here through htmx. On a 422 the response is the form with
     * its errors, which htmx swaps in; on success HX-Redirect says where to go.
     * Validation runs after Gate::authorize, so an outsider never triggers the
     * provider call.
     */
    private function save(Request $request, Company $company, Integration $integration): Response
    {
        // On edit the key field only arrives when the user chose to replace the key.
        $keyRules = ['bail', 'required', 'string', 'max:255', new AcceptedByProvider];

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'api_key' => $integration->exists ? ['sometimes', ...$keyRules] : $keyRules,
        ], attributes: ['api_key' => 'API key']);

        if ($validator->fails()) {
            $integration->name = $request->input('name'); // redisplay the name, never the key

            return response(
                view('integrations.form', compact('company', 'integration'))->withErrors($validator)->fragment('form'),
                422,
            );
        }

        $integration->fill($validator->validated())->save();

        $request->session()->flash('status', 'Integration saved.');

        return response()->noContent()->header('HX-Redirect', route('companies.show', $company));
    }
}
