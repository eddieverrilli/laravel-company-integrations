<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'api_key'])]
#[Hidden(['api_key'])]
class Integration extends Model
{
    /** @use HasFactory<IntegrationFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }

    /**
     * The only part of the key the app ever shows.
     */
    protected function apiKeyLastFour(): Attribute
    {
        return Attribute::get(fn () => substr($this->api_key, -4));
    }
}
