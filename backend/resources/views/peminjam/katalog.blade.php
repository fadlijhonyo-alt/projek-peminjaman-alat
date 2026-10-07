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

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($alats as $alat)
                        <article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-emerald-300 hover:shadow-md">
                            <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                                @if($alat->gambar)
                                    <img src="{{ asset(str_starts_with($alat->gambar, 'storage/') ? $alat->gambar : 'storage/' . $alat->gambar) }}"
                                         alt="Foto {{ $alat->nama_alat }}" loading="lazy" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 via-emerald-50 to-blue-100 text-sm font-medium text-slate-400">
                                        Foto belum tersedia
                                    </div>
                                @endif
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase text-emerald-700">{{ $alat->kategori->nama_kategori ?? 'Tanpa kategori' }}</p>
                                        <h3 class="mt-1 font-bold text-slate-900">{{ $alat->nama_alat }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-md bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">{{ $alat->stok }} tersedia</span>
                                </div>
                                <p class="min-h-12 text-sm leading-5 text-slate-600">{{ $alat->deskripsi ?: 'Deskripsi alat belum tersedia.' }}</p>
                                <div class="flex items-end justify-between gap-3 border-t border-slate-100 pt-3">
                                    <label for="alat-{{ $alat->id }}" class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input id="alat-{{ $alat->id }}" type="checkbox" name="alat_id[{{ $alat->id }}]" value="{{ $alat->id }}"
                                               @checked(old('alat_id.' . $alat->id))
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        Pilih alat
                                    </label>
                                    <div>
                                        <label for="jumlah-{{ $alat->id }}" class="mb-1 block text-xs font-medium text-slate-500">Jumlah</label>
                                        <input id="jumlah-{{ $alat->id }}" type="number" name="jumlah[{{ $alat->id }}]" min="1" max="{{ $alat->stok }}"
                                               value="{{ old('jumlah.' . $alat->id, 1) }}"
                                               class="w-20 rounded-md border border-slate-300 px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-md border border-dashed border-slate-300 px-5 py-12 text-center text-sm text-slate-500">
                            Tidak ada alat yang tersedia saat ini.
                        </div>
                    @endforelse
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
