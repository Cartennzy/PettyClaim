<aside class="w-64 fixed inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out lg:translate-x-0 shadow-[10px_0_25px_rgba(0,0,0,0.15)] rounded-r-[2rem] overflow-hidden bg-[#2C74B3]"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <!-- Overlay Background Two-Tone (Warna Biru Gelap di Kiri tembus sampai bawah) -->
    <div class="absolute inset-y-0 left-0 w-[76px] bg-[#144272] z-0 shadow-[2px_0_10px_rgba(0,0,0,0.2)]"></div>

    <!-- Wrapper Konten Utama -->
    <div class="relative z-10 flex flex-col h-full w-full">
        
        <!-- Header (Logo & Brand) -->
        <div class="flex items-center h-24 mt-2">
            <!-- Tempat Logo PettyClaim -->
            <div class="w-[76px] flex justify-center items-center shrink-0">
                <div class="bg-white p-1 rounded-xl shadow-lg border border-white/20 w-11 h-11 flex items-center justify-center">
                    <img src="{{ asset('assets/logo_pettyclaim.png') }}" alt="Logo PettyClaim" class="w-full h-full object-cover rounded-lg">
                </div>
            </div>
            <!-- Nama Brand -->
            <div class="flex-1 pr-4 pl-3 h-full flex flex-col justify-center border-b border-white/10 relative">
                <h1 class="font-extrabold text-sm tracking-tight text-white uppercase mt-1">PettyClaim</h1>
                <p class="text-[9px] font-bold text-white/60 tracking-widest uppercase">Navigation</p>
                
                <!-- Tombol Tutup Mobile -->
                <button type="button" @click="sidebarOpen = false" class="lg:hidden absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white">
                    <i class="fa-solid fa-caret-left text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Daftar Navigasi -->
        <nav class="flex flex-col gap-2 mt-6 flex-1 overflow-y-auto no-scrollbar">
            
            <!-- Menu: Draf Jurnal -->
            <button type="button" @click="activeTab = 'jurnal'" class="w-full flex items-center h-12 transition-all relative group"
                    :class="activeTab === 'jurnal' ? 'text-[#144272]' : 'text-white/60 hover:text-white'">
                <!-- Background putih mulus -->
                <div x-show="activeTab === 'jurnal'" class="absolute inset-y-0 left-4 right-0 bg-white rounded-l-full shadow-md z-10" x-cloak></div>
                <div class="w-[76px] shrink-0 flex justify-center items-center relative z-20">
                    <i class="fa-solid fa-book-bookmark text-[1.15rem]"></i>
                </div>
                <div class="flex-1 text-left pl-1 relative z-20">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">Draf Jurnal</span>
                </div>
            </button>

            <!-- Menu: Klaim Struk -->
            <button type="button" @click="activeTab = 'claims'" class="w-full flex items-center h-12 transition-all relative group"
                    :class="activeTab === 'claims' ? 'text-[#144272]' : 'text-white/60 hover:text-white'">
                <div x-show="activeTab === 'claims'" class="absolute inset-y-0 left-4 right-0 bg-white rounded-l-full shadow-md z-10" x-cloak></div>
                <div class="w-[76px] shrink-0 flex justify-center items-center relative z-20">
                    <i class="fa-solid fa-receipt text-[1.15rem]"></i>
                </div>
                <div class="flex-1 text-left pl-1 relative z-20">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">Klaim Struk</span>
                </div>
            </button>

            <!-- Menu: Laporan -->
            <button type="button" @click="activeTab = 'reports'" class="w-full flex items-center h-12 transition-all relative group"
                    :class="activeTab === 'reports' ? 'text-[#144272]' : 'text-white/60 hover:text-white'">
                <div x-show="activeTab === 'reports'" class="absolute inset-y-0 left-4 right-0 bg-white rounded-l-full shadow-md z-10" x-cloak></div>
                <div class="w-[76px] shrink-0 flex justify-center items-center relative z-20">
                    <i class="fa-solid fa-chart-pie text-[1.15rem]"></i>
                </div>
                <div class="flex-1 text-left pl-1 relative z-20">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">Laporan</span>
                </div>
            </button>

            <!-- Jarak Pemisah -->
            <div class="h-4"></div>

            <!-- Menu: Batas Kas -->
            <button type="button" @click="activeTab = 'limits'" class="w-full flex items-center h-12 transition-all relative group"
                    :class="activeTab === 'limits' || activeTab === 'batas' ? 'text-[#144272]' : 'text-white/60 hover:text-white'">
                <div x-show="activeTab === 'limits' || activeTab === 'batas'" class="absolute inset-y-0 left-4 right-0 bg-white rounded-l-full shadow-md z-10" x-cloak></div>
                <div class="w-[76px] shrink-0 flex justify-center items-center relative z-20">
                    <i class="fa-solid fa-sliders text-[1.15rem]"></i>
                </div>
                <div class="flex-1 text-left pl-1 relative z-20">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">Batas Kas</span>
                </div>
            </button>

            <!-- Menu: Pengguna -->
            <button type="button" @click="activeTab = 'users'" class="w-full flex items-center h-12 transition-all relative group"
                    :class="activeTab === 'users' ? 'text-[#144272]' : 'text-white/60 hover:text-white'">
                <div x-show="activeTab === 'users'" class="absolute inset-y-0 left-4 right-0 bg-white rounded-l-full shadow-md z-10" x-cloak></div>
                <div class="w-[76px] shrink-0 flex justify-center items-center relative z-20">
                    <i class="fa-solid fa-users-gear text-[1.15rem]"></i>
                </div>
                <div class="flex-1 text-left pl-1 relative z-20">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest">Pengguna</span>
                </div>
            </button>

        </nav>

        <!-- Area Bawah Khusus (Menyatu tanpa garis potong) -->
        <div class="mt-auto relative w-full h-32 flex flex-col justify-end pb-6 z-20 overflow-hidden">
            
            <!-- Siluet Icons KHUSUS di area bawah -->
            <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
                <!-- Ikon Power / Logout besar di background kanan -->
                <i class="fa-solid fa-power-off absolute -bottom-4 -right-2 text-7xl text-white opacity-10 rotate-12"></i>
                <!-- Ikon Shield / Security di background tengah/atas -->
                <i class="fa-solid fa-shield-halved absolute top-2 right-20 text-5xl text-white opacity-[0.05] -rotate-12"></i>
                <!-- Ikon kunci kecil di background kiri gelap -->
                <i class="fa-solid fa-lock absolute bottom-6 -left-2 text-5xl text-white opacity-[0.03] -rotate-6"></i>
            </div>

            <!-- Ornamen Gelombang -->
            <div class="absolute bottom-0 left-0 right-0 opacity-[0.15] pointer-events-none z-0">
                <svg viewBox="0 0 1440 320" class="w-full h-auto text-white fill-current">
                    <path d="M0,288L48,272C96,256,192,224,288,197.3C384,171,480,149,576,165.3C672,181,768,235,864,250.7C960,267,1056,245,1152,250.7C1248,256,1344,288,1392,304L1440,320L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <!-- Kontainer Tombol & Settings (Transparan, sejajar dengan background utama) -->
            <div class="flex items-center w-full relative z-10">
                <!-- Ikon Settings (Kiri - Di atas biru gelap) -->
                <div class="w-[76px] shrink-0 flex justify-center items-center">
                    <i class="fa-solid fa-gear text-xl text-white/50 hover:text-white cursor-pointer transition-all duration-300 hover:rotate-90"></i>
                </div>
                <!-- Tombol Logout Badge Merah (Kanan - Di atas biru terang) -->
                <div class="flex-1 pr-4 pl-1 flex items-center">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="w-full m-0">
                        @csrf
                        <button type="button" onclick="confirmLogout()" class="w-full flex items-center justify-center gap-2 bg-[#E12E2E] hover:bg-[#C92222] text-white px-3 py-3 rounded-xl text-[10px] font-extrabold uppercase tracking-widest transition-colors shadow-[0_4px_15px_rgba(225,46,46,0.3)]">
                            Logout Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</aside>