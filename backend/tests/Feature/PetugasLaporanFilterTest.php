<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetugasLaporanFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_search_filters_and_status_colors_work(): void
    {
        $petugas = User::create([
            'name' => 'Petugas',
            'email' => 'petugas@example.test',
            'password' => 'password123',
            'role' => 'petugas',
        ]);
        $siti = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
        ]);
        $rina = User::create([
            'name' => 'Rina',
            'email' => 'rina@example.test',
            'password' => 'password123',
            'role' => 'peminjam',
        ]);
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon',
            'stok' => 10,
            'status_kondisi' => 'Baik',
        ]);

        $this->createLoan($siti, $alat, 'selesai', '2026-09-20');
        $this->createLoan($siti, $alat, 'dikembalikan', '2026-09-25');
        $this->createLoan($siti, $alat, 'ditolak', '2026-09-24');
        $this->createLoan($siti, $alat, 'telat', '2026-09-24');
        $this->createLoan($siti, $alat, 'diajukan', '2026-09-24');
        $this->createLoan($siti, $alat, 'dipinjam', '2026-09-24');
        $this->createLoan($rina, $alat, 'dikembalikan', '2026-09-25');

        $this->actingAs($petugas)
            ->get(route('petugas.laporan.index'))
            ->assertOk()
            ->assertSee('bg-yellow-100 text-yellow-800', false)
            ->assertSee('bg-blue-100 text-blue-800', false)
            ->assertSee('bg-emerald-100 text-emerald-800', false)
            ->assertSee('bg-red-100 text-red-800', false)
            ->assertSee('Dikembalikan');

        $this->get(route('petugas.laporan.index', [
            'search' => 'Siti',
            'status' => 'dikembalikan',
        ]))
            ->assertOk()
            ->assertSee('2026-09-20')
            ->assertSee('2026-09-25')
            ->assertDontSee('Rina')
            ->assertDontSee('2026-09-24');

        $this->get(route('petugas.laporan.index', ['search' => 'Kamera Canon']))
            ->assertOk()
            ->assertSee('Siti Rahma')
            ->assertSee('Rina');

        $this->get(route('petugas.laporan.index', ['dari_tanggal' => '2026-09-25']))
            ->assertOk()
            ->assertSee('2026-09-25')
            ->assertDontSee('2026-09-20');

        $this->get(route('petugas.peminjaman.index', ['search' => 'Rina']))
            ->assertOk()
            ->assertSee('Rina')
            ->assertDontSee('Siti Rahma');

        $this->get(route('petugas.peminjaman.index', [
            'search' => 'Kamera Canon',
            'status' => 'diajukan',
            'dari_tanggal' => '2026-09-24',
            'sampai_tanggal' => '2026-09-24',
        ]))
            ->assertOk()
            ->assertSee('name="status"', false)
            ->assertSee('Siti Rahma')
            ->assertDontSee('2026-09-20');

        $this->get(route('petugas.pengembalian.index', [
            'search' => 'Rina',
            'status' => 'dikembalikan',
            'dari_tanggal' => '2026-09-25',
            'sampai_tanggal' => '2026-09-25',
        ]))
            ->assertOk()
            ->assertSee('name="dari_tanggal"', false)
            ->assertSee('Rina')
            ->assertDontSee('Siti Rahma');
    }

    private function createLoan(User $peminjam, Alat $alat, string $status, string $tanggal): void
    {
        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tgl_pinjam' => $tanggal,
            'tgl_kembali_plan' => '2026-10-01',
            'status' => $status,
        ]);

        DetailPinjam::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);
    }
}