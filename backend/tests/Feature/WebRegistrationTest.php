<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_as_a_peminjam_and_is_logged_in(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Buat Akun')
            ->assertSee(route('login'));

        $response = $this->post(route('register.store'), [
            'name' => 'Budi Peminjam',
            'email' => 'budi@example.test',
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'budi@example.test')->firstOrFail();

        $response->assertRedirect(route('peminjam.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('peminjam', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
        ]);
    }

    public function test_registration_requires_a_unique_email_and_confirmed_password(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
        ]);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Another User',
                'email' => 'existing@example.test',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);
    }
}
