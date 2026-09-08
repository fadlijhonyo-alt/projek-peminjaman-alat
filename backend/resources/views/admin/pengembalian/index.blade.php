@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Manajemen Pengembalian')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl shadow-sm text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-indigo-100/80">

    {{-- Header --}}
    <div class="p-5 border-b border-indigo-50 bg-indigo-50/20">

        <h3 class="font-bold text-slate-800">
            Daftar Pengembalian Alat
        </h3>

        <p class="text-xs text-slate-500 mt-0.5">
            Kelola pengembalian alat yang masih dipinjam secara manual.
        </p>

    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto">

        <table class="w-full text-left border-collapse">

            <thead>
                <tr class="bg-indigo-50/40 text-slate-400 text-xs uppercase tracking-wider border-b border-indigo-50">

                    <th class="py-3.5 px-4 font-semibold">
                        Peminjam
                    </th>

                    <th class="py-3.5 px-4 font-semibold">
                        Alat
                    </th>

                    <th class="py-3.5 px-4 font-semibold">
                        Tanggal Pinjam
                    </th>

                    <th class="py-3.5 px-4 font-semibold">
                        Rencana Kembali
                    </th>

                    <th class="py-3.5 px-4 font-semibold">
                        Keterlambatan
                    </th>

                    <th class="py-3.5 px-4 font-semibold">
                        Denda
                    </th>

                    <th class="py-3.5 px-4 font-semibold text-center">
                        Aksi
                    </th>

                </tr>
            </thead>

            <tbody class="text-slate-700 text-sm divide-y divide-slate-100">

                @forelse($peminjamans as $pinjam)

                    @php
                        $tanggalKembali = \Carbon\Carbon::parse($pinjam->tgl_kembali_plan);
                        $hariIni = \Carbon\Carbon::today();

                        $terlambat = $hariIni->gt($tanggalKembali);
                        $hariTerlambat = $terlambat
                            ? $tanggalKembali->diffInDays($hariIni)
                            : 0;

                        $dendaPerHari = 5000;
                        $perkiraanDenda = $hariTerlambat * $dendaPerHari;
                    @endphp

                    <tr class="hover:bg-indigo-50/30 transition align-top">

                        {{-- Peminjam --}}
                        <td class="py-4 px-4 font-medium text-slate-800">

                            <div class="text-slate-900 font-semibold">
                                {{ $pinjam->user->name ?? 'User Dihapus' }}
                            </div>

                            @if($pinjam->user)
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $pinjam->user->email }}
                                </div>
                            @endif

                        </td>


                        {{-- Alat --}}
                        <td class="py-4 px-4">

                            <div class="space-y-1.5">

                                @foreach($pinjam->detailPinjam as $detail)

                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-700">
                                            {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                        </span>

                                        <span class="text-xs bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded-full font-semibold">
                                            {{ $detail->jumlah }} pcs
                                        </span>
                                    </div>

                                @endforeach

                            </div>

                        </td>


                        {{-- Tanggal Pinjam --}}
                        <td class="py-4 px-4 text-xs text-slate-500">
                            <span class="font-medium text-slate-700">
                                {{ \Carbon\Carbon::parse($pinjam->tgl_pinjam)->format('d-m-Y') }}
                            </span>
                        </td>


                        {{-- Rencana Kembali --}}
                        <td class="py-4 px-4">

                            <div class="font-medium text-slate-800 text-xs">
                                {{ $tanggalKembali->format('d-m-Y') }}
                            </div>

                            @if($terlambat)

                                <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-50 text-red-600 border border-red-100">
                                    Terlambat
                                </span>

                            @else

                                <span class="inline-block mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                                    Belum terlambat
                                </span>

                            @endif

                        </td>


                        {{-- Keterlambatan --}}
                        <td class="py-4 px-4">

                            @if($hariTerlambat > 0)

                                <span class="font-semibold text-red-600 text-xs">
                                    {{ $hariTerlambat }} hari
                                </span>

                            @else

                                <span class="text-slate-400 text-xs">
                                    0 hari
                                </span>

                            @endif

                        </td>


                        {{-- Denda --}}
                        <td class="py-4 px-4">

                            @if($perkiraanDenda > 0)

                                <span class="font-bold text-red-600 text-xs">
                                    Rp {{ number_format($perkiraanDenda, 0, ',', '.') }}
                                </span>

                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    Rp {{ number_format($dendaPerHari, 0, ',', '.') }}/hari
                                </div>

                            @else

                                <span class="font-semibold text-emerald-600 text-xs">
                                    Rp 0
                                </span>

                            @endif

                        </td>


                        {{-- Aksi --}}
                        <td class="py-4 px-4 text-center">

                            <form
                                action="{{ route('admin.pengembalian.kembalikan', $pinjam->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin alat ini sudah dikembalikan?')"
                                class="inline-block"
                            >
                                @csrf
                                @method('PUT')

                                <a href="{{ route('admin.pengembalian.create', $pinjam->id) }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold shadow-sm shadow-indigo-600/25 transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Kembalikan
                                </a>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="py-12 text-center text-slate-400">

                            <div class="text-sm font-semibold text-slate-600">
                                Tidak ada peminjaman yang perlu dikembalikan.
                            </div>

                            <div class="text-xs mt-1 text-slate-400">
                                Semua alat sudah dikembalikan.
                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection