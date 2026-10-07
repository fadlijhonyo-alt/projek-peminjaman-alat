<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetugasPengembalianTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_petugas_can_review_and_process_a_late_return(): void
    {
        Carbon::setTestNow('2026-09-29 10:00:00');

        $petugas = $this->createUser('Petugas', 'petugas@example.test', 'petugas');
        $peminjam = $this->createUser('Siti Rahma', 'siti@example.test', 'peminjam');
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon',
            'stok' => 4,
            'status_kondisi' => 'Baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tgl_pinjam' => '2026-09-20',
            'tgl_kembali_plan' => '2026-09-25',
            'status' => 'dipinjam',
        ]);
        DetailPinjam::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 2,
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.pengembalian.index'))
            ->assertOk()
            ->assertSee('Pengembalian Peminjaman Alat')
            ->assertSee('4 hari')
            ->assertSee('Rp 20.000')
            ->assertSee('Kembalikan');

        $this->get(route('petugas.pengembalian.create', $peminjaman))
            ->assertOk()
            ->assertSee('Denda tambahan yang diatur petugas')
            ->assertSee('Rusak Berat')
            ->assertSee('Hilang');

        $this->post(route('petugas.pengembalian.proses', $peminjaman), [
            'kondisi_kembali' => 'Baik',
            'denda_tambahan' => 15000,
        ])->assertRedirect();

        $this->assertDatabaseHas('pengembalian', [
            'peminjaman_id' => $peminjaman->id,
            'tgl_kembali' => '2026-09-29 00:00:00',
            'kondisi_kembali' => 'Baik',
            'denda' => 35000,
            'petugas_id' => $petugas->id,
        ]);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
        $this->assertSame(6, $alat->fresh()->stok);
    }

    public function test_lost_equipment_is_not_added_back_to_stock(): void
    {
        Carbon::setTestNow('2026-09-29 10:00:00');

        $petugas = $this->createUser('Petugas', 'petugas@example.test', 'petugas');
        $peminjam = $this->createUser('Siti Rahma', 'siti@example.test', 'peminjam');
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon',
            'stok' => 4,
            'status_kondisi' => 'Baik',
        ]);
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tgl_pinjam' => '2026-09-20',
            'tgl_kembali_plan' => '2026-09-29',
            'status' => 'dipinjam',
        ]);
        DetailPinjam::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        $this->actingAs($petugas)
            ->post(route('petugas.pengembalian.proses', $peminjaman), [
                'kondisi_kembali' => 'Hilang',
                'denda_tambahan' => 100000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pengembalian', [
            'peminjaman_id' => $peminjaman->id,
            'kondisi_kembali' => 'Hilang',
            'denda' => 100000,
        ]);
        $this->assertSame(4, $alat->fresh()->stok);

        $this->get(route('petugas.pengembalian.index'))
            ->assertOk()
            ->assertSee('Barang hilang: Rp 100.000');
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }
}