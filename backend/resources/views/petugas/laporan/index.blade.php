@extends('layouts.app')

@section('title', 'Laporan Peminjaman - Dashboard Petugas')
@section('header-title', 'Laporan Peminjaman Alat')

@section('content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <!-- Header & Tombol Cetak -->
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-800">Laporan Rekapitulasi Peminjaman</h3>

            <div class="flex items-center gap-2">
                <a href="{{ route('petugas.laporan.cetak', request()->query()) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Laporan
                </a>
            </div>
        </div>

        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <form action="{{ route('petugas.laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="dari_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input id="dari_tanggal" type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="sampai_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input id="sampai_tanggal" type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Filter
                    </button>
                    <a href="{{ route('petugas.laporan.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Laporan -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Peminjam</th>
                        <th class="px-6 py-3">Tgl Pinjam</th>
                        <th class="px-6 py-3">Rencana Kembali</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Alat yang Dipinjam</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($laporans as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->tgl_pinjam }}</td>
                            <td class="px-6 py-4">{{ $item->tgl_kembali_plan }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                    @if($item->status == 'diajukan') bg-amber-100 text-amber-800
                                    @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-800
                                    @elseif($item->status == 'selesai') bg-emerald-100 text-emerald-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="list-disc list-inside">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data laporan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection