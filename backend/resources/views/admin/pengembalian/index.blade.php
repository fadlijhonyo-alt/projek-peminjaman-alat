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

    <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="p-5 border-b border-indigo-50 grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2">
            <label for="search" class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
            <input id="search" type="search" name="search" value="{{ $search }}" placeholder="Nama, email, alat, atau status..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="status" class="block text-xs font-medium text-slate-600 mb-1">Status</label>
            <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Status</option>
                <option value="dipinjam" {{ $status === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="telat" {{ $status === 'telat' ? 'selected' : '' }}>Telat</option>
                <option value="dikembalikan" {{ $status === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
            </select>
        </div>
        <div>
            <label for="dari_tanggal" class="block text-xs font-medium text-slate-600 mb-1">Dari tanggal pinjam</label>
            <input id="dari_tanggal" type="date" name="dari_tanggal" value="{{ $dariTanggal }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label for="sampai_tanggal" class="block text-xs font-medium text-slate-600 mb-1">Sampai tanggal pinjam</label>
            <input id="sampai_tanggal" type="date" name="sampai_tanggal" value="{{ $sampaiTanggal }}"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="md:col-span-5 flex justify-end gap-2">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Cari / Filter</button>
            @if($search || $status || $dariTanggal || $sampaiTanggal)
                <a href="{{ route('admin.pengembalian.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold">Reset</a>
            @endif
        </div>
    </form>

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
                        $hariIni = $pinjam->pengembalian
                            ? \Carbon\Carbon::parse($pinjam->pengembalian->tgl_kembali)
                            : \Carbon\Carbon::today();

                        $terlambat = $hariIni->gt($tanggalKembali);
                        $hariTerlambat = $terlambat
                            ? $tanggalKembali->diffInDays($hariIni)
                            : 0;

                        $dendaPerHari = 5000;
                        $dendaKeterlambatan = $hariTerlambat * $dendaPerHari;
                        $perkiraanDenda = $pinjam->pengembalian
                            ? $pinjam->pengembalian->denda
                            : $dendaKeterlambatan;
                        $dendaKondisi = $pinjam->pengembalian
                            ? max(0, $perkiraanDenda - $dendaKeterlambatan)
                            : 0;
                        $kondisiDendaLabel = match ($pinjam->pengembalian?->kondisi_kembali) {
                            'Hilang' => 'Barang hilang',
                            'Rusak Ringan' => 'Kerusakan ringan',
                            'Rusak Berat' => 'Kerusakan berat',
                            default => 'Denda tambahan',
                        };
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

                                @if($hariTerlambat > 0)
                                    <div class="mt-0.5 text-[11px] text-slate-400">
                                        Terlambat {{ $hariTerlambat }} hari · Rp {{ number_format($dendaPerHari, 0, ',', '.') }}/hari
                                    </div>
                                @endif
                                @if($pinjam->pengembalian && ($pinjam->pengembalian->kondisi_kembali !== 'Baik' || $dendaKondisi > 0))
                                    <div class="mt-0.5 text-[11px] text-slate-500">
                                        {{ $kondisiDendaLabel }}: Rp {{ number_format($dendaKondisi, 0, ',', '.') }}
                                    </div>
                                @endif

                            @else

                                <span class="font-semibold text-emerald-600 text-xs">
                                    Rp 0
                                </span>

                            @endif

                        </td>


                        {{-- Aksi --}}
                        <td class="py-4 px-4 text-center">

                            @if(in_array($pinjam->status, ['dipinjam', 'telat'], true))
                                <a href="{{ route('admin.pengembalian.create', $pinjam->id) }}"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold shadow-sm shadow-indigo-600/25 transition inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Kembalikan
                                </a>
                            @else
                                <span class="text-xs font-medium text-slate-500">
                                    Dikembalikan {{ $pinjam->pengembalian?->tgl_kembali ? \Carbon\Carbon::parse($pinjam->pengembalian->tgl_kembali)->format('d-m-Y') : '' }}
                                </span>
                            @endif

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

    <div class="p-4 border-t border-indigo-50 bg-gray-50">
        {{ $peminjamans->links() }}
    </div>

</div>

@endsection