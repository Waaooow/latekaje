<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- CARD KIRI: INFORMASI UTAMA SISTEM (Makan 2 Kolom) -->
        <div class="md:col-span-2 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 rounded-lg font-bold text-xl tracking-wider">
                        LTKJ
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">LATEKAJE</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">Build Version 1.0.0 (Stable)</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    <p>
                        <strong>LATEKAJE</strong> (Layanan Terpadu Inventaris & Peminjaman Alat TJKT) adalah sistem manajemen aset laboratorium modern yang dirancang khusus untuk mempermudah operasional, pencatatan data fisik barang, pelabelan stiker QR unik, hingga alur sirkulasi peminjaman mandiri bagi siswa di jurusan <strong>Teknik Jaringan Komputer dan Telekomunikasi</strong>.
                    </p>
                    <p>
                        Aplikasi ini dikembangkan dengan pendekatan <em>Kiosk-Mode</em>, memungkinkan satu komputer di dalam ruang laboratorium digunakan secara bergantian oleh puluhan siswa dengan metode <strong>Scan QR Webcam nirkabel</strong> yang sangat instan.
                    </p>
                </div>

                <!-- DAFTAR TEKNOLOGI YANG DIGUNAKAN -->
                <div class="mt-6">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Core Stack Engine</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300">Laravel 13.x</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Filament Framework</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">Alpine.js (Reactive UI)</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300">Tailwind CSS</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Html5-Qrcode Engine</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 dark:text-gray-500 font-mono">
                Status Sistem: 🟢 Terhubung ke Basis Data Utama (MySQL Lokal)
            </div>
        </div>

        <!-- CARD KANAN: KREDIT PENGEMBANG & EASTER EGG (1 Kolom) -->
        <div class="p-6 bg-gradient-to-br from-gray-900 to-slate-950 dark:from-gray-950 dark:to-black text-white rounded-xl shadow-sm border border-gray-800 flex flex-col justify-between relative overflow-hidden">
            <!-- Dekorasi Efek Cahaya Belakang -->
            <div class="absolute -right-10 -top-10 w-32 height-32 bg-primary-500/10 rounded-full blur-2xl"></div>
            
            <div>
                <h3 class="text-lg font-bold tracking-tight text-primary-400">Tim Pengembang</h3>
                <p class="text-xs text-gray-400 mt-1">Kolaborasi Manusia & Kecerdasan Buatan</p>

                <div class="mt-6 space-y-4">
                    <!-- Developer 1: Kamu -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center font-bold text-white shadow-inner">
                            AF
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Alfin</h4>
                            <p class="text-xs text-gray-400">Lead System Architect & DevOps</p>
                        </div>
                    </div>

                    <!-- Developer 2: AI -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-tr from-purple-600 to-indigo-600 rounded-full flex items-center justify-center font-bold text-white text-lg">
                            🤖
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Gemini AI</h4>
                            <p class="text-xs text-gray-400">Logic Writer & Debugging Expert</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesan Motivasi Khas Programmer -->
            <div class="mt-8 pt-4 border-t border-gray-800 text-center">
                <blockquote class="text-xs italic text-gray-400">
                    "Crafted with logic by humans, powered by AI, built for the next generation of network technicians."
                </blockquote>
            </div>
        </div>

    </div>
</x-filament-panels::page><x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- CARD KIRI: INFORMASI UTAMA SISTEM (Makan 2 Kolom) -->
        <div class="md:col-span-2 p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400 rounded-lg font-bold text-xl tracking-wider">
                        LTKJ
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">LATEKAJE</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">Build Version 1.0.0 (Stable)</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    <p>
                        <strong>LATEKAJE</strong> (Layanan Terpadu Inventaris & Peminjaman Alat TJKT) adalah sistem manajemen aset laboratorium modern yang dirancang khusus untuk mempermudah operasional, pencatatan data fisik barang, pelabelan stiker QR unik, hingga alur sirkulasi peminjaman mandiri bagi siswa di jurusan <strong>Teknik Jaringan Komputer dan Telekomunikasi</strong>.
                    </p>
                    <p>
                        Aplikasi ini dikembangkan dengan pendekatan <em>Kiosk-Mode</em>, memungkinkan satu komputer di dalam ruang laboratorium digunakan secara bergantian oleh puluhan siswa dengan metode <strong>Scan QR Webcam nirkabel</strong> yang sangat instan.
                    </p>
                </div>

                <!-- DAFTAR TEKNOLOGI YANG DIGUNAKAN -->
                <div class="mt-6">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Core Stack Engine</h3>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300">Laravel 13.x</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Filament Framework</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">Alpine.js (Reactive UI)</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-50 text-cyan-700 dark:bg-cyan-950 dark:text-cyan-300">Tailwind CSS</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Html5-Qrcode Engine</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 dark:text-gray-500 font-mono">
                Status Sistem: 🟢 Terhubung ke Basis Data Utama (MySQL Lokal)
            </div>
        </div>

        <!-- CARD KANAN: KREDIT PENGEMBANG & EASTER EGG (1 Kolom) -->
        <div class="p-6 bg-gradient-to-br from-gray-900 to-slate-950 dark:from-gray-950 dark:to-black text-white rounded-xl shadow-sm border border-gray-800 flex flex-col justify-between relative overflow-hidden">
            <!-- Dekorasi Efek Cahaya Belakang -->
            <div class="absolute -right-10 -top-10 w-32 height-32 bg-primary-500/10 rounded-full blur-2xl"></div>
            
            <div>
                <h3 class="text-lg font-bold tracking-tight text-primary-400">Tim Pengembang</h3>
                <p class="text-xs text-gray-400 mt-1">Kolaborasi Manusia & Kecerdasan Buatan</p>

                <div class="mt-6 space-y-4">
                    <!-- Developer 1: Kamu -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center font-bold text-white shadow-inner">
                            AF
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Alfin</h4>
                            <p class="text-xs text-gray-400">Lead System Architect & DevOps</p>
                        </div>
                    </div>

                    <!-- Developer 2: AI -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-tr from-purple-600 to-indigo-600 rounded-full flex items-center justify-center font-bold text-white text-lg">
                            🤖
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold">Gemini AI</h4>
                            <p class="text-xs text-gray-400">Logic Writer & Debugging Expert</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesan Motivasi Khas Programmer -->
            <div class="mt-8 pt-4 border-t border-gray-800 text-center">
                <blockquote class="text-xs italic text-gray-400">
                    "Crafted with logic by humans, powered by AI, built for the next generation of network technicians."
                </blockquote>
            </div>
        </div>

    </div>
</x-filament-panels::page>