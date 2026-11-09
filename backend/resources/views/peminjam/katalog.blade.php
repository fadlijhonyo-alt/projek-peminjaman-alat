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

        <form action="{{ route('peminjam.katalog') }}" method="GET" class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 md:flex-row md:items-end">
            <div class="flex-1">
                <label for="search" class="mb-1 block text-xs font-semibold text-slate-600">Cari Alat</label>
                <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Nama atau deskripsi alat..."
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="md:w-64">
                <label for="kategori_id" class="mb-1 block text-xs font-semibold text-slate-600">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) $kategoriId === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900">Cari</button>
            @if($search !== '' || $kategoriId)
                <a href="{{ route('peminjam.katalog') }}" class="rounded-md border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-100">Reset</a>
            @endif
        </form>

        <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" class="space-y-5">
            @csrf

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="grid grid-cols-1 gap-4 border-b border-slate-200 bg-slate-50 p-5 sm:grid-cols-[1fr_auto] sm:items-end">
                    <div>
                        <label for="tgl_kembali_plan" class="mb-2 block text-sm font-semibold text-slate-700">Rencana Tanggal Kembali</label>
                        <input type="date" id="tgl_kembali_plan" name="tgl_kembali_plan" min="{{ now()->addDay()->toDateString() }}"
                               value="{{ old('tgl_kembali_plan') }}" required
                               class="w-full max-w-sm border border-slate-300 rounded-md px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('tgl_kembali_plan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-sm text-slate-500">{{ $alats->count() }} alat tersedia untuk dipilih</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                                <th class="border-b px-4 py-3">Pilih</th>
                                <th class="border-b px-4 py-3">Gambar</th>
                                <th class="border-b px-4 py-3">Nama Alat</th>
                                <th class="border-b px-4 py-3">Kategori</th>
                                <th class="border-b px-4 py-3">Deskripsi</th>
                                <th class="border-b px-4 py-3">Stok</th>
                                <th class="border-b px-4 py-3">Jumlah Pinjam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                    @forelse($alats as $alat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <input id="alat-{{ $alat->id }}" type="checkbox" name="alat_id[{{ $alat->id }}]" value="{{ $alat->id }}"
                                           @checked(old('alat_id.' . $alat->id))
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-3">
                                @if($alat->gambar)
                                    <img src="{{ asset(str_starts_with($alat->gambar, 'storage/') ? $alat->gambar : 'storage/' . $alat->gambar) }}"
                                         alt="Foto {{ $alat->nama_alat }}" loading="lazy" class="h-12 w-12 rounded-md object-cover">
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-md bg-slate-100 text-[10px] text-slate-400">
                                        Tidak ada
                                    </div>
                                @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $alat->nama_alat }}</td>
                                <td class="px-4 py-3">{{ $alat->kategori->nama_kategori ?? '-' }}</td>
                                <td class="max-w-xs px-4 py-3 text-slate-500">{{ $alat->deskripsi ?: '-' }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $alat->stok }}</td>
                                <td class="px-4 py-3">
                                        <input id="jumlah-{{ $alat->id }}" type="number" name="jumlah[{{ $alat->id }}]" min="1" max="{{ $alat->stok }}"
                                               value="{{ old('jumlah.' . $alat->id, 1) }}"
                                               class="w-20 rounded-md border border-slate-300 px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </td>
                            </tr>
                    @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-500">Tidak ada alat yang sesuai dengan pencarian.</td>
                            </tr>
                    @endforelse
                        </tbody>
                    </table>
                </div>

                @error('alat_id') <p class="px-5 pb-3 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('jumlah.*') <p class="px-5 pb-3 text-sm text-rose-600">{{ $message }}</p> @enderror

                <div class="flex justify-end border-t border-slate-200 bg-slate-50 p-5">
                    <button type="submit" class="rounded-md bg-emerald-600 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        Ajukan Peminjaman
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
