<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE peminjaman MODIFY status ENUM('diajukan', 'dipinjam', 'dikembalikan', 'telat', 'ditolak') NOT NULL DEFAULT 'diajukan'");

            return;
        }

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('status')->default('diajukan')->change();
        });
    }

    public function down(): void
    {
        DB::table('peminjaman')->where('status', 'ditolak')->update(['status' => 'diajukan']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE peminjaman MODIFY status ENUM('diajukan', 'dipinjam', 'dikembalikan', 'telat') NOT NULL DEFAULT 'diajukan'");

            return;
        }

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('status')->default('diajukan')->change();
        });
    }
};