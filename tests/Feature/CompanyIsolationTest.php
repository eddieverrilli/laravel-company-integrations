<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * @return array<string, array{string, Closure(Company): string}>
     */
    public static function routes(): array
    {
        return [
            'company page' => ['get', fn (Company $company) => route('companies.show', $company)],
        ];
    }

    #[DataProvider('routes')]
    public function test_guests_are_sent_to_log_in(string $method, Closure $url): void
    {
        $this->call($method, $url(Company::factory()->create()))
            ->assertRedirect('/login');
    }

    #[DataProvider('routes')]
    public function test_another_companys_records_are_a_404(string $method, Closure $url): void
    {
        $this->actingAs(User::factory()->create())
            ->call($method, $url(Company::factory()->create()))
            ->assertNotFound();
    }
}
