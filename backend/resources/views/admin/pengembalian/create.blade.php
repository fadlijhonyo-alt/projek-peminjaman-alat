@extends('layouts.app')

@section('title', 'Konfirmasi Pengembalian - Panel Admin')
@section('header-title', 'Proses Pengembalian Alat')

@section('content')

<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    {{-- Pesan Error --}}
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800">
            Konfirmasi Pengembalian
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Pastikan data alat dan peminjam sudah sesuai sebelum diproses.
        </p>
    </div>

    {{-- Data Peminjam --}}
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-semibold mb-2">
            Peminjam
        </label>

        <div class="w-full px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg">
            <div class="font-semibold text-gray-800">
                {{ $peminjaman->user->name ?? 'User Dihapus' }}
            </div>

            @if($peminjaman->user)
                <div class="text-xs text-gray-500">
                    {{ $peminjaman->user->email }}
                </div>
            @endif
        </div>
    </div>

    {{-- Tanggal --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Tanggal Pinjam
            </label>

            <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm">
                {{ \Carbon\Carbon::parse($peminjaman->tgl_pinjam)->format('d-m-Y') }}
            </div>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Rencana Kembali
            </label>

            <div class="px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm">
                {{ \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan)->format('d-m-Y') }}
            </div>
        </div>

    </div>
    {{-- Alat --}}
    <div class="mb-5">
        <label class="mb-2 block text-sm font-semibold text-gray-700">Alat yang Dipinjam</label>
        <div class="overflow-hidden rounded-lg border border-gray-200">
            @foreach($peminjaman->detailPinjam as $detail)
                <div class="flex items-center justify-between border-b px-4 py-3 last:border-b-0">
                    <div>
                        <div class="font-semibold text-gray-800">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</div>
                        <div class="text-xs text-gray-500">Jumlah dipinjam: {{ $detail->jumlah }} pcs</div>
                    </div>
                    <div class="text-sm font-semibold text-gray-700">{{ $detail->jumlah }} pcs</div>
                </div>
            @endforeach
        </div>
    </div>

    @php
        $tanggalRencana = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan);
        $tanggalSekarang = \Carbon\Carbon::today();
        $hariTerlambat = $tanggalSekarang->gt($tanggalRencana)
            ? $tanggalRencana->diffInDays($tanggalSekarang)
            : 0;
        $dendaPerHari = 5000;
        $dendaKeterlambatan = $hariTerlambat * $dendaPerHari;
    @endphp

    <form action="{{ route('admin.pengembalian.kembalikan', $peminjaman->id) }}" method="POST" onsubmit="return confirm('Yakin ingin memproses pengembalian alat ini?')">
        @csrf
        @method('PUT')

        <div class="mb-5">
            <label for="kondisi-kembali" class="mb-2 block text-sm font-semibold text-gray-700">Kondisi Alat</label>
            <select id="kondisi-kembali" name="kondisi_kembali" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                <option value="">Pilih kondisi alat</option>
                @foreach(['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'] as $kondisi)
                    <option value="{{ $kondisi }}" @selected(old('kondisi_kembali') === $kondisi)>{{ $kondisi }}</option>
                @endforeach
            </select>
            <p id="peringatan-hilang" class="mt-2 hidden text-xs font-medium text-red-700">Barang hilang tidak akan ditambahkan kembali ke stok.</p>
        </div>

        <div class="mb-6 rounded-lg border {{ $hariTerlambat > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }} p-4">
            <div class="mb-3 flex justify-between gap-4 text-sm">
                <span class="text-gray-600">Tanggal Pengembalian</span>
                <span class="font-semibold text-gray-800">{{ $tanggalSekarang->format('d-m-Y') }}</span>
            </div>
            <div class="mb-3 flex justify-between gap-4 text-sm">
                <span class="text-gray-600">Keterlambatan</span>
                <span class="font-semibold {{ $hariTerlambat > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $hariTerlambat }} hari</span>
            </div>
            <div class="mb-4 flex justify-between gap-4 text-sm">
                <span class="text-gray-600">Denda keterlambatan (Rp {{ number_format($dendaPerHari, 0, ',', '.') }}/hari)</span>
                <span class="font-semibold text-gray-800">Rp {{ number_format($dendaKeterlambatan, 0, ',', '.') }}</span>
            </div>
            <div class="mb-4">
                <label for="denda-tambahan" class="mb-1 block text-sm font-semibold text-gray-700">Denda tambahan yang diatur admin (Rp)</label>
                <input id="denda-tambahan" name="denda_tambahan" type="number" min="0" max="100000000" step="1000" required value="{{ old('denda_tambahan', 0) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
            </div>
            <div class="flex justify-between gap-4 border-t border-gray-200 pt-3 text-sm">
                <span class="font-semibold text-gray-700">Total Denda</span>
                <span id="total-denda" class="text-lg font-bold text-gray-900">Rp {{ number_format($dendaKeterlambatan + (int) old('denda_tambahan', 0), 0, ',', '.') }}</span>
            </div>
            <p class="mt-2 text-xs text-gray-500">Denda keterlambatan dihitung otomatis; total denda adalah denda keterlambatan ditambah denda tambahan.</p>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.pengembalian.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-300">Batal</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Konfirmasi Pengembalian</button>
        </div>
    </form>
</div>

<script>
    const dendaTambahanInput = document.getElementById('denda-tambahan');
    const totalDendaOutput = document.getElementById('total-denda');
    const kondisiKembaliSelect = document.getElementById('kondisi-kembali');
    const peringatanHilang = document.getElementById('peringatan-hilang');
    const dendaKeterlambatan = {{ $dendaKeterlambatan }};
    const formatRupiah = new Intl.NumberFormat('id-ID');

    dendaTambahanInput.addEventListener('input', () => {
        const dendaTambahan = Math.max(0, Number(dendaTambahanInput.value) || 0);
        totalDendaOutput.textContent = `Rp ${formatRupiah.format(dendaKeterlambatan + dendaTambahan)}`;
    });

    kondisiKembaliSelect.addEventListener('change', () => {
        peringatanHilang.classList.toggle('hidden', kondisiKembaliSelect.value !== 'Hilang');
    });
    kondisiKembaliSelect.dispatchEvent(new Event('change'));
</script>
@endsection