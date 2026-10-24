<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Log in');
    }

    public function test_users_can_log_in(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_and_password_are_required(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'not-the-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
