<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_profile_page(): void
    {
        $user = User::create([
            'name' => 'Ayu Peminjam',
            'email' => 'ayu@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
            'no_hp' => '08123456789',
            'alamat' => 'Bandung',
        ]);

        $this->actingAs($user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Profil Saya')
            ->assertSee('Ayu Peminjam')
            ->assertSee('ayu@example.test')
            ->assertSee('08123456789')
            ->assertSee('Bandung')
            ->assertSee('href="' . route('profile') . '"', false)
            ->assertSee('Lihat profil');
    }

    public function test_guest_is_redirected_to_login_from_profile_page(): void
    {
        $this->get(route('profile'))
            ->assertRedirect(route('login'));
    }
}
