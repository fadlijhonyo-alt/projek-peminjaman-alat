<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
    public function dashboard()
    {
        $peminjamanUser = Peminjaman::where('user_id', auth()->id());
        $jumlahAktif = (clone $peminjamanUser)->whereIn('status', ['dipinjam', 'telat'])->count();
        $jumlahMenunggu = (clone $peminjamanUser)->where('status', 'diajukan')->count();
        $jumlahDikembalikan = (clone $peminjamanUser)->whereIn('status', ['dikembalikan', 'selesai'])->count();
        $aktivitasTerbaru = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('peminjam.dashboard', compact(
            'jumlahAktif',
            'jumlahMenunggu',
            'jumlahDikembalikan',
            'aktivitasTerbaru'
        ));
    }

    // Melihat daftar/katalog alat yang tersedia
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog', compact('alats'));
    }

    public function ajukanPeminjaman(Request $request)
    {
        $request->validate([
            'tgl_kembali_plan' => 'required|date|after:today',
            'alat_id' => 'required|array',
            'alat_id.*' => 'required|integer|exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Buat header peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            // Masukkan daftar alat yang dipinjam ke detail_pinjam
            foreach ($request->alat_id as $alatId) {
                $jumlahPinjam = $request->jumlah[$alatId] ?? null;
                $alat = Alat::lockForUpdate()->findOrFail($alatId);

                if (!$jumlahPinjam || $alat->stok < $jumlahPinjam) {
                    throw new \Exception("Jumlah {$alat->nama_alat} melebihi stok yang tersedia.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            DB::commit();

            return redirect()->route('peminjam.riwayat')
                ->with('success', 'Pengajuan peminjaman berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal mengajukan peminjaman: ' . $e->getMessage());
        }
    }

    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjams.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }
}