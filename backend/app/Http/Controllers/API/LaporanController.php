<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanResource;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan peminjaman.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Validasi parameter
        $validator = Validator::make($request->all(), [
            'start_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
            ],

            'end_date' => [
                'nullable',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'string',
                'in:diajukan,dipinjam,dikembalikan,telat',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parameter filter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Eager loading untuk mencegah N+1 Query
        $query = Peminjaman::with([
            'user',
            'detailPinjam.alat',
            'pengembalian.petugas',
        ]);

        // 3. Filter tanggal mulai
        $query->when(
            $request->filled('start_date'),
            function ($q) use ($request) {
                $q->whereDate(
                    'tgl_pinjam',
                    '>=',
                    $request->start_date
                );
            }
        );

        // 4. Filter tanggal akhir
        $query->when(
            $request->filled('end_date'),
            function ($q) use ($request) {
                $q->whereDate(
                    'tgl_pinjam',
                    '<=',
                    $request->end_date
                );
            }
        );

        // 5. Filter berdasarkan status
        $query->when(
            $request->filled('status'),
            function ($q) use ($request) {
                $q->where(
                    'status',
                    $request->status
                );
            }
        );

        // 6. Pagination
        $perPage = $request->input('per_page', 15);

        $laporan = $query
            ->latest()
            ->paginate($perPage);

        // 7. Response menggunakan PeminjamanResource
        return PeminjamanResource::collection($laporan)
            ->additional([
                'message' =>
                    'Laporan peminjaman berhasil ditarik.',
            ])
            ->response();
    }
}