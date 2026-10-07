@extends('layouts.app')

@section('title', 'Dashboard Peminjam - Sistem Peminjaman')
@section('header-title', 'Dashboard Peminjam')

@section('content')
    <div class="space-y-6 pb-6">
        <section class="relative overflow-hidden rounded-lg bg-slate-950 px-6 py-7 text-white shadow-sm sm:px-8">
            <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-l from-emerald-500/20 to-transparent"></div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-emerald-300">Panel Peminjam</p>
                    <h2 class="mt-2 text-2xl font-bold">Halo, {{ auth()->user()->name }}</h2>
                    <p class="mt-1 text-sm text-slate-300">Pantau pengajuan dan status peminjaman alat Anda.</p>
                </div>
                <a href="{{ route('peminjam.katalog') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-emerald-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">
                    Ajukan Peminjaman
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-blue-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pinjaman Aktif</p>
                <div class="mt-3 flex items-end justify-between">
                    <p class="text-3xl font-bold text-slate-900">{{ $jumlahAktif }}</p>
                    <span class="rounded-md bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-800">Dipinjam / Telat</span>
                </div>
            </div>
            <div class="rounded-lg border border-yellow-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Menunggu Persetujuan</p>
                <div class="mt-3 flex items-end justify-between">
                    <p class="text-3xl font-bold text-slate-900">{{ $jumlahMenunggu }}</p>
                    <span class="rounded-md bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">Diajukan</span>
                </div>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Dikembalikan</p>
                <div class="mt-3 flex items-end justify-between">
                    <p class="text-3xl font-bold text-slate-900">{{ $jumlahDikembalikan }}</p>
                    <span class="rounded-md bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Selesai</span>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="font-bold text-slate-900">Aktivitas Peminjaman Terbaru</h3>
                    <p class="mt-1 text-xs text-slate-500">Pembaruan pengajuan terakhir pada akun Anda</p>
                </div>
                <a href="{{ route('peminjam.riwayat') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Lihat riwayat</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($aktivitasTerbaru as $item)
                    @php
                        $statusDikembalikan = in_array($item->status, ['dikembalikan', 'selesai'], true);
                        $statusClass = match ($item->status) {
                            'diajukan' => 'bg-yellow-100 text-yellow-800',
                            'dipinjam' => 'bg-blue-100 text-blue-800',
                            'dikembalikan', 'selesai' => 'bg-emerald-100 text-emerald-800',
                            'ditolak', 'telat' => 'bg-rose-100 text-rose-800',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-slate-800">
                                @forelse($item->detailPinjams as $detail)
                                    {{ $detail->alat->nama_alat ?? 'Alat dihapus' }}{{ !$loop->last ? ', ' : '' }}
                                @empty
                                    Pengajuan peminjaman #{{ $item->id }}
                                @endforelse
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Diajukan {{ $item->created_at->format('d-m-Y H:i') }}</p>
                        </div>
                        <span class="w-fit rounded-md px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                            {{ $statusDikembalikan ? 'Dikembalikan' : ucfirst($item->status) }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-500">Belum ada aktivitas peminjaman.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
