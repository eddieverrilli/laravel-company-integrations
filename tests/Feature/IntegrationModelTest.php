<?php

namespace Tests\Feature;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntegrationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_api_key_is_encrypted_at_rest(): void
    {
        $integration = Integration::factory()->create(['api_key' => 'fake-key-1234']);

        $stored = DB::table('integrations')->where('id', $integration->id)->value('api_key');

        $this->assertNotSame('fake-key-1234', $stored);
        $this->assertSame('fake-key-1234', Crypt::decryptString($stored));
    }

    public function test_the_api_key_is_never_serialized(): void
    {
        $integration = Integration::factory()->create(['api_key' => 'fake-key-1234']);

        $this->assertArrayNotHasKey('api_key', $integration->toArray());
        $this->assertStringNotContainsString('fake-key-1234', $integration->toJson());
    }

    public function test_only_the_last_four_characters_are_exposed_for_display(): void
    {
        $integration = Integration::factory()->create(['api_key' => 'fake-key-1234']);

        $this->assertSame('1234', $integration->api_key_last_four);
    }

    public function test_creating_fills_in_the_signed_in_users_company(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $integration = Integration::create(['name' => 'Search ads', 'api_key' => 'fake-key-1234']);

        $this->assertSame($user->company_id, $integration->company_id);
    }

    public function test_signed_in_users_only_see_their_companys_integrations(): void
    {
        $user = User::factory()->create();
        $ours = Integration::factory()->for($user->company)->create();
        $theirs = Integration::factory()->create();

        $this->actingAs($user);

        $this->assertSame([$ours->id], Integration::pluck('id')->all());
        $this->assertNull(Integration::find($theirs->id));
    }

    public function test_the_scope_is_off_without_a_signed_in_user(): void
    {
        Integration::factory()->count(2)->create();

        $this->assertSame(2, Integration::count());
    }
}
