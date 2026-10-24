<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Users only see their own company. Anyone else gets a 404, so the
     * response doesn't confirm that the company exists.
     */
    public function view(User $user, Company $company): Response
    {
        return $user->company_id === $company->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
