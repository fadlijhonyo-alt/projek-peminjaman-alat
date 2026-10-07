@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')
    <!-- Alert Selamat Datang -->
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
        Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai hak akses
        <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
    </div>

    <!-- Tabel Log Aktivitas -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Log Aktivitas Terbaru</h3>
        </div>
        <form action="{{ route('admin.dashboard') }}" method="GET" class="p-4 border-b border-gray-200 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari aktivitas, nama, atau email..."
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div>
                <label for="dari_tanggal" class="block text-xs font-medium text-gray-600 mb-1">Dari tanggal</label>
                <input id="dari_tanggal" type="date" name="dari_tanggal" value="{{ $dariTanggal }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="sampai_tanggal" class="block text-xs font-medium text-gray-600 mb-1">Sampai tanggal</label>
                <input id="sampai_tanggal" type="date" name="sampai_tanggal" value="{{ $sampaiTanggal }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-lg transition">Cari / Filter</button>
                @if($search || $dariTanggal || $sampaiTanggal)
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 text-sm font-semibold rounded-lg transition">Reset</a>
                @endif
            </div>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Waktu</th>
                        <th class="py-3 px-4 border-b">User</th>
                        <th class="py-3 px-4 border-b">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 border-b">{{ $log->created_at }}</td>
                            <td class="py-3 px-4 border-b font-medium text-gray-900">{{ $log->user->name ?? 'Sistem' }}</td>
                            <td class="py-3 px-4 border-b">{{ $log->aktivitas }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>
@endsection