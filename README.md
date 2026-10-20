# projek-peminjaman-alat
curl -I https://getcomposer.org/schema.json
public function destroy(Alat $alat): JsonResponse
    {
        DB::transaction(function () use ($alat) {
            if ($alat->gambar) {
                Storage::disk('public')->delete($alat->gambar);
            }

            $alat->delete();
        });

        return response()->json([
            'message' => 'Alat berhasil dihapus.',
        ]);
    }

     // Menghapus data alat
    public function destroyAlat($id)
    {
        $alat = Alat::findOrFail($id);

        // Hapus file gambar fisik jika ada
        if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
        }

        $alat->delete();

        return redirect()->route('admin.alat.index')->with('success', 'Data alat berhasil dihapus.');
    }
    
