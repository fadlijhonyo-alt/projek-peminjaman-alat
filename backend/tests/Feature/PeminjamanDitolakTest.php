<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanDitolakTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_loan_remains_visible_to_staff_and_borrower(): void
    {
        $petugas = User::create([
            'name' => 'Petugas',
            'email' => 'petugas@example.test',
            'password' => 'password123',
            'role' => 'petugas',
        ]);
        $peminjam = User::create([
            'name' => 'Peminjam',
            'email' => 'peminjam@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tgl_pinjam' => '2026-09-28',
            'tgl_kembali_plan' => '2026-09-29',
            'status' => 'diajukan',
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.peminjaman.tolak', $peminjaman->id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'ditolak',
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.peminjaman.index'))
            ->assertOk()
            ->assertSee('Ditolak');

        $this->actingAs($peminjam)
            ->get(route('peminjam.riwayat'))
            ->assertOk()
            ->assertSee('Ditolak');
    }
}