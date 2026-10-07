<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamDashboardCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_and_recent_activity_only_use_the_signed_in_borrower(): void
    {
        $peminjam = $this->createUser('Siti', 'siti@example.test');
        $peminjamLain = $this->createUser('Rina', 'rina@example.test');

        $this->actingAs($peminjam);
        $this->createLoan($peminjam, 'dipinjam');
        $this->createLoan($peminjam, 'telat');
        $this->createLoan($peminjam, 'diajukan');
        $this->createLoan($peminjam, 'selesai');
        $this->createLoan($peminjam, 'dikembalikan');
        $this->createLoan($peminjam, 'ditolak');
        $this->createLoan($peminjamLain, 'dipinjam');

        $this->get(route('peminjam.dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas Peminjaman Terbaru')
            ->assertSee('Dikembalikan')
            ->assertDontSee('Rina');
    }

    public function test_catalog_shows_tool_photo_and_description_and_submits_matching_quantity(): void
    {
        $peminjam = $this->createUser('Siti', 'siti@example.test');
        $kategori = Kategori::create(['nama_kategori' => 'Elektronik']);
        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Kamera',
            'stok' => 4,
            'status_kondisi' => 'Baik',
            'deskripsi' => 'Kamera untuk dokumentasi acara.',
            'gambar' => 'storage/alat/kamera.jpg',
        ]);

        $this->actingAs($peminjam)
            ->get(route('peminjam.katalog'))
            ->assertOk()
            ->assertSee(asset('storage/alat/kamera.jpg'))
            ->assertSee('Kamera untuk dokumentasi acara.')
            ->assertSee('jumlah[' . $alat->id . ']', false);

        $this->post(route('peminjam.peminjaman.ajukan'), [
            'tgl_kembali_plan' => now()->addDays(2)->toDateString(),
            'alat_id' => [$alat->id => $alat->id],
            'jumlah' => [$alat->id => 2],
        ])->assertRedirect(route('peminjam.riwayat'));

        $this->assertDatabaseHas('detail_pinjam', [
            'alat_id' => $alat->id,
            'jumlah' => 2,
        ]);
    }

    private function createUser(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => 'peminjam',
        ]);
    }

    private function createLoan(User $user, string $status): void
    {
        Peminjaman::create([
            'user_id' => $user->id,
            'tgl_pinjam' => now()->toDateString(),
            'tgl_kembali_plan' => now()->addDay()->toDateString(),
            'status' => $status,
        ]);
    }
}