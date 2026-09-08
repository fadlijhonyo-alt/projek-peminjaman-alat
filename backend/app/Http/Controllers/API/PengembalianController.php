<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Http\Requests\Pengembalian\UpdatePengembalianRequest;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    /**
     * Menampilkan daftar pengembalian.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $query = Pengembalian::with([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas',
        ]);

        // Peminjam hanya dapat melihat pengembaliannya sendiri
        if ($user->role === 'peminjam') {
            $query->whereHas('peminjaman', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $pengembalian = $query->latest()->get();

        return response()->json([
            'message' => 'Riwayat pengembalian berhasil diambil.',
            'data' => $pengembalian,
        ]);
    }

    /**
     * Menampilkan detail pengembalian.
     */
    public function show(Pengembalian $pengembalian): JsonResponse
    {
        $user = auth()->user();

        // Eager load relasi
        $pengembalian->load([
            'peminjaman.user',
            'peminjaman.detailPinjam.alat',
            'petugas',
        ]);

        // Peminjam hanya dapat melihat pengembaliannya sendiri
        if (
            $user->role === 'peminjam' &&
            $pengembalian->peminjaman->user_id !== $user->id
        ) {
            return response()->json([
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return response()->json([
            'message' => 'Detail pengembalian berhasil diambil.',
            'data' => $pengembalian,
        ]);
    }

    /**
     * Memproses pengembalian alat.
     */
    public function store(
        StorePengembalianRequest $request
    ): JsonResponse {
        try {
            $pengembalian = DB::transaction(function () use ($request) {

                // Mengunci data peminjaman selama transaksi
                $peminjaman = Peminjaman::with('detailPinjam')
                    ->lockForUpdate()
                    ->find($request->peminjaman_id);

                // Pastikan data peminjaman ditemukan
                if (!$peminjaman) {
                    throw new Exception(
                        'Data peminjaman tidak ditemukan.'
                    );
                }

                // Pastikan status masih dipinjam
                if ($peminjaman->status !== 'dipinjam') {
                    throw new Exception(
                        "Data ditolak. Peminjaman ini berstatus " .
                        "'{$peminjaman->status}', bukan 'dipinjam'."
                    );
                }

                // Cek tanggal keterlambatan
                $tglKembaliPlan = Carbon::parse(
                    $peminjaman->tgl_kembali_plan
                )->startOfDay();

                $hariIni = Carbon::now()->startOfDay();

                $statusPeminjamanBaru = $hariIni->greaterThan(
                    $tglKembaliPlan
                )
                    ? 'telat'
                    : 'dikembalikan';

                // 1. Simpan data pengembalian
                $pengembalian = Pengembalian::create([
                    'peminjaman_id' => $peminjaman->id,
                    'tgl_kembali' => now()->toDateString(),
                    'kondisi_kembali' => $request->kondisi_kembali,
                    'denda' => $request->denda ?? 0,
                    'petugas_id' => auth()->id(),
                ]);

                // 2. Update status peminjaman
                $peminjaman->update([
                    'status' => $statusPeminjamanBaru,
                ]);

                // 3. Kembalikan stok alat
                foreach ($peminjaman->detailPinjam as $detail) {

                    $alat = Alat::lockForUpdate()
                        ->find($detail->alat_id);

                    if ($alat) {
                        $alat->increment(
                            'stok',
                            $detail->jumlah
                        );
                    }
                }

                // 4. Catat aktivitas petugas
                auth()->user()->logAktivitas()->create([
                    'aktivitas' =>
                        "Memproses pengembalian peminjaman ID: " .
                        "#{$peminjaman->id} dengan status akhir: " .
                        "{$statusPeminjamanBaru}.",
                ]);

                // Load relasi untuk response
                return $pengembalian->load([
                    'peminjaman.user',
                    'peminjaman.detailPinjam.alat',
                    'petugas',
                ]);
            });

            return response()->json([
                'message' =>
                    'Proses pengembalian alat berhasil diselesaikan.',
                'data' => $pengembalian,
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Memperbarui data pengembalian.
     */
    public function update(
        UpdatePengembalianRequest $request,
        Pengembalian $pengembalian
    ): JsonResponse {
        // Membatasi field yang boleh diperbarui
        $pengembalian->update([
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $request->denda ?? $pengembalian->denda,
        ]);

        return response()->json([
            'message' => 'Data pengembalian berhasil diperbarui.',
            'data' => $pengembalian->load([
                'peminjaman.user',
                'petugas',
            ]),
        ]);
    }

    /**
     * Membatalkan pengembalian dan menghapus datanya.
     */
    public function destroy(
        Pengembalian $pengembalian
    ): JsonResponse {
        try {

            DB::transaction(function () use ($pengembalian) {

                // Ambil data peminjaman dan kunci selama transaksi
                $peminjaman = Peminjaman::with('detailPinjam')
                    ->lockForUpdate()
                    ->findOrFail(
                        $pengembalian->peminjaman_id
                    );

                // Kurangi kembali stok alat
                foreach ($peminjaman->detailPinjam as $detail) {

                    $alat = Alat::lockForUpdate()
                        ->findOrFail($detail->alat_id);

                    // Pastikan stok mencukupi
                    if ($alat->stok < $detail->jumlah) {
                        throw new Exception(
                            "Gagal membatalkan pengembalian. " .
                            "Stok alat '{$alat->nama_alat}' saat ini " .
                            "tidak mencukupi untuk ditarik kembali."
                        );
                    }

                    $alat->decrement(
                        'stok',
                        $detail->jumlah
                    );
                }

                // Kembalikan status peminjaman menjadi dipinjam
                $peminjaman->update([
                    'status' => 'dipinjam',
                ]);

                // Catat aktivitas petugas
                auth()->user()->logAktivitas()?->create([
                    'aktivitas' =>
                        "Membatalkan pengembalian ID: " .
                        "#{$pengembalian->id}",
                ]);

                // Hapus data pengembalian
                $pengembalian->delete();
            });

            return response()->json([
                'message' =>
                    'Data pengembalian berhasil dihapus. ' .
                    'Stok dan status peminjaman telah dikembalikan ' .
                    'ke kondisi semula.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}