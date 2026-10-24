<?php

namespace Tests\Feature;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_add_form_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('companies.integrations.create', $user->company))
            ->assertOk()
            ->assertSee('Add integration');
    }

    public function test_a_new_key_is_checked_with_the_provider_then_saved(): void
    {
        Http::fake(['ads.example.com/*' => Http::response(['id' => 'acct_1'])]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('companies.integrations.store', $user->company), [
                'name' => 'Search ads',
                'api_key' => 'fake-key-1234',
            ])
            ->assertNoContent()
            ->assertHeader('HX-Redirect', route('companies.show', $user->company))
            ->assertSessionHas('status', 'Integration saved.');

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://ads.example.com/api/v1/me'
            && $request->hasHeader('Authorization', 'Bearer fake-key-1234'));

        $integration = $user->company->integrations()->sole();
        $this->assertSame('Search ads', $integration->name);
        $this->assertSame('fake-key-1234', $integration->api_key);
    }

    public function test_name_and_key_are_required(): void
    {
        Http::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('companies.integrations.store', $user->company), [])
            ->assertUnprocessable()
            ->assertSee('The name field is required.')
            ->assertSee('The API key field is required.');

        // The body is just the form, for htmx to swap in place of the old one.
        $this->assertStringStartsWith('<form', trim($response->getContent()));
        Http::assertNothingSent();
        $this->assertDatabaseCount('integrations', 0);
    }

    public function test_a_key_the_provider_rejects_comes_back_as_a_validation_error(): void
    {
        Http::fake(['ads.example.com/*' => Http::response(['error' => 'invalid_key'], 401)]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('companies.integrations.store', $user->company), [
                'name' => 'Search ads',
                'api_key' => 'fake-key-1234',
            ])
            ->assertUnprocessable()
            ->assertSee('The provider rejected this API key.')
            ->assertSee('value="Search ads"', false)
            ->assertDontSee('fake-key-1234');

        $this->assertDatabaseCount('integrations', 0);
    }

    public function test_an_unreachable_provider_comes_back_as_a_validation_error(): void
    {
        Http::fake(['ads.example.com/*' => Http::failedConnection()]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('companies.integrations.store', $user->company), [
                'name' => 'Search ads',
                'api_key' => 'fake-key-1234',
            ])
            ->assertUnprocessable()
            ->assertSee("We couldn't reach the provider to check this key.");

        $this->assertDatabaseCount('integrations', 0);
    }

    public function test_renaming_keeps_the_key_without_calling_the_provider(): void
    {
        Http::fake();
        $user = User::factory()->create();
        $integration = Integration::factory()->for($user->company)->create(['api_key' => 'fake-key-1234']);

        $this->actingAs($user)
            ->put(route('companies.integrations.update', [$user->company, $integration]), ['name' => 'Renamed'])
            ->assertNoContent()
            ->assertHeader('HX-Redirect', route('companies.show', $user->company));

        Http::assertNothingSent();
        $this->assertSame('Renamed', $integration->fresh()->name);
        $this->assertSame('fake-key-1234', $integration->fresh()->api_key);
    }

    public function test_a_rejected_replacement_key_leaves_the_old_one_in_place(): void
    {
        Http::fake(['ads.example.com/*' => Http::response(status: 401)]);
        $user = User::factory()->create();
        $integration = Integration::factory()->for($user->company)->create(['api_key' => 'fake-key-1234']);

        $this->actingAs($user)
            ->put(route('companies.integrations.update', [$user->company, $integration]), [
                'name' => $integration->name,
                'api_key' => 'fake-key-9999',
            ])
            ->assertUnprocessable()
            ->assertSee('The provider rejected this API key.')
            ->assertDontSee('fake-key-9999');

        $this->assertSame('fake-key-1234', $integration->fresh()->api_key);
    }

    public function test_an_accepted_replacement_key_is_saved(): void
    {
        Http::fake(['ads.example.com/*' => Http::response(['id' => 'acct_1'])]);
        $user = User::factory()->create();
        $integration = Integration::factory()->for($user->company)->create(['api_key' => 'fake-key-1234']);

        $this->actingAs($user)
            ->put(route('companies.integrations.update', [$user->company, $integration]), [
                'name' => $integration->name,
                'api_key' => 'fake-key-9999',
            ])
            ->assertNoContent();

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer fake-key-9999'));
        $this->assertSame('fake-key-9999', $integration->fresh()->api_key);
    }

    public function test_pages_show_the_last_four_and_never_the_key(): void
    {
        $user = User::factory()->create();
        $integration = Integration::factory()->for($user->company)->create(['api_key' => 'fake-key-1234']);
        $ciphertext = DB::table('integrations')->value('api_key');

        $this->actingAs($user);

        $pages = [
            route('companies.show', $user->company),
            route('companies.integrations.edit', [$user->company, $integration]),
        ];

        foreach ($pages as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('•••• 1234')
                ->assertDontSee('fake-key-1234')
                ->assertDontSee($ciphertext);
        }
    }
}
