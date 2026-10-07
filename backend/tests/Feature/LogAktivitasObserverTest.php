<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_changes_to_users_categories_and_tools_are_logged(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $user = User::create([
            'name' => 'Peminjam',
            'email' => 'peminjam@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
        ]);
        $user->update(['role' => 'petugas']);

        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera',
            'stok' => 2,
            'status_kondisi' => 'Baik',
        ]);
        $alat->update(['stok' => 3]);

        $peminjaman = Peminjaman::create([
            'user_id' => $user->id,
            'tgl_pinjam' => '2026-09-28',
            'tgl_kembali_plan' => '2026-09-29',
            'status' => 'diajukan',
        ]);
        $peminjaman->update(['status' => 'dipinjam']);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $admin->id,
            'aktivitas' => "Menambahkan pengguna 'Peminjam' (peminjam@example.test, role: peminjam)",
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $admin->id,
            'aktivitas' => "Memperbarui pengguna 'Peminjam' (Kolom yang diubah: role)",
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $admin->id,
            'aktivitas' => "Menambahkan kategori 'Elektronik' (ID: {$kategori->id})",
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $admin->id,
            'aktivitas' => "Memperbarui data alat 'Kamera' (Kolom yang diubah: stok)",
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'user_id' => $admin->id,
            'aktivitas' => "Status peminjaman (ID: #{$peminjaman->id}) berubah dari 'diajukan' menjadi 'dipinjam'",
        ]);
    }
}