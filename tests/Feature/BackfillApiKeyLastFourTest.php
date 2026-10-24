<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Runs the real migration over rows written before it existed. This class uses
 * DatabaseMigrations rather than RefreshDatabase because MySQL commits the
 * test's transaction as soon as the migration alters the table.
 */
class BackfillApiKeyLastFourTest extends TestCase
{
    use DatabaseMigrations;

    public function test_it_backfills_every_existing_row_across_several_chunks(): void
    {
        $migration = require database_path('migrations/2026_10_24_143830_add_api_key_last_four_to_integrations_table.php');

        // Back to the schema the existing rows were written under.
        $migration->down();

        // 250 rows at 100 a chunk. An offset-based chunk() would skip the middle
        // hundred, because updated rows drop out of the whereNull filter.
        $company = Company::factory()->create();
        $keys = Collection::times(250, fn () => Str::random(40));

        DB::table('integrations')->insert($keys->map(fn (string $key) => [
            'company_id' => $company->id,
            'name' => 'Existing integration',
            'api_key' => Crypt::encryptString($key),
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $migration->up();

        $this->assertSame(
            $keys->map(fn (string $key) => substr($key, -4))->all(),
            DB::table('integrations')->orderBy('id')->pluck('api_key_last_four')->all(),
        );
    }
}
