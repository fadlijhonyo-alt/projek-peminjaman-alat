<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    // Menampilkan daftar pengajuan peminjaman dari siswa/peminjam
    public function indexPeminjaman(Request $request)
    {
        $peminjaman = $this->applyPeminjamanFilters(
            Peminjaman::with(['user', 'detailPinjam.alat']),
            $request
        )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.peminjaman.index', compact('peminjaman'));
    }

    public function setujuiPeminjaman($id)
    {
        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);
            $peminjaman->update(['status' => 'dipinjam']);

            // Kurangi stok alat secara otomatis
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok -= $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function indexPengembalian(Request $request)
    {
        Peminjaman::where('status', 'dipinjam')
            ->whereDate('tgl_kembali_plan', '<', Carbon::today())
            ->update(['status' => 'telat']);

        $pengembalian = $this->applyPeminjamanFilters(
            Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
                ->whereIn('status', ['dipinjam', 'telat', 'dikembalikan']),
            $request
        )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('petugas.pengembalian.index', compact('pengembalian'));
    }

    public function createPengembalian($peminjamanId)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])->findOrFail($peminjamanId);

        if (!in_array($peminjaman->status, ['dipinjam', 'telat'], true)) {
            return redirect()->route('petugas.pengembalian.index')
                ->with('error', 'Peminjaman ini sudah dikembalikan.');
        }

        return view('petugas.pengembalian.create', compact('peminjaman'));
    }

    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak Ringan,Rusak Berat,Hilang',
            'denda_tambahan' => 'required|integer|min:0|max:100000000',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')
                ->lockForUpdate()
                ->findOrFail($peminjamanId);

            if (!in_array($peminjaman->status, ['dipinjam', 'telat'], true)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Peminjaman ini sudah dikembalikan.');
            }

            $tanggalRencana = Carbon::parse($peminjaman->tgl_kembali_plan);
            $tanggalKembali = Carbon::today();
            $hariTerlambat = $tanggalKembali->gt($tanggalRencana)
                ? $tanggalRencana->diffInDays($tanggalKembali)
                : 0;
            $dendaKeterlambatan = $hariTerlambat * 5000;
            $denda = $dendaKeterlambatan + (int) $request->denda_tambahan;

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $tanggalKembali,
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $denda,
                'petugas_id' => auth()->id(),
            ]);

            $peminjaman->update(['status' => 'dikembalikan']);

            if ($request->kondisi_kembali !== 'Hilang') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    if ($detail->alat) {
                        $detail->alat->increment('stok', $detail->jumlah);
                    }
                }
            }

            DB::commit();
            return redirect()->route('petugas.pengembalian.index')->with(
                'success',
                'Pengembalian berhasil dicatat. Keterlambatan: ' . $hariTerlambat . ' hari, total denda: Rp ' . number_format($denda, 0, ',', '.') . '.'
            );
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function tolakPeminjaman($id)
    {
        try {
            $peminjaman = Peminjaman::findOrFail($id);
            
            //pastikan statusnya memang diajukan
            if ($peminjaman->status == 'diajukan') {
                $peminjaman->update(['status' => 'ditolak']);
                return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil ditolak');
            } 

            return redirect()->back()->with('error', 'Status peminjaman sudah berubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function indexLaporan()
    {
        $laporan = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->latest()
            ->get();

        // Arahkan ke view laporan yang baru dibuat
        return view('petugas.laporan.index', [
            'peminjaman' => $laporan
        ]);
    }
    public function laporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');
        $search = $request->input('search');
        $laporans = $this->queryLaporan($request)->get();

        return view('petugas.laporan.index', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal', 'search'));
    }

    // Menampilkan halaman khusus cetak (print preview)
    public function cetakLaporan(Request $request)
    {
        $status = $request->input('status');
        $dari_tanggal = $request->input('dari_tanggal');
        $sampai_tanggal = $request->input('sampai_tanggal');
        $search = $request->input('search');
        $laporans = $this->queryLaporan($request)->get();

        return view('petugas.laporan.cetak', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal', 'search'));
    }

    private function queryLaporan(Request $request): Builder
    {
        return Peminjaman::with(['user', 'detailPinjams.alat', 'pengembalian'])
            ->when($request->input('status'), function (Builder $query, $status) {
                if (in_array($status, ['dikembalikan', 'selesai'], true)) {
                    $query->whereIn('status', ['dikembalikan', 'selesai']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('dari_tanggal'), function (Builder $query, $tanggal) {
                $query->whereDate('tgl_pinjam', '>=', $tanggal);
            })
            ->when($request->input('sampai_tanggal'), function (Builder $query, $tanggal) {
                $query->whereDate('tgl_pinjam', '<=', $tanggal);
            })
            ->when($request->input('search'), function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('detailPinjams.alat', function (Builder $alatQuery) use ($search) {
                        $alatQuery->where('nama_alat', 'like', "%{$search}%");
                    });
                });
            })
            ->latest();
    }

    private function applyPeminjamanFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->input('status'), function (Builder $query, $status) {
                if (in_array($status, ['dikembalikan', 'selesai'], true)) {
                    $query->whereIn('status', ['dikembalikan', 'selesai']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($request->input('dari_tanggal'), function (Builder $query, $tanggal) {
                $query->whereDate('tgl_pinjam', '>=', $tanggal);
            })
            ->when($request->input('sampai_tanggal'), function (Builder $query, $tanggal) {
                $query->whereDate('tgl_pinjam', '<=', $tanggal);
            })
            ->when($request->input('search'), function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->whereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('detailPinjam.alat', function (Builder $alatQuery) use ($search) {
                        $alatQuery->where('nama_alat', 'like', "%{$search}%");
                    });
                });
            });
    }
}