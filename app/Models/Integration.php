<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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

    protected static function booted(): void
    {
        // The last four is the only part of the key the app shows. Keeping it in
        // its own column means showing it never decrypts the key.
        static::saving(function (Integration $integration) {
            if ($integration->isDirty('api_key')) {
                $integration->api_key_last_four = substr($integration->api_key, -4);
            }
        });
    }
}
