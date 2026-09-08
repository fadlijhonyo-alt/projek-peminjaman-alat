@extends('layouts.app')

@section('title', 'Dashboard Peminjam - Sistem Peminjaman')
@section('header-title', 'Dashboard Peminjam')

@section('content')
    <div class="space-y-6">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm">
            Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai
            <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pinjaman Aktif</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">3</h3>
                    </div>
                    <div class="bg-blue-100 text-blue-700 p-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Menunggu Persetujuan</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">2</h3>
                    </div>
                    <div class="bg-amber-100 text-amber-700 p-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Selesai</p>
                        <h3 class="mt-2 text-3xl font-bold text-slate-800">5</h3>
                    </div>
                    <div class="bg-emerald-100 text-emerald-700 p-3 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 3"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-200 bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Aksi Cepat</h3>
            </div>
            <div class="p-5 space-y-3">
                <a href="{{ route('peminjam.katalog') }}" class="flex items-center justify-between p-3 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                    <span class="font-medium">Ajukan Peminjaman</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </a>

                <a href="{{ route('peminjam.riwayat') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                    <span class="font-medium">Lihat Riwayat Pinjam</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
@endsection
