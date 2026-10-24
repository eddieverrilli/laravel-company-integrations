<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes the model to the signed-in user's company and fills in company_id
 * when a record is created during a request.
 *
 * With nobody signed in (console commands, queued jobs) the scope is off, so
 * that code has to scope its own queries. Don't add this to User: resolving
 * the signed-in user would run the scope, which needs the signed-in user.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $query) {
            if ($user = Auth::user()) {
                $query->where($query->qualifyColumn('company_id'), $user->company_id);
            }
        });

        static::creating(function (Model $model) {
            if ($user = Auth::user()) {
                $model->company_id ??= $user->company_id;
            }
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
