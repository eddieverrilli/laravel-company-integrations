<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_the_users_company(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('companies.show', $user->company));
    }

    public function test_the_company_page_lists_only_that_companys_members(): void
    {
        $user = User::factory()->create();
        $colleague = User::factory()->for($user->company)->create();
        $outsider = User::factory()->create();

        $this->actingAs($user)
            ->get(route('companies.show', $user->company))
            ->assertOk()
            ->assertSee($colleague->email)
            ->assertDontSee($outsider->email);
    }
}
