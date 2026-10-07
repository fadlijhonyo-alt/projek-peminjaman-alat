<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityAndReturnsFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_search_and_filter_activity_logs(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'admin');
        $peminjam = $this->createUser('Siti Rahma', 'siti@example.test', 'peminjam');

        $oldLog = LogAktivitas::create([
            'user_id' => $peminjam->id,
            'aktivitas' => 'Mengajukan peminjaman kamera',
        ]);
        $oldLog->forceFill(['created_at' => '2026-09-20 10:00:00'])->saveQuietly();

        $currentLog = LogAktivitas::create([
            'user_id' => $peminjam->id,
            'aktivitas' => 'Memperbarui data peminjaman kamera',
        ]);
        $currentLog->forceFill(['created_at' => '2026-09-25 10:00:00'])->saveQuietly();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', [
                'search' => 'Siti',
                'dari_tanggal' => '2026-09-24',
                'sampai_tanggal' => '2026-09-26',
            ]))
            ->assertOk()
            ->assertSee('Memperbarui data peminjaman kamera')
            ->assertDontSee('Mengajukan peminjaman kamera');
    }

    public function test_admin_can_search_and_filter_return_records(): void
    {
        $admin = $this->createUser('Admin', 'admin@example.test', 'admin');
        $siti = $this->createUser('Siti Rahma', 'siti@example.test', 'peminjam');
        $rina = $this->createUser('Rina', 'rina@example.test', 'peminjam');
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $kamera = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera Canon',
            'stok' => 10,
            'status_kondisi' => 'Baik',
        ]);
        $proyektor = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Proyektor',
            'stok' => 5,
            'status_kondisi' => 'Baik',
        ]);

        $this->createLoan($siti, $kamera, 'dikembalikan', '2026-09-25');
        $this->createLoan($rina, $proyektor, 'dikembalikan', '2026-09-25');
        $this->createLoan($siti, $kamera, 'dipinjam', '2026-09-24');
        $this->createLoan($siti, $kamera, 'ditolak', '2026-09-25');

        $this->actingAs($admin)
            ->get(route('admin.pengembalian.index', [
                'search' => 'Siti',
                'status' => 'dikembalikan',
                'dari_tanggal' => '2026-09-24',
                'sampai_tanggal' => '2026-09-26',
            ]))
            ->assertOk()
            ->assertSee('Siti Rahma')
            ->assertSee('Kamera Canon')
            ->assertDontSee('Rina')
            ->assertDontSee('Proyektor')
            ->assertDontSee('ditolak');

        $this->get(route('admin.pengembalian.index', ['search' => 'Proyektor']))
            ->assertOk()
            ->assertSee('Rina')
            ->assertDontSee('Siti Rahma');
    }

    public function test_admin_can_set_return_condition_and_additional_fine(): void
    {
        Carbon::setTestNow('2026-09-29 10:00:00');

        $admin = $this->createUser('Admin', 'admin@example.test', 'admin');
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

        $this->actingAs($admin)
            ->get(route('admin.pengembalian.create', $peminjaman))
            ->assertOk()
            ->assertSee('Denda tambahan yang diatur admin')
            ->assertSee('Hilang');

        $this->actingAs($admin)
            ->put(route('admin.pengembalian.kembalikan', $peminjaman), [
                'kondisi_kembali' => 'Rusak Ringan',
                'denda_tambahan' => 15000,
            ])
            ->assertRedirect(route('admin.pengembalian.index'));

        $this->assertDatabaseHas('pengembalian', [
            'peminjaman_id' => $peminjaman->id,
            'kondisi_kembali' => 'Rusak Ringan',
            'denda' => 35000,
            'petugas_id' => $admin->id,
        ]);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
        $this->assertSame(6, $alat->fresh()->stok);

        $this->get(route('admin.pengembalian.index'))
            ->assertOk()
            ->assertSee('Terlambat 4 hari')
            ->assertSee('Rp 5.000/hari')
            ->assertSee('Kerusakan ringan: Rp 15.000');
    }

    public function test_admin_does_not_add_lost_equipment_back_to_stock(): void
    {
        Carbon::setTestNow('2026-09-29 10:00:00');

        $admin = $this->createUser('Admin', 'admin@example.test', 'admin');
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

        $this->actingAs($admin)
            ->put(route('admin.pengembalian.kembalikan', $peminjaman), [
                'kondisi_kembali' => 'Hilang',
                'denda_tambahan' => 100000,
            ])
            ->assertRedirect(route('admin.pengembalian.index'));

        $this->assertDatabaseHas('pengembalian', [
            'peminjaman_id' => $peminjaman->id,
            'kondisi_kembali' => 'Hilang',
            'denda' => 100000,
        ]);
        $this->assertSame(4, $alat->fresh()->stok);
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

    private function createLoan(User $user, Alat $alat, string $status, string $tanggal): void
    {
        $peminjaman = Peminjaman::create([
            'user_id' => $user->id,
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