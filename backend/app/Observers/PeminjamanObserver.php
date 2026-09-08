<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;

class PeminjamanObserver
{
    /**
     * Mencatat aktivitas ke tabel log_aktivitas.
     */
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }

    /**
     * Dipanggil ketika data peminjaman berhasil dibuat.
     */
    public function created(Peminjaman $peminjaman): void
    {
        $namaPeminjam = $peminjaman->user?->name ?? 'User';

        $this->catatLog(
            "Peminjam ({$namaPeminjam}) membuat permohonan " .
            "peminjaman baru (ID: #{$peminjaman->id})"
        );
    }

    /**
     * Dipanggil ketika data peminjaman diperbarui.
     */
    public function updated(Peminjaman $peminjaman): void
    {
        // Jika status berubah, catat perubahan status
        if ($peminjaman->wasChanged('status')) {
            $this->catatLog(
                "Status peminjaman (ID: #{$peminjaman->id}) " .
                "berubah menjadi: '{$peminjaman->status}'"
            );

            return;
        }

        // Jika ada perubahan kolom lain
        $perubahan = array_diff(
            array_keys($peminjaman->getChanges()),
            ['updated_at']
        );

        if (!empty($perubahan)) {
            $kolomDiubah = implode(', ', $perubahan);

            $this->catatLog(
                "Memperbarui detail data peminjaman " .
                "(ID: #{$peminjaman->id}). " .
                "Kolom yang diubah: {$kolomDiubah}"
            );
        }
    }

    /**
     * Dipanggil ketika data peminjaman dihapus.
     */
    public function deleted(Peminjaman $peminjaman): void
    {
        $this->catatLog(
            "Membatalkan/menghapus permohonan peminjaman " .
            "(ID: #{$peminjaman->id})"
        );
    }
}