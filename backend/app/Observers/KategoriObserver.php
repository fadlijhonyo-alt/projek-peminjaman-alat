<?php

namespace App\Observers;

use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class KategoriObserver
{
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }

    public function created(Kategori $kategori): void
    {
        $this->catatLog("Menambahkan kategori '{$kategori->nama_kategori}' (ID: {$kategori->id})");
    }

    public function updated(Kategori $kategori): void
    {
        $perubahan = array_diff(array_keys($kategori->getChanges()), ['updated_at']);

        if ($perubahan !== []) {
            $kolomDiubah = implode(', ', $perubahan);
            $this->catatLog("Memperbarui kategori '{$kategori->nama_kategori}' (Kolom yang diubah: {$kolomDiubah})");
        }
    }

    public function deleted(Kategori $kategori): void
    {
        $this->catatLog("Menghapus kategori '{$kategori->nama_kategori}' (ID: {$kategori->id})");
    }
}