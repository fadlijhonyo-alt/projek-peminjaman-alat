@extends('layouts.app')

@section('title', 'Laporan Peminjaman - Dashboard Petugas')
@section('header-title', 'Laporan Peminjaman Alat')

@section('content')
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <!-- Header & Tombol Cetak -->
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-800">Laporan Rekapitulasi Peminjaman</h3>
            
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Laporan
            </button>
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
                    @forelse($peminjaman as $index => $item)
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