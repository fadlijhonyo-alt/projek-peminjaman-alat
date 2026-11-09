@extends('layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Profil Saya')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <p class="text-sm font-medium uppercase tracking-wide text-indigo-600">Akun Saya</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-800">Profil Saya</h2>
            <p class="mt-1 text-sm text-slate-500">Informasi akun yang sedang digunakan.</p>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-center gap-4 border-b border-slate-100 bg-slate-50 p-6 sm:flex-row">
                @if($user->foto_profile)
                    <img src="{{ asset('storage/' . ltrim($user->foto_profile, '/')) }}"
                         alt="Foto profil {{ $user->name }}"
                         class="h-20 w-20 rounded-full border-4 border-white object-cover shadow-sm">
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="text-center sm:text-left">
                    <h3 class="text-xl font-bold text-slate-800">{{ $user->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ ucfirst($user->role) }}</p>
                </div>
            </div>

            <dl class="divide-y divide-slate-100">
                <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-slate-500">Nama</dt>
                    <dd class="text-sm text-slate-800 sm:col-span-2">{{ $user->name }}</dd>
                </div>
                <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-slate-500">Email</dt>
                    <dd class="text-sm text-slate-800 sm:col-span-2">{{ $user->email }}</dd>
                </div>
                <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-slate-500">Role</dt>
                    <dd class="text-sm capitalize text-slate-800 sm:col-span-2">{{ $user->role }}</dd>
                </div>
                <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-slate-500">Nomor HP</dt>
                    <dd class="text-sm text-slate-800 sm:col-span-2">{{ $user->no_hp ?: '-' }}</dd>
                </div>
                <div class="grid gap-1 px-6 py-4 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-slate-500">Alamat</dt>
                    <dd class="whitespace-pre-line text-sm text-slate-800 sm:col-span-2">{{ $user->alamat ?: '-' }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
