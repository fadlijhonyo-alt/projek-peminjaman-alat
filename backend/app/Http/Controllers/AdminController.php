<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\Pengembalian;
use Illuminate\Support\Facades\DB;
use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // Menampilkan dashboard admin dan log aktivitas
    public function index(Request $request)
    {
        $search = $request->input('search');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        $logs = LogAktivitas::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('aktivitas', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($dariTanggal, function ($query, $tanggal) {
                $query->whereDate('created_at', '>=', $tanggal);
            })
            ->when($sampaiTanggal, function ($query, $tanggal) {
                $query->whereDate('created_at', '<=', $tanggal);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', compact('logs', 'search', 'dariTanggal', 'sampaiTanggal'));
    }

    // CRUD Alat: Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('status_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                        $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    // Menampilkan form tambah alat
    public function createAlat()
    {
        $kategori = Kategori::all();
        return view('admin.alat.create', compact('kategori'));
    }

    // Menyimpan alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except('gambar');

        // Handle Upload Gambar jika ada
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil ditambahkan.');
    }

    // Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();
        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    // Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Update Gambar jika ada file baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
                unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil diperbarui.');
    }

    // Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Hapus file gambar fisik jika ada
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }

    // CRUD User: Manajemen user (admin, petugas, peminjam)
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    // Menyimpan user baru ke database
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:20',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->hasFile('foto_profile')) {
            $data['foto_profile'] = $request->file('foto_profile')->store('profiles', 'public');
        }

        User::create($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user.edit', compact('user'));
    }

    // Memperbarui data user
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,petugas,peminjam',
            'no_hp' => 'nullable|string|max:20',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $user->is(auth()->user()) ? $user->role : $request->role,
            'no_hp' => $request->no_hp,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto_profile')) {
            $fotoProfile = $request->file('foto_profile')->store('profiles', 'public');

            if ($fotoProfile) {
                if ($user->foto_profile) {
                    Storage::disk('public')->delete($user->foto_profile);
                }

                $data['foto_profile'] = $fotoProfile;
            }
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'Data user berhasil diperbarui.');
    }

    // Menghapus user
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        abort_if($user->is(auth()->user()), 403, 'Anda tidak dapat menghapus akun sendiri.');

        if ($user->foto_profile) {
            Storage::disk('public')->delete($user->foto_profile);
        }

        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }

    // CRUD Kategori
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
            return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    // Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

        if ($kategori->alat()->count() > 0) {
            return redirect()->route('admin.kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
        }

        $kategori->delete();

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // 1. Menampilkan daftar peminjaman ($peminjaman)
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjaman', 'search'));
    }

    // 2. Menampilkan form tambah peminjaman ($user dan $alat)
    public function createPeminjaman()
    {
        $user = User::where('role', 'peminjam')->get(); 
        $alat = Alat::where('stok', '>', 0)->get();

        return view('admin.peminjaman.create', compact('user', 'alat'));
    }

    // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'tgl_pinjam'       => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id'          => 'required|array',
            'alat_id.*'        => 'exists:alat,id', 
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::create([
                'user_id'          => $request->user_id,
                'tgl_pinjam'       => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status'           => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];
                $alatData = Alat::findOrFail($alatId);

                if ($alatData->stok < $jumlahPinjam) {
                    throw new \Exception("Stok alat '{$alatData->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id'       => $alatId,
                    'jumlah'        => $jumlahPinjam,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // 4. Memperbarui status peminjaman
    public function updateStatusPeminjaman(Request $request, $id)
    {
        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,dikembalikan,telat',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $peminjaman->status;
            $statusBaru = $request->status;

            if ($statusLama != 'dipinjam' && $statusBaru == 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alatData = $detail->alat;
                    if ($alatData->stok < $detail->jumlah) {
                        throw new \Exception("Stok alat {$alatData->nama_alat} tidak mencukupi untuk dipinjam.");
                    }
                    $alatData->decrement('stok', $detail->jumlah);
                }
            } elseif ($statusLama == 'dipinjam' && in_array($statusBaru, ['dikembalikan', 'selesai'])) {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $statusBaru]);

            DB::commit();
            return redirect()->route('admin.peminjaman.index')->with('success', 'Status peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // 5. Menghapus data peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

        if ($peminjaman->status == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                if ($detail->alat) {
                    $detail->alat->increment('stok', $detail->jumlah);
                }
            }
        }

        $peminjaman->delete();

        return redirect()->route('admin.peminjaman.index')->with('success', 'Data peminjaman berhasil dihapus.');
    }

   
   // 6. Menampilkan daftar pengembalian
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        // Update status otomatis untuk data yang sudah lewat dari tanggal rencana kembali
        $peminjamanList = Peminjaman::where('status', 'dipinjam')->get();
        foreach ($peminjamanList as $pinjam) {
            if (Carbon::today()->gt(Carbon::parse($pinjam->tgl_kembali_plan))) {
                $pinjam->update(['status' => 'telat']);
            }
        }

        $pengembalian = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->whereIn('status', ['dipinjam', 'telat', 'dikembalikan'])
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($dariTanggal, function ($query, $tanggal) {
                $query->whereDate('tgl_pinjam', '>=', $tanggal);
            })
            ->when($sampaiTanggal, function ($query, $tanggal) {
                $query->whereDate('tgl_pinjam', '<=', $tanggal);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('status', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('detailPinjam.alat', function ($alatQuery) use ($search) {
                            $alatQuery->where('nama_alat', 'like', "%{$search}%");
                        });
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Kita kirimkan $peminjamans agar file blade baris 76 tidak error
        $peminjamans = $pengembalian;

        return view('admin.pengembalian.index', compact(
            'pengembalian',
            'peminjamans',
            'search',
            'status',
            'dariTanggal',
            'sampaiTanggal'
        ));
    }

    // 7. Proses Pengembalian
    public function kembalikan(Request $request, $id)
    {
        $validated = $request->validate([
            'kondisi_kembali' => 'required|in:Baik,Rusak Ringan,Rusak Berat,Hilang',
            'denda_tambahan' => 'required|integer|min:0|max:100000000',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::with('detailPinjam.alat')->lockForUpdate()->findOrFail($id);

            if (!in_array($peminjaman->status, ['dipinjam', 'telat'], true)) {
                DB::rollBack();
                return back()->with('error', 'Peminjaman ini sudah dikembalikan.');
            }

            $tanggalRencana = Carbon::parse($peminjaman->tgl_kembali_plan);
            $tanggalKembali = Carbon::today();

            $hariTerlambat = $tanggalKembali->gt($tanggalRencana) ? $tanggalRencana->diffInDays($tanggalKembali) : 0;
            $dendaKeterlambatan = $hariTerlambat * 5000;
            $denda = $dendaKeterlambatan + (int) $validated['denda_tambahan'];

            if ($validated['kondisi_kembali'] !== 'Hilang') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    if ($detail->alat) {
                        $detail->alat->increment('stok', $detail->jumlah);
                    }
                }
            }

            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => $tanggalKembali,
                'kondisi_kembali' => $validated['kondisi_kembali'],
                'denda' => $denda,
                'petugas_id' => auth()->id(),
            ]);
            $peminjaman->update(['status' => 'dikembalikan']);

            DB::commit();
            return redirect()->route('admin.pengembalian.index')
                ->with('success', 'Pengembalian berhasil dicatat. Keterlambatan: ' . $hariTerlambat . ' hari, total denda: Rp ' . number_format($denda, 0, ',', '.') . '.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pengembalian gagal: ' . $e->getMessage());
        }
    }

    // 8. Form Pengembalian
    public function createPengembalian($id)
    {
        $peminjaman = Peminjaman::with(['user', 'detailPinjam.alat'])->findOrFail($id);

        if (!in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return back()->with('error', 'Peminjaman ini sudah selesai dikembalikan.');
        }

        return view('admin.pengembalian.create', compact('peminjaman'));
    }
}