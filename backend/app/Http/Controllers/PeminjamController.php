<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
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
    public function katalogAlat(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $kategoriId = $request->input('kategori_id');

        $alats = Alat::with('kategori')
            ->where('stok', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_alat', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, function ($query) use ($kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->orderBy('nama_alat')
            ->get();

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('peminjam.katalog', compact('alats', 'kategoris', 'search', 'kategoriId'));
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
        $peminjamans = Peminjaman::with(['detailPinjams.alat', 'pengembalian'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $hariIni = today();
        $jumlahTerlambat = $peminjamans->filter(function ($peminjaman) use ($hariIni) {
            return in_array($peminjaman->status, ['dipinjam', 'telat'], true)
                && !$peminjaman->pengembalian
                && $peminjaman->tgl_kembali_plan->lt($hariIni);
        })->count();

        return view('peminjam.riwayat', compact('peminjamans', 'hariIni', 'jumlahTerlambat'));
    }
}