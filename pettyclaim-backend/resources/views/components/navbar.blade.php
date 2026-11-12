<header class="h-16 bg-[#144272] dark:bg-slate-800 rounded-xl mx-4 mt-4 mb-6 flex items-stretch justify-between shadow-lg sticky top-4 z-30 transition-colors duration-300">
    
    <!-- Bagian Kiri: Ikon Dashboard & Judul Dinamis -->
    <div class="flex items-center gap-3 pl-5 text-white">
        <!-- Tombol Hamburger Mobile -->
        <button type="button" @click="sidebarOpen = true" class="lg:hidden p-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fa-solid fa-bars text-sm"></i>
        </button>

        <!-- Ikon Dashboard -->
        <i class="fa-solid fa-table-cells-large text-xl hidden sm:block"></i>
        
        <!-- Judul Dinamis -->
        <h2 class="text-sm sm:text-base font-extrabold tracking-wide text-white" x-text="
            activeTab === 'jurnal' ? 'Dashboard Draf Jurnal' : 
            activeTab === 'claims' ? 'Pengajuan Klaim Struk' : 
            activeTab === 'reports' ? 'Laporan Analytics' : 
            activeTab === 'limits' || activeTab === 'batas' ? 'Pengaturan Batas Kas' : 'Kelola Pengguna Sistem'
        ">Dashboard</h2>
    </div>

    <!-- Bagian Kanan: Tema, Export, dan Profil (Search & Notif dihapus) -->
    <div class="flex items-center gap-4 md:gap-5 pr-0">
        
        <!-- Tombol Export & Tema -->
        <div class="hidden md:flex items-center gap-2.5">
            <!-- MODERN PILL TOGGLE SWITCH UNTUK DARK/LIGHT MODE -->
            <button type="button" 
                    x-on:click="
                        isDark = !isDark;
                        if (isDark) {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('color-theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('color-theme', 'light');
                        }
                    " 
                    class="relative inline-flex items-center w-12 h-6 rounded-full bg-slate-900/40 border border-white/20 cursor-pointer transition-colors duration-300 px-1 focus:outline-none" 
                    title="Beralih Tema">
                <span class="w-full flex items-center justify-between px-0.5 text-[10px] pointer-events-none select-none">
                    <i class="fa-solid fa-sun text-amber-400"></i>
                    <i class="fa-solid fa-moon text-indigo-300"></i>
                </span>
                <span class="absolute left-1 top-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-300 flex items-center justify-center pointer-events-none"
                      :class="isDark ? 'translate-x-5' : 'translate-x-0'">
                </span>
            </button>

            <!-- Export Buttons (Solid Block) -->
            <a href="{{ route('admin.journals.export-pdf') ?? '#' }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-extrabold uppercase tracking-widest transition flex items-center gap-1.5 shadow-sm border border-rose-500">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.journals.export') ?? '#' }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-extrabold uppercase tracking-widest transition flex items-center gap-1.5 shadow-sm border border-emerald-500">
                <i class="fa-solid fa-file-excel"></i> CSV
            </a>
        </div>

        <!-- Blok Profil User (Putih Pekat di Kanan) -->
        <div class="bg-white h-full flex items-center gap-3 px-4 md:px-5 shadow-[-4px_0_15px_rgba(0,0,0,0.1)] rounded-r-xl border-l border-slate-100">
            <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-800 shrink-0">
                <i class="fa-solid fa-user text-sm"></i>
            </div>
            <div class="flex flex-col justify-center hidden sm:flex">
                <span class="text-xs font-extrabold text-slate-900 leading-tight">{{ Auth::user()->name ?? 'Administrator Finance' }}</span>
                <span class="text-[9px] text-slate-500 font-semibold tracking-wide">{{ Auth::user()->email ?? 'admin@pettyclaim.com' }}</span>
            </div>
        </div>

    </div>
</header>