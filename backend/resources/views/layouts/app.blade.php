<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>@yield('title', 'Dashboard Admin')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- SweetAlert2 untuk Notifikasi Pop-up Toast -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased overflow-hidden">

    <!-- Layout Utama -->
    <div class="flex h-screen w-full">

        <!-- Sidebar (Full Tinggi & Flex Column untuk dorong User ke bawah) -->
        <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col justify-between shrink-0 h-full shadow-lg">

            <!-- BAGIAN ATAS: Judul & Navigasi -->
            <div>
                <div class="p-6 text-base font-bold tracking-wider border-b border-slate-800 text-indigo-400 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="px-6 py-4 flex items-center gap-2 border-b border-gray-800">
                    <span class="text-white font-bold text-lg uppercase tracking-wider">
                        PANEL {{ auth()->user()->role }}
                    </span>
                </div>

                <nav class="p-4 space-y-1.5">

                    <!-- MENU KHUSUS ADMIN -->
                    @if(auth()->check() && auth()->user()->role == 'admin')
                        
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                            <span>Dashboard</span>
                        </a>

                        <!-- Kelola User -->
                        <a href="{{ route('admin.user.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Kelola User</span>
                        </a>

                        <!-- Kelola Kategori -->
                        <a href="{{ route('admin.kategori.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <span>Kelola Kategori</span>
                        </a>

                        <!-- Kelola Alat -->
                        <a href="{{ route('admin.alat.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Kelola Alat</span>
                        </a>

                        <!-- Kelola Peminjaman -->
                        <a href="{{ route('admin.peminjaman.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <span>Kelola Peminjaman</span>
                        </a>

                        <!-- Kelola Pengembalian -->
                        <a href="{{ route('admin.pengembalian.index') }}" 
                            class="group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                                
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-400 transition-colors duration-200" 
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                
                                <span class="font-medium">Kelola Pengembalian</span>
                            </a>

                    @endif

                    <!-- MENU KHUSUS PETUGAS -->
                    @if(auth()->user()->role === 'petugas')
                        <!-- Persetujuan Peminjaman -->
                        <a href="{{ route('petugas.peminjaman.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 {{ 
                            request()->routeIs('petugas.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            
                            <svg class="w-5 h-5 {{ request()->routeIs('petugas.peminjaman*') ? 'text-blue-400' : 'text-gray-400 group-hover:text-blue-400' }} transition-colors duration-200" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Persetujuan Peminjaman</span>
                        </a>

                        <!-- Pemantauan Pengembalian -->
                        <a href="{{ route('petugas.pengembalian.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 {{ 
                            request()->routeIs('petugas.pengembalian*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            
                            <svg class="w-5 h-5 {{ request()->routeIs('petugas.pengembalian*') ? 'text-blue-400' : 'text-gray-400 group-hover:text-blue-400' }} transition-colors duration-200" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4-4m-4 4l4 4m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Pemantauan Pengembalian</span>
                        </a>

                        <!-- Cetak Laporan -->
                        <a href="{{ route('petugas.laporan.index') }}"
                            class="group flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 {{ 
                            request()->routeIs('petugas.laporan*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            
                            <svg class="w-5 h-5 {{ request()->routeIs('petugas.laporan*') ? 'text-blue-400' : 'text-gray-400 group-hover:text-blue-400' }} transition-colors duration-200" 
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Cetak Laporan</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- BAGIAN BAWAH: Status User Login (Otomatis di bawah) -->
            <div class="p-4 border-t border-slate-800 text-xs text-slate-400 bg-slate-950/40">
                <p class="mb-0.5">Logged in as :</p>
                @auth
                    <span class="font-semibold text-slate-200 text-sm">
                        {{ auth()->user()->name }}
                    </span>
                @endauth
            </div>

        </aside>

        <!-- Content sebelah kanan -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Navbar -->
            <header class="bg-white border-b border-slate-100 h-16 flex justify-between items-center px-6 shrink-0 shadow-sm">

                <h1 class="text-base font-bold text-slate-800">
                    @yield('header-title', 'Dashboard')
                </h1>

                @auth
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-medium px-4 py-2 rounded-xl group flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            Logout
                        </button>
                    </form>
                @endauth

            </header>

            <!-- Content Utama yang bisa di-scroll -->
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- Script Notifikasi Pop-Up Toast Melayang -->
    @if(session('success'))
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end', // Pop-up melayang di pojok kanan bawah
            showConfirmButton: false,
            timer: 3000, // Hilang otomatis setelah 3 detik
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    </script>
    @endif

</body>
</html>