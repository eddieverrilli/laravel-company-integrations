<?php

namespace Tests\Feature;

use App\Models\Integration;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every route that takes a company or one of its records belongs in routes(),
 * so each one is checked as a guest and as a user from another company.
 */
class CompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, Closure(Integration): string}>
     */
    public static function routes(): array
    {
        return [
            'company page' => ['get', fn (Integration $theirs) => route('companies.show', $theirs->company)],
            'add integration form' => ['get', fn (Integration $theirs) => route('companies.integrations.create', $theirs->company)],
            'store integration' => ['post', fn (Integration $theirs) => route('companies.integrations.store', $theirs->company)],
            'edit integration form' => ['get', fn (Integration $theirs) => route('companies.integrations.edit', [$theirs->company, $theirs])],
            'update integration' => ['put', fn (Integration $theirs) => route('companies.integrations.update', [$theirs->company, $theirs])],
        ];
    }

    /**
     * The routes that take an integration, for swapping another company's
     * integration ID into a URL that names your own company.
     *
     * @return array<string, array{string, string}>
     */
    public static function integrationRoutes(): array
    {
        return [
            'edit integration form' => ['get', 'companies.integrations.edit'],
            'update integration' => ['put', 'companies.integrations.update'],
        ];
    }

    #[DataProvider('routes')]
    public function test_guests_are_sent_to_log_in(string $method, Closure $url): void
    {
        $this->call($method, $url(Integration::factory()->create()))
            ->assertRedirect('/login');
    }

    #[DataProvider('routes')]
    public function test_another_companys_records_are_a_404(string $method, Closure $url): void
    {
        Http::fake();
        $theirs = Integration::factory()->create(['name' => 'Theirs']);

        $this->actingAs(User::factory()->create())
            ->call($method, $url($theirs), ['name' => 'Changed', 'api_key' => 'fake-key-0000'])
            ->assertNotFound();

        Http::assertNothingSent();
        $this->assertSame('Theirs', $theirs->fresh()->name);
        $this->assertDatabaseCount('integrations', 1);
    }

    #[DataProvider('integrationRoutes')]
    public function test_another_companys_integration_under_your_own_company_is_a_404(string $method, string $route): void
    {
        $user = User::factory()->create();
        $theirs = Integration::factory()->create(['name' => 'Theirs']);

        $this->actingAs($user)
            ->call($method, route($route, [$user->company, $theirs]), ['name' => 'Changed'])
            ->assertNotFound();

        $this->assertSame('Theirs', $theirs->fresh()->name);
    }

    /**
     * The global scope hides other companies' integrations from route binding,
     * so the tests above would pass without the policies. This checks the
     * policies on their own.
     */
    public function test_the_policies_answer_another_company_with_a_404(): void
    {
        $gate = Gate::forUser(User::factory()->create());
        $theirs = Integration::factory()->create();

        $this->assertSame(404, $gate->inspect('view', $theirs->company)->status());
        $this->assertSame(404, $gate->inspect('create', [Integration::class, $theirs->company])->status());
        $this->assertSame(404, $gate->inspect('update', $theirs)->status());
    }
}
