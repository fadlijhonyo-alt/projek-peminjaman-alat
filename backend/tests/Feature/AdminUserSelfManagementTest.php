<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserSelfManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_own_account_without_changing_role_or_deleting_it(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.user.index'))
            ->assertOk()
            ->assertSee(route('admin.user.edit', $admin->id))
            ->assertDontSee('value="DELETE"', false);

        $this->get(route('admin.user.edit', $admin->id))
            ->assertOk()
            ->assertSee('Role akun sendiri tidak dapat diubah.');
        $this->put(route('admin.user.update', $admin->id), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'role' => 'peminjam',
        ])->assertRedirect(route('admin.user.index'));
        $this->delete(route('admin.user.destroy', $admin->id))->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.test',
            'role' => 'admin',
        ]);
    }
}