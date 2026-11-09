<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-8">
    <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-md">
        <h1 class="mb-2 text-center text-2xl font-bold text-gray-800">Buat Akun</h1>
        <p class="mb-6 text-center text-sm text-gray-600">Daftar sebagai peminjam alat.</p>

        @if($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-100 p-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="no_hp" class="mb-1 block text-sm font-semibold text-gray-700">Nomor HP <span class="font-normal text-gray-500">(opsional)</span></label>
                <input id="no_hp" type="tel" name="no_hp" value="{{ old('no_hp') }}" maxlength="15" autocomplete="tel"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="alamat" class="mb-1 block text-sm font-semibold text-gray-700">Alamat <span class="font-normal text-gray-500">(opsional)</span></label>
                <textarea id="alamat" name="alamat" rows="2" autocomplete="street-address"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat') }}</textarea>
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-semibold text-gray-700">Password</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter.</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-blue-600 py-2 font-semibold text-white transition hover:bg-blue-700">
                Daftar
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">Masuk</a>
        </p>
    </div>
</body>
</html>
