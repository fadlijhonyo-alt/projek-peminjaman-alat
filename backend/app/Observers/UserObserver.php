<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
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

    public function created(User $user): void
    {
        $this->catatLog("Menambahkan pengguna '{$user->name}' ({$user->email}, role: {$user->role})");
    }

    public function updated(User $user): void
    {
        $perubahan = array_diff(array_keys($user->getChanges()), ['updated_at']);

        if ($perubahan !== []) {
            $kolomDiubah = implode(', ', $perubahan);
            $this->catatLog("Memperbarui pengguna '{$user->name}' (Kolom yang diubah: {$kolomDiubah})");
        }
    }

    public function deleted(User $user): void
    {
        $this->catatLog("Menghapus pengguna '{$user->name}' ({$user->email}, ID: {$user->id})");
    }
}