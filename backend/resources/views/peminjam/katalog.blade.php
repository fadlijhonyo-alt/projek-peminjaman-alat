@extends('layouts.app')

@section('title', 'Katalog Alat - Peminjam')
@section('header-title', 'Katalog Alat')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">Peminjam</p>
                <h2 class="text-2xl font-bold text-slate-800">Katalog Alat Tersedia</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('peminjam.dashboard') }}"
                   class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl font-medium shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('peminjam.riwayat') }}"
                   class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl font-medium shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Riwayat Pinjam
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" class="space-y-5">
            @csrf

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tgl_kembali_plan" class="block text-sm font-medium text-slate-700 mb-2">
                                Rencana Tanggal Kembali
                            </label>
                            <input type="date" id="tgl_kembali_plan" name="tgl_kembali_plan" required
                                   class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-100 text-slate-700 uppercase text-xs">
                            <tr>
                                <th class="px-5 py-3">Pilih</th>
                                <th class="px-5 py-3">Nama Alat</th>
                                <th class="px-5 py-3">Kategori</th>
                                <th class="px-5 py-3">Stok</th>
                                <th class="px-5 py-3">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($alats as $alat)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-4 text-center">
                                        <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}"
                                               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                                    </td>
                                    <td class="px-5 py-4 font-medium text-slate-800">{{ $alat->nama_alat }}</td>
                                    <td class="px-5 py-4">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            {{ $alat->stok }} pcs
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <input type="number" name="jumlah[]" min="1" max="{{ $alat->stok }}" value="1"
                                               class="w-24 border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                        Tidak ada alat yang tersedia saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-sm transition">
                        Ajukan Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
