@extends('layouts.app')

@section('title', 'Pengembalian Peminjaman Alat - Panel Petugas')
@section('header-title', 'Pengembalian Peminjaman Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @error('kondisi_kembali')
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 bg-gray-50 p-5">
            <h2 class="font-bold text-gray-800">Pengembalian Peminjaman Alat</h2>
            <p class="mt-1 text-xs text-gray-500">Periksa keterlambatan dan denda sebelum mencatat pengembalian.</p>
            <form action="{{ route('petugas.pengembalian.index') }}" method="GET" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-5">
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
                    <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Nama, email, atau alat..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="telat" {{ request('status') === 'telat' ? 'selected' : '' }}>Telat</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>
                <div>
                    <label for="dari_tanggal" class="mb-1 block text-sm font-medium text-gray-700">Dari Tanggal</label>
                    <input id="dari_tanggal" type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="sampai_tanggal" class="mb-1 block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                    <input id="sampai_tanggal" type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Filter</button>
                    <a href="{{ route('petugas.pengembalian.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b bg-gray-100 text-xs uppercase text-gray-600">
                        <th class="px-4 py-3">Peminjam</th>
                        <th class="px-4 py-3">Alat</th>
                        <th class="px-4 py-3">Rencana Kembali</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Keterlambatan</th>
                        <th class="px-4 py-3">Denda</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($pengembalian as $pinjam)
                        @php
                            $tanggalRencana = \Carbon\Carbon::parse($pinjam->tgl_kembali_plan);
                            $tanggalAcuan = $pinjam->pengembalian
                                ? \Carbon\Carbon::parse($pinjam->pengembalian->tgl_kembali)
                                : \Carbon\Carbon::today();
                            $hariTerlambat = $tanggalAcuan->gt($tanggalRencana)
                                ? $tanggalRencana->diffInDays($tanggalAcuan)
                                : 0;
                            $dendaKeterlambatan = $hariTerlambat * 5000;
                            $denda = $pinjam->pengembalian
                                ? $pinjam->pengembalian->denda
                                : $dendaKeterlambatan;
                            $dendaKondisi = $pinjam->pengembalian
                                ? max(0, $denda - $dendaKeterlambatan)
                                : 0;
                            $kondisiDendaLabel = match ($pinjam->pengembalian?->kondisi_kembali) {
                                'Hilang' => 'Barang hilang',
                                'Rusak Ringan' => 'Kerusakan ringan',
                                'Rusak Berat' => 'Kerusakan berat',
                                default => 'Denda tambahan',
                            };
                        @endphp

                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-900">{{ $pinjam->user->name ?? 'User Dihapus' }}</div>
                                @if($pinjam->user)
                                    <div class="mt-0.5 text-xs text-gray-500">{{ $pinjam->user->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <ul class="space-y-1">
                                    @foreach($pinjam->detailPinjam as $detail)
                                        <li>
                                            {{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}
                                            <span class="text-xs text-gray-500">({{ $detail->jumlah }} pcs)</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs">
                                {{ $tanggalRencana->format('d-m-Y') }}
                                @if($hariTerlambat > 0)
                                    <div class="mt-1 font-semibold text-red-600">Terlambat</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $pinjam->status === 'dikembalikan' ? 'bg-emerald-50 text-emerald-700' : ($pinjam->status === 'telat' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }}">
                                    {{ ucfirst($pinjam->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 {{ $hariTerlambat > 0 ? 'font-semibold text-red-600' : 'text-gray-500' }}">
                                {{ $hariTerlambat }} hari
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold {{ $denda > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                Rp {{ number_format($denda, 0, ',', '.') }}
                                @if($hariTerlambat > 0)
                                    <div class="mt-0.5 text-xs font-normal text-gray-500">
                                        Terlambat {{ $hariTerlambat }} hari · Rp 5.000/hari
                                    </div>
                                @endif
                                @if($pinjam->pengembalian && ($pinjam->pengembalian->kondisi_kembali !== 'Baik' || $dendaKondisi > 0))
                                    <div class="mt-0.5 text-xs font-normal text-gray-600">
                                        {{ $kondisiDendaLabel }}: Rp {{ number_format($dendaKondisi, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if(in_array($pinjam->status, ['dipinjam', 'telat'], true))
                                    <a href="{{ route('petugas.pengembalian.create', $pinjam->id) }}"
                                        class="inline-flex items-center rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Kembalikan
                                    </a>
                                @else
                                    <span class="text-xs font-medium text-gray-500">
                                        Dikembalikan {{ $pinjam->pengembalian?->tgl_kembali ? \Carbon\Carbon::parse($pinjam->pengembalian->tgl_kembali)->format('d-m-Y') : '' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                Tidak ada peminjaman yang perlu dikembalikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengembalian->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">
                {{ $pengembalian->links() }}
            </div>
        @endif
    </div>
@endsection