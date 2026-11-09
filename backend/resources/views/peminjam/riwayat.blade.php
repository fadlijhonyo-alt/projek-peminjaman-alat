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

        @if($jumlahTerlambat > 0)
            <div role="alert" class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-900">
                <p class="font-semibold">Pemberitahuan keterlambatan</p>
                <p class="mt-1 text-sm">
                    Ada {{ $jumlahTerlambat }} peminjaman yang sudah melewati tanggal rencana kembali.
                    Mohon segera kembalikan alat untuk menghindari denda tambahan.
                </p>
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
                            <th class="px-5 py-3">Pengembalian / Pemberitahuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($peminjamans as $index => $item)
                            @php
                                $tanggalAktualKembali = $item->pengembalian?->tgl_kembali;
                                $terlambatDikembalikan = $tanggalAktualKembali
                                    && $tanggalAktualKembali->gt($item->tgl_kembali_plan);
                                $masihTerlambat = in_array($item->status, ['dipinjam', 'telat'], true)
                                    && !$item->pengembalian
                                    && $item->tgl_kembali_plan->lt($hariIni);
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">{{ $index + 1 }}</td>
                                <td class="px-5 py-4">{{ $item->tgl_pinjam->format('d/m/Y') }}</td>
                                <td class="px-5 py-4">{{ $item->tgl_kembali_plan->format('d/m/Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                        @if($item->status == 'diajukan') bg-amber-100 text-amber-700
                                        @elseif($item->status == 'dipinjam') bg-blue-100 text-blue-700
                                        @elseif($item->status == 'telat') bg-rose-100 text-rose-700
                                        @elseif(in_array($item->status, ['dikembalikan', 'selesai'], true)) bg-emerald-100 text-emerald-700
                                        @elseif($item->status == 'ditolak') bg-rose-100 text-rose-700
                                        @else bg-rose-100 text-rose-700 @endif">
                                        {{ in_array($item->status, ['dikembalikan', 'selesai'], true) ? 'Dikembalikan' : ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($item->detailPinjams as $detail)
                                            <li>{{ $detail->alat->nama_alat ?? 'Alat' }} ({{ $detail->jumlah }} pcs)</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="min-w-64 px-5 py-4">
                                    @if($item->pengembalian)
                                        <p>
                                            Tanggal dikembalikan:
                                            <span class="font-medium text-slate-800">{{ $tanggalAktualKembali->format('d/m/Y') }}</span>
                                        </p>
                                        <p class="mt-1">
                                            Kondisi:
                                            <span class="font-medium text-slate-800">{{ $item->pengembalian->kondisi_kembali }}</span>
                                        </p>
                                        <p class="mt-1">
                                            Denda:
                                            <span class="font-semibold text-slate-800">Rp {{ number_format($item->pengembalian->denda, 0, ',', '.') }}</span>
                                        </p>
                                        @if($terlambatDikembalikan)
                                            <p class="mt-2 rounded-lg bg-rose-50 px-3 py-2 text-xs font-medium text-rose-700">
                                                Terlambat {{ (int) $item->tgl_kembali_plan->diffInDays($tanggalAktualKembali) }} hari dari tanggal rencana kembali.
                                            </p>
                                        @endif
                                    @elseif($masihTerlambat)
                                        <p role="alert" class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">
                                            Terlambat {{ (int) $item->tgl_kembali_plan->diffInDays($hariIni) }} hari.
                                            Segera kembalikan alat.
                                        </p>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-500">
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
