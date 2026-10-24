<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IntegrationPolicy
{
    /**
     * Only a company's own members add integrations to it.
     */
    public function create(User $user, Company $company): Response
    {
        return $user->company_id === $company->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Only members of the integration's company change it. The global scope
     * already hides other companies' integrations; this holds even without it.
     */
    public function update(User $user, Integration $integration): Response
    {
        return $user->company_id === $integration->company_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
