<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    #[Test]
    public function it_displays_profile_edit_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
        $response->assertViewHas('user');
    }

    #[Test]
    public function it_updates_user_profile(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_on_update(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => '',
            'email' => '',
            'role' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_validates_unique_email_excluding_self(): void
    {
        User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'other@example.com',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_allows_same_email_on_update(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'test@example.com', // Same email
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function it_validates_role_options(): void
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'invalid_role',
        ]);

        $response->assertSessionHasErrors('role');
    }

    #[Test]
    public function it_clears_email_verified_at_when_email_changes(): void
    {
        $this->user->update(['email_verified_at' => now()]);

        $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'newemail@example.com',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'newemail@example.com',
            'email_verified_at' => null,
        ]);
    }

    #[Test]
    public function it_updates_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        // Verify new password works
        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }

    #[Test]
    public function it_validates_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    #[Test]
    public function it_validates_password_confirmation(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function it_validates_required_password_fields(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['current_password', 'password']);
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }
}
