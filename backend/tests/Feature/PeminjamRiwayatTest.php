<?php

namespace Tests\Feature;

use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamRiwayatTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_riwayat_shows_return_date_fine_and_notice_for_late_return(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $peminjam = $this->createUser('Siti', 'siti@example.test', 'peminjam');
        $petugas = $this->createUser('Petugas', 'petugas@example.test', 'petugas');
        $peminjaman = $this->createLoan($peminjam, 'dikembalikan', '2026-11-05');

        Pengembalian::create([
            'peminjaman_id' => $peminjaman->id,
            'tgl_kembali' => '2026-11-08',
            'kondisi_kembali' => 'Baik',
            'denda' => 15000,
            'petugas_id' => $petugas->id,
        ]);

        $this->actingAs($peminjam)
            ->get(route('peminjam.riwayat'))
            ->assertOk()
            ->assertSee('Tanggal dikembalikan:')
            ->assertSee('08/11/2026')
            ->assertSee('Rp 15.000')
            ->assertSee('Terlambat 3 hari dari tanggal rencana kembali.');
    }

    public function test_riwayat_warns_borrower_when_active_loan_is_past_due(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $peminjam = $this->createUser('Siti', 'siti@example.test', 'peminjam');
        $this->createLoan($peminjam, 'dipinjam', '2026-11-07');

        $this->actingAs($peminjam)
            ->get(route('peminjam.riwayat'))
            ->assertOk()
            ->assertSee('Pemberitahuan keterlambatan')
            ->assertSee('Ada 1 peminjaman yang sudah melewati tanggal rencana kembali.')
            ->assertSee('Terlambat 2 hari.')
            ->assertSee('Segera kembalikan alat.');
    }

    public function test_riwayat_does_not_warn_for_a_loan_due_today(): void
    {
        Carbon::setTestNow('2026-11-09 12:00:00');
        $peminjam = $this->createUser('Siti', 'siti@example.test', 'peminjam');
        $this->createLoan($peminjam, 'dipinjam', '2026-11-09');

        $this->actingAs($peminjam)
            ->get(route('peminjam.riwayat'))
            ->assertOk()
            ->assertDontSee('Pemberitahuan keterlambatan')
            ->assertDontSee('Segera kembalikan alat.');
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

    private function createLoan(User $user, string $status, string $plannedReturnDate): Peminjaman
    {
        return Peminjaman::create([
            'user_id' => $user->id,
            'tgl_pinjam' => '2026-11-01',
            'tgl_kembali_plan' => $plannedReturnDate,
            'status' => $status,
        ]);
    }
}
