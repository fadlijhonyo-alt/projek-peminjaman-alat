@extends('layouts.app')

@section('title', 'Persetujuan Peminjaman - Dashboard Petugas')
@section('header-title', 'Daftar Pengajuan Peminjaman Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Menunggu Verifikasi Persetujuan</h3>
            <form action="{{ route('petugas.peminjaman.index') }}" method="GET" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-5">
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
                    <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau alat..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Status</option>
                        <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                        <option value="telat" {{ request('status') === 'telat' ? 'selected' : '' }}>Telat</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div>
                    <label for="dari_tanggal" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input id="dari_tanggal" type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="sampai_tanggal" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input id="sampai_tanggal" type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Filter</button>
                    <a href="{{ route('petugas.peminjaman.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Tanggal Pinjam</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($peminjaman as $item)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_pinjam }}</td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_kembali_plan }}</td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjams as $detail)
                                        <li>
                                            <span class="font-semibold">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                            (Jumlah: {{ $detail->jumlah }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b text-center">
                                @if($item->status == 'diajukan')
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('petugas.peminjaman.setujui', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Setujui peminjaman alat ini?')"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                                Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('petugas.peminjaman.tolak', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Tolak peminjaman alat ini?')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded
                                        {{ $item->status == 'ditolak' ? 'text-red-700 bg-red-50' : 'text-blue-600 bg-blue-50' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Tidak ada pengajuan peminjaman baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjaman->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $peminjaman->links() }}
            </div>
        @endif
    </div>
@endsection