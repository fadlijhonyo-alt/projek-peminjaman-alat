@extends('layouts.app')

@section('title', 'Riwayat Peminjaman - Peminjam')
@section('header-title', 'Riwayat Peminjaman')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">Peminjam</p>
                <h2 class="text-2xl font-bold text-slate-800">Riwayat Peminjaman</h2>
            </div>
            <a href="{{ route('peminjam.katalog') }}"
               class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl font-medium shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Katalog
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-100 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Tanggal Pinjam</th>
                            <th class="px-5 py-3">Rencana Kembali</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Alat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($peminjamans as $index => $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">{{ $index + 1 }}</td>
                                <td class="px-5 py-4">{{ $item->tgl_pinjam }}</td>
                                <td class="px-5 py-4">{{ $item->tgl_kembali_plan }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                        @if($item->status == 'diajukan') bg-amber-100 text-amber-700
                                        @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-700
                                        @elseif($item->status == 'selesai') bg-emerald-100 text-emerald-700
                                        @else bg-rose-100 text-rose-700 @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($item->detailPinjams as $detail)
                                            <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                    Belum ada riwayat peminjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
