<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Peminjaman Alat</title>
    <style>
        @page { margin: 0; }
        body { font-family: sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2, .header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; float: right; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { margin: 16mm; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; background: #e2e8f0; padding: 10px; border-radius: 5px; text-align: right;">
    <button onclick="window.print()" style="background: #2563eb; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
    <a href="{{ route('petugas.laporan.index', request()->query()) }}" style="display: inline-block; background: #64748b; color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; margin-left: 5px;">Kembali ke Rekap</a>
</div>

<div class="header">
    <h2>LAPORAN PEMINJAMAN DAN PENGEMBALIAN ALAT</h2>
    <p>Sistem Informasi Manajemen Peminjaman Alat</p>
    @if(request('dari_tanggal') && request('sampai_tanggal'))
        <p style="font-size: 11px;">Periode: {{ request('dari_tanggal') }} s/d {{ request('sampai_tanggal') }}</p>
    @endif
    @if($search)
        <p style="font-size: 11px;">Pencarian: {{ $search }}</p>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th width="5%" class="text-center">No</th>
            <th width="20%">Peminjam</th>
            <th width="15%">Tgl Pinjam</th>
            <th width="15%">Rencana Kembali</th>
            <th width="15%">Status</th>
            <th width="20%">Detail Alat</th>
            <th width="10%">Denda / Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($laporans as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ $item->tgl_pinjam }}</td>
                <td>{{ $item->tgl_kembali_plan }}</td>
                <td>{{ in_array($item->status, ['dikembalikan', 'selesai'], true) ? 'Dikembalikan' : ucfirst($item->status) }}</td>
                <td>
                    <ul style="margin: 0; padding-left: 15px;">
                        @foreach($item->detailPinjams as $detail)
                            <li>{{ $detail->alat->nama_alat ?? '-' }} ({{ $detail->jumlah }})</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    @php
                        $pengembalian = $item->pengembalian;
                        $denda = $pengembalian->denda ?? 0;
                        $hariTerlambat = 0;
                        $dendaKondisi = 0;
                        $kondisiDendaLabel = match ($pengembalian?->kondisi_kembali) {
                            'Hilang' => 'Barang hilang',
                            'Rusak Ringan' => 'Kerusakan ringan',
                            'Rusak Berat' => 'Kerusakan berat',
                            default => 'Denda tambahan',
                        };

                        if ($pengembalian) {
                            $tanggalRencana = \Carbon\Carbon::parse($item->tgl_kembali_plan);
                            $tanggalKembali = \Carbon\Carbon::parse($pengembalian->tgl_kembali);
                            $hariTerlambat = $tanggalKembali->gt($tanggalRencana)
                                ? $tanggalRencana->diffInDays($tanggalKembali)
                                : 0;
                            $dendaKondisi = max(0, $denda - ($hariTerlambat * 5000));
                        }
                    @endphp
                    Rp {{ number_format($denda, 0, ',', '.') }}
                    @if($pengembalian && $hariTerlambat > 0)
                        <div style="margin-top: 3px; font-size: 10px; color: #555;">
                            Terlambat {{ $hariTerlambat }} hari · Rp 5.000/hari
                        </div>
                    @endif
                    @if($pengembalian && ($pengembalian->kondisi_kembali !== 'Baik' || $dendaKondisi > 0))
                        <div style="margin-top: 3px; font-size: 10px; color: #555;">
                            {{ $kondisiDendaLabel }}: Rp {{ number_format($dendaKondisi, 0, ',', '.') }}
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data laporan.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <p>Baleendah, {{ date('d F Y') }}</p>
    <p>Petugas Pengelola,</p>
    <br><br><br>
    <p><b>{{ auth()->user()->name }}</b></p>
</div>

</body>
</html>