<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PettyClaim - Enterprise Finance System</title>
    
    <!-- Favicon Logo PettyClaim -->
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_pettyclaim.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <!-- Script Deteksi & Inisialisasi Tema Awal -->
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Dark Cobalt 3D Vertical Fluted Ribbon / Slats Wave (Hero Dark Mode) */
        .hero-ribbon-bg {
            background-color: #030825;
            background-image: 
                radial-gradient(circle at 50% 25%, rgba(30, 88, 241, 0.45) 0%, transparent 65%),
                radial-gradient(circle at 80% 10%, rgba(56, 189, 248, 0.25) 0%, transparent 40%),
                repeating-linear-gradient(
                    90deg,
                    rgba(3, 10, 42, 0.95) 0px,
                    rgba(14, 43, 130, 0.75) 28px,
                    rgba(37, 99, 235, 0.35) 45px,
                    rgba(10, 25, 75, 0.9) 65px,
                    rgba(2, 6, 23, 0.98) 78px
                );
        }

        /* 3D Vertical Fluted Ribbon / Slats Wave (Hero Light Mode) */
        html:not(.dark) .hero-ribbon-bg {
            background-color: #0d1e63;
            background-image: 
                radial-gradient(circle at 50% 25%, rgba(59, 130, 246, 0.6) 0%, transparent 65%),
                radial-gradient(circle at 80% 10%, rgba(56, 189, 248, 0.4) 0%, transparent 40%),
                repeating-linear-gradient(
                    90deg,
                    rgba(12, 28, 96, 0.95) 0px,
                    rgba(37, 99, 235, 0.8) 28px,
                    rgba(147, 197, 253, 0.45) 45px,
                    rgba(29, 78, 216, 0.9) 65px,
                    rgba(15, 23, 42, 0.98) 78px
                );
        }

        /* Smooth out the ribbon at the bottom edge (Fixing the Perbatasan) */
        .hero-bottom-fade {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: linear-gradient(to top, rgba(3, 8, 37, 1) 0%, rgba(3, 8, 37, 0) 100%);
            z-index: 1;
            pointer-events: none;
        }
        html:not(.dark) .hero-bottom-fade {
            background: linear-gradient(to top, rgba(13, 30, 99, 1) 0%, rgba(13, 30, 99, 0) 100%);
        }

        /* BACKGROUND SECTION WITH VIBRANT AURORA GLOW EFFECT */
        .features-aurora-wrapper {
            position: relative;
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.45) 0%, transparent 50%),
                radial-gradient(circle at 90% 25%, rgba(192, 132, 252, 0.45) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 15% 80%, rgba(52, 211, 153, 0.35) 0%, transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(129, 140, 248, 0.35) 0%, transparent 50%);
        }

        .dark .features-aurora-wrapper {
            background-color: #020617;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(14, 165, 233, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 90% 25%, rgba(168, 85, 247, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.3) 0%, transparent 60%),
                radial-gradient(circle at 15% 80%, rgba(16, 185, 129, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 85% 85%, rgba(99, 102, 241, 0.2) 0%, transparent 50%);
        }

        /* High-Tier Reveal Animation on Scroll */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(40px) scale(0.97);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-revealed {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* Continuous Rotating Halo */
        @keyframes rotateHalo {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .animate-halo { animation: rotateHalo 6s linear infinite; }
        .animate-halo-reverse { animation: rotateHalo 9s linear infinite reverse; }

        /* Multi-Orbit Pulsing Glow */
        @keyframes neonPulseGlow {
            0%, 100% { transform: scale(1); opacity: 0.6; filter: blur(35px); }
            50% { transform: scale(1.3); opacity: 0.95; filter: blur(55px); }
        }
        .animate-neon-pulse { animation: neonPulseGlow 3s ease-in-out infinite; }

        /* Workflow Connector Flow Beam */
        @keyframes flowBeam {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        .animate-flow-beam {
            background: linear-gradient(90deg, rgba(56,189,248,0.2) 0%, rgba(56,189,248,1) 50%, rgba(99,102,241,0.2) 100%);
            background-size: 200% auto;
            animation: flowBeam 3s linear infinite;
        }

        /* Unclip Text Reveal */
        @keyframes textUnclip {
            0% { max-width: 0px; opacity: 0; }
            100% { max-width: 450px; opacity: 1; }
        }
        .animate-unclip {
            overflow: hidden;
            white-space: nowrap;
            animation: textUnclip 1.1s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.4s;
        }

        /* Ambient Glow Pulse */
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.35; filter: blur(45px); }
            50% { opacity: 0.75; filter: blur(70px); }
        }
        .animate-glow { animation: pulseGlow 5s ease-in-out infinite; }

        /* Text Shimmer Gradient */
        @keyframes textShimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        .animate-shimmer {
            background: linear-gradient(110deg, #ffffff 30%, #38bdf8 50%, #ffffff 70%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            animation: textShimmer 5s linear infinite;
        }

        /* Ripple Wave Effect Animation */
        @keyframes rippleEffect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        .ripple-span {
            position: absolute;
            border-radius: 50%;
            transform: scale(0);
            animation: rippleEffect 600ms linear;
            background-color: rgba(255, 255, 255, 0.45);
            pointer-events: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #1e293b; }
        ::-webkit-scrollbar-thumb:hover { background: #38bdf8; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#020617] text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-between selection:bg-sky-500 selection:text-white relative overflow-x-hidden transition-colors duration-300">

    <!-- MEMANGGIL COMPONENT SPLASH SCREEN DARI PARTIALS -->
    @include('splash')

    <!-- ========================================== -->
    <!-- 2. HERO SECTION DENGAN 3D VERTICAL SLATS   -->
    <!-- ========================================== -->
    <header class="relative w-full hero-ribbon-bg text-white pt-6 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden rounded-b-[40px] lg:rounded-b-[56px] shadow-[0_30px_60px_-15px_rgba(37,99,235,0.35)] dark:shadow-[0_30px_60px_-15px_rgba(2,6,23,1)] ring-1 ring-white/5 relative z-20 transition-all duration-300">
        
        <!-- Gradien tambahan untuk menyamarkan perbatasan kasar bawah ribbon ke lengkungan -->
        <div class="hero-bottom-fade"></div>

        <canvas id="heroParticleCanvas" class="absolute inset-0 w-full h-full opacity-35 pointer-events-none z-0"></canvas>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-blue-500/25 rounded-full blur-3xl pointer-events-none z-0 animate-glow"></div>

        <nav class="max-w-7xl mx-auto flex items-center justify-between relative z-20 pb-12 sm:pb-16 border-b border-white/10">
            <div class="flex items-center gap-3">
                <!-- LOGO NAVBAR REPLACEMENT -->
                <img src="{{ asset('assets/logo_pettyclaim.png') }}" alt="Logo PettyClaim" class="w-11 h-11 rounded-xl object-cover border-2 border-sky-400 shadow-[0_0_15px_rgba(56,189,248,0.7)] dark:shadow-[0_0_20px_rgba(56,189,248,0.9)]">
                <span class="text-xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-sky-100 to-sky-300">PettyClaim.Ai</span>
            </div>

            <div class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-300">
                <a href="#" class="text-white hover:text-sky-300 transition">Home</a>
                <a href="#approval-flow" class="hover:text-sky-300 transition">Alur Approval</a>
                <a href="#features" class="hover:text-sky-300 transition">Fitur OCR</a>
                <a href="#faq" class="hover:text-sky-300 transition">FAQ</a>
            </div>

            <div class="flex items-center gap-3">
                <!-- LIGHT / DARK MODE SWITCH BUTTON (TOGGLE PILL) -->
                <button id="theme-toggle" type="button" aria-label="Toggle Light/Dark Theme" class="relative w-14 h-8 rounded-full p-1 bg-white/15 dark:bg-slate-900/80 border border-white/20 dark:border-sky-500/30 backdrop-blur-md transition-colors duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-sky-400 flex items-center justify-between shadow-inner">
                    <i class="fa-solid fa-sun text-[11px] text-amber-300 ml-1"></i>
                    <i class="fa-solid fa-moon text-[11px] text-sky-300 mr-1"></i>
                    <span id="theme-toggle-indicator" class="absolute left-1 top-1 w-6 h-6 rounded-full bg-white dark:bg-sky-400 shadow-md flex items-center justify-center transform transition-transform duration-300">
                        <i id="theme-icon-dark" class="fa-solid fa-moon text-[10px] text-sky-950 hidden"></i>
                        <i id="theme-icon-light" class="fa-solid fa-sun text-[10px] text-amber-500 hidden"></i>
                    </span>
                </button>

                <a href="{{ route('login') }}" class="btn-ripple relative overflow-hidden px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 via-sky-500 to-blue-700 hover:from-blue-500 hover:to-sky-400 text-white text-xs font-bold transition-all duration-200 shadow-lg shadow-blue-600/40 active:scale-95 inline-block">Portal Login</a>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto text-center relative z-10 pt-6">
            <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full border border-sky-400/30 bg-sky-500/10 backdrop-blur-md text-sky-300 text-[11px] font-bold tracking-widest uppercase mb-6 shadow-[0_0_20px_rgba(56,189,248,0.2)] reveal-on-scroll">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-400"></span>
                </span>
                Manage Smarter With PettyClaim.Ai
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.1] mb-6 reveal-on-scroll" style="transition-delay: 100ms;">
                Save Time & Budget <br>
                <span class="animate-shimmer">On Every Claim</span>
            </h1>

            <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto mb-10 leading-relaxed font-normal reveal-on-scroll" style="transition-delay: 200ms;">
                Ekstraksi otomatis kwitansi pengeluaran karyawan via OCR AI, otomasi persetujuan berjenjang, dan sinkronisasi laporan jurnal kas kecil secara instan.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-3.5 mb-16 relative reveal-on-scroll" style="transition-delay: 300ms;">
                <!-- CTA 1: Login Portal -->
                <a href="{{ route('login') }}" class="btn-ripple relative overflow-hidden w-full sm:w-auto px-7 py-3.5 rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:via-indigo-500 hover:to-purple-500 text-white font-extrabold text-sm transition-all duration-200 shadow-[0_0_30px_rgba(79,70,229,0.5)] hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center justify-center gap-2.5 cursor-pointer group border border-indigo-400/30">
                    <span class="relative z-10">Mulai Kelola Klaim</span>
                    <div class="w-6 h-6 rounded-full bg-white text-indigo-600 flex items-center justify-center text-xs group-hover:translate-x-1 transition-transform relative z-10 shadow-sm">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>

                <!-- CTA 2: Alur Approval -->
                <a href="#approval-flow" class="btn-ripple relative overflow-hidden w-full sm:w-auto px-6 py-3.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-sm transition-all duration-200 shadow-[0_0_30px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center justify-center gap-2.5 cursor-pointer group border border-emerald-400/50">
                    <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-emerald-100 group-hover:rotate-12 transition-transform relative z-10">
                        <i class="fa-solid fa-diagram-project text-[11px]"></i>
                    </div>
                    <span class="relative z-10">Alur Approval</span>
                </a>

                <!-- CTA 3: FAQ -->
                <a href="#faq" class="btn-ripple relative overflow-hidden w-full sm:w-auto px-6 py-3.5 rounded-full bg-gradient-to-r from-cyan-500 via-sky-600 to-violet-600 hover:from-cyan-400 hover:via-sky-500 hover:to-violet-500 text-white font-bold text-sm transition-all duration-200 shadow-[0_0_30px_rgba(6,182,212,0.45)] hover:-translate-y-0.5 active:translate-y-0 active:scale-95 flex items-center justify-center gap-2.5 cursor-pointer group border border-cyan-400/50">
                    <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-cyan-100 group-hover:scale-110 transition-transform relative z-10">
                        <i class="fa-solid fa-circle-question text-[11px]"></i>
                    </div>
                    <span class="relative z-10">Pusat Bantuan FAQ</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 3. AURORA GRADIENT WRAPPER                                                -->
    <!-- ========================================================================= -->
    <div class="features-aurora-wrapper w-full transition-colors duration-300 relative z-10 -mt-10 pt-10">
        
        <!-- ALUR APPROVAL WORKFLOW -->
        <section id="approval-flow" class="max-w-7xl mx-auto px-6 pt-24 pb-16 text-center relative z-10">
            
            <div class="reveal-on-scroll inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/25 text-blue-600 dark:text-sky-400 text-xs font-black uppercase tracking-widest mb-4 shadow-sm">
                <i class="fa-solid fa-diagram-next text-blue-500"></i>
                Multi-Tier Approval Workflow Engine
            </div>
            <h2 class="reveal-on-scroll text-3xl sm:text-5xl font-black text-slate-900 dark:text-white mb-4 tracking-tight" style="transition-delay: 100ms;">
                Alur Persetujuan Klaim <span class="animate-shimmer">Berjenjang</span>
            </h2>
            <p class="reveal-on-scroll text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-16 leading-relaxed" style="transition-delay: 200ms;">
                Automasi persetujuan bebas birokrasi berbelit. Setiap step terhubung dengan protokol audit ketat dan SLA otomatis.
            </p>

            <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                
                <div class="hidden lg:block absolute top-[72px] left-[10%] right-[10%] h-1 animate-flow-beam rounded-full z-0 pointer-events-none opacity-60 dark:opacity-40"></div>

                <!-- STEP 1 -->
                <div class="reveal-on-scroll step-card p-6 rounded-[32px] bg-gradient-to-b from-sky-50/95 via-white/90 to-sky-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-sky-950/40 backdrop-blur-2xl border border-white/90 dark:border-sky-500/20 shadow-[0_10px_30px_rgba(56,189,248,0.1)] hover:border-sky-500/60 hover:-translate-y-2 hover:shadow-[0_20px_45px_rgba(56,189,248,0.25)] flex flex-col justify-between group transition-all duration-300 relative z-10 overflow-hidden" style="transition-delay: 100ms;">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-sky-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 text-white flex items-center justify-center font-black text-sm shadow-lg shadow-sky-500/30 group-hover:rotate-6 transition-transform">
                                01
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-700 dark:text-sky-300 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span> Karyawan
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2 tracking-tight">Upload & AI OCR</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Karyawan memfoto struk via aplikasi; Vision AI memproses merchant, nominal, tanggal, dan mendeteksi nota palsu secara instan.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400"><i class="fa-solid fa-bolt text-sky-500 mr-1"></i>0.8s Scan</span>
                        <button type="button" class="step-cta-btn px-3 py-1.5 rounded-xl bg-sky-500/10 dark:bg-sky-500/20 border border-sky-500/30 text-sky-600 dark:text-sky-300 text-xs font-bold hover:bg-sky-50 hover:text-white dark:hover:bg-sky-500 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-step="01" data-title="Tahap 01: Upload & AI OCR Extraction" data-badge="Karyawan Portal" data-desc="Karyawan cukup mengambil foto bukti fisik melalui kamera smartphone atau upload file PDF. Sistem OCR mengekstraksi data secara instan dan mengunci nilai nominal tanpa celah manipulasi angka.">
                            <span>Detail Alur</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="reveal-on-scroll step-card p-6 rounded-[32px] bg-gradient-to-b from-blue-50/95 via-white/90 to-blue-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-blue-950/40 backdrop-blur-2xl border border-white/90 dark:border-blue-500/20 shadow-[0_10px_30px_rgba(59,130,246,0.1)] hover:border-blue-500/60 hover:-translate-y-2 hover:shadow-[0_20px_45px_rgba(59,130,246,0.25)] flex flex-col justify-between group transition-all duration-300 relative z-10 overflow-hidden" style="transition-delay: 200ms;">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-sm shadow-lg shadow-blue-500/30 group-hover:rotate-6 transition-transform">
                                02
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-700 dark:text-blue-300 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Finance Officer
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2 tracking-tight">Validasi Tim Finance</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Pemeriksaan kesesuaian faktur pajak PPh/PPN, alokasi COA (Chart of Accounts) akun buku besar, dan validasi rekonsiliasi.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400"><i class="fa-solid fa-calculator text-blue-500 mr-1"></i>COA Auto-Map</span>
                        <button type="button" class="step-cta-btn px-3 py-1.5 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 border border-blue-500/30 text-blue-600 dark:text-blue-300 text-xs font-bold hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-step="02" data-title="Tahap 02: Validasi Dokumen & COA Finance" data-badge="Finance Verifier" data-desc="Staf keuangan mencocokkan kategori pengeluaran ke kode akun buku besar (COA). Dilengkapi pengecekan duplikasi otomatis untuk memastikan kwitansi belum pernah diklaim sebelumnya.">
                            <span>Detail Alur</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="reveal-on-scroll step-card p-6 rounded-[32px] bg-gradient-to-b from-indigo-50/95 via-white/90 to-indigo-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-indigo-950/40 backdrop-blur-2xl border border-white/90 dark:border-indigo-500/20 shadow-[0_10px_30px_rgba(99,102,241,0.1)] hover:border-indigo-500/60 hover:-translate-y-2 hover:shadow-[0_20px_45px_rgba(99,102,241,0.25)] flex flex-col justify-between group transition-all duration-300 relative z-10 overflow-hidden" style="transition-delay: 300ms;">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center font-black text-sm shadow-lg shadow-indigo-500/30 group-hover:rotate-6 transition-transform">
                                03
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span> Manager Divisi
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2 tracking-tight">Review Manager</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Atasan langsung memverifikasi urgensi kegiatan operasional dan memantau sisa kuota pagu anggaran departemen secara live.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400"><i class="fa-solid fa-shield-check text-indigo-500 mr-1"></i>Pagu Limit Rule</span>
                        <button type="button" class="step-cta-btn px-3 py-1.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-step="03" data-title="Tahap 03: Otorisasi & Budget Cap Manager" data-badge="Head / Manager" data-desc="Persetujuan digital multi-level dengan batas pagu anggaran. Jika nilai klaim melebihi batas toleransi divisi, sistem otomatis meminta approval tambahan ke level Direktur.">
                            <span>Detail Alur</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div class="reveal-on-scroll step-card p-6 rounded-[32px] bg-gradient-to-b from-emerald-50/95 via-white/90 to-emerald-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-emerald-950/40 backdrop-blur-2xl border border-white/90 dark:border-emerald-500/20 shadow-[0_10px_30px_rgba(16,185,129,0.1)] hover:border-emerald-500/60 hover:-translate-y-2 hover:shadow-[0_20px_45px_rgba(16,185,129,0.25)] flex flex-col justify-between group transition-all duration-300 relative z-10 overflow-hidden" style="transition-delay: 400ms;">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center font-black text-sm shadow-lg shadow-emerald-500/30 group-hover:rotate-6 transition-transform">
                                04
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-[10px] font-black uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Auto Settlement
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2 tracking-tight">Disbursement & Jurnal</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                            Dana langsung dicairkan via API Bank/Payment Gateway, dan jurnal penutupan kas kecil otomatis tercatat di buku besar.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400"><i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>Realtime Disbursed</span>
                        <button type="button" class="step-cta-btn px-3 py-1.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-600 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-step="04" data-title="Tahap 04: Auto Disbursement & Book Closing" data-badge="Auto Settlement" data-desc="Pencairan dana transfer bank langsung dieksekusi secara instan ke rekening karyawan. Jurnal kas kecil langsung tersinkronisasi ke laporan keuangan ERP tanpa input ulang.">
                            <span>Detail Alur</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- WORKFLOW SIMULATOR BANNER CTA -->
            <div class="reveal-on-scroll mt-12 p-8 rounded-[32px] bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 border border-blue-500/30 text-white shadow-2xl flex flex-col md:flex-row items-center justify-between gap-6 text-left relative overflow-hidden" style="transition-delay: 500ms;">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-sky-300 text-[11px] font-extrabold uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-gauge-high text-sky-400"></i>
                        Automated SLA Monitoring
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black tracking-tight">Kustomisasi Hierarki Sesuai Organisasi Anda</h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">Atur batas pagu nominal, delegasi persetujuan saat cuti, dan alur approval khusus antar anak perusahaan.</p>
                </div>
                <div class="relative z-10 flex items-center gap-3 shrink-0">
                    <a href="{{ route('login') }}" class="btn-ripple px-6 py-3.5 rounded-2xl bg-gradient-to-r from-sky-400 via-blue-600 to-indigo-600 hover:from-sky-300 hover:to-blue-500 text-white font-extrabold text-xs tracking-wide shadow-lg shadow-blue-600/40 active:scale-95 transition flex items-center gap-2">
                        <span>Konfigurasi Alur Tim</span>
                        <i class="fa-solid fa-sliders text-xs"></i>
                    </a>
                </div>
            </div>

        </section>

        <!-- FEATURES SECTION -->
        <main id="features" class="max-w-7xl mx-auto px-6 py-24 text-center relative z-10">
            
            <div class="reveal-on-scroll inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/25 text-sky-600 dark:text-sky-400 text-xs font-black uppercase tracking-widest mb-4 shadow-sm">
                <i class="fa-solid fa-wand-magic-sparkles text-sky-500"></i>
                AI-Powered Enterprise Capabilities
            </div>
            <h2 class="reveal-on-scroll text-3xl sm:text-5xl font-black text-slate-900 dark:text-white mb-4 tracking-tight" style="transition-delay: 100ms;">
                AI-Powered Features, Travel & Claim <br>
                <span class="animate-shimmer">Effortless With PettyClaim</span>
            </h2>
            <p class="reveal-on-scroll text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-16" style="transition-delay: 200ms;">
                Ekosistem otomatisasi terlengkap untuk verifikasi kas kecil real-time, deteksi fraud, dan integrasi akuntansi mutakhir.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                
                <!-- Card 1 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-br from-blue-600 via-indigo-600 to-blue-700 text-white shadow-2xl shadow-blue-600/30 flex flex-col justify-between group hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(37,99,235,0.45)] transition-all duration-300 relative overflow-hidden border border-blue-400/30" style="transition-delay: 100ms;">
                    <div class="absolute -right-8 -top-8 w-28 h-28 bg-white/10 rounded-full blur-xl group-hover:scale-150 transition-transform duration-500"></div>
                    <div>
                        <div class="flex items-center justify-between mb-5 relative z-10">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 group-hover:rotate-6 transition-transform">
                                <i class="fa-solid fa-camera text-base text-white"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider backdrop-blur-md">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> 99.4% Accuracy
                            </span>
                        </div>
                        <h4 class="text-lg font-black tracking-tight mb-2">Real-Time OCR AI</h4>
                        <p class="text-xs text-blue-100 leading-relaxed mb-6">Ekstraksi instan item nota, nominal, tanggal, dan nama toko dari kwitansi struk secara presisi.</p>
                    </div>
                    <div class="pt-4 border-t border-white/20 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-blue-200">Neural Vision 4.0</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-white text-blue-700 text-xs font-black shadow-md hover:bg-sky-50 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Real-Time OCR AI" data-desc="Model OCR generasi ke-4 kami mampu mengekstrak struk termal, nota tulisan tangan, serta faktur digital dalam hitungan 0.8 detik dengan akurasi 99.4%." data-badge="Neural Vision Core">
                            <span>Explore</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-sky-50/95 via-white/90 to-sky-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-sky-950/40 backdrop-blur-2xl border border-white/90 dark:border-sky-500/20 shadow-lg shadow-sky-500/5 hover:border-sky-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(56,189,248,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 200ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-sky-500/15 border border-sky-500/30 text-sky-600 dark:text-sky-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-location-dot text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-ping"></span> GPS Live
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Smart Route Claim</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Perhitungan reimbursement bensin dan tol berbasis kalkulasi kilometer rute riil.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-sky-600 dark:text-sky-400">Maps API Sync</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-sky-500/10 dark:bg-sky-500/20 border border-sky-500/30 text-sky-600 dark:text-sky-300 text-xs font-bold hover:bg-sky-500 hover:text-white dark:hover:bg-sky-500 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Smart Route Claim" data-desc="Integrasi Google Maps & Geolocation API untuk menghitung tarif per KM, tarif tol, dan konsumsi bahan bakar dinas secara presisi tanpa mark-up manual." data-badge="Live Telematics">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-purple-50/95 via-white/90 to-purple-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-purple-950/40 backdrop-blur-2xl border border-white/90 dark:border-purple-500/20 shadow-lg shadow-purple-500/5 hover:border-purple-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(168,85,247,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 300ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-purple-500/15 border border-purple-500/30 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-layer-group text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span> Multi-Tier
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Dynamic Tracking</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Pantau alur status klaim berjenjang secara live dari portal mobile dan desktop.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-purple-600 dark:text-purple-400">WebSocket Live</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 border border-purple-500/30 text-purple-600 dark:text-purple-300 text-xs font-bold hover:bg-purple-600 hover:text-white dark:hover:bg-purple-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Dynamic Tracking Workflow" data-desc="Audit posisi persetujuan klaim secara detik-ke-detik. Dilengkapi timeline interaktif dari input karyawan, persetujuan lead, hingga pencairan finance." data-badge="Live WebSockets">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-indigo-50/95 via-white/90 to-sky-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-indigo-950/40 backdrop-blur-2xl border border-white/90 dark:border-indigo-500/20 shadow-lg shadow-indigo-500/5 hover:border-indigo-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(99,102,241,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 400ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-bell text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span> Instant Push
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Personalized Alerts</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Notifikasi instan untuk pagu anggaran dan peringatan over-budget divisi.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400">Email & Slack Sync</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-500 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Personalized Alerts" data-desc="Kirim notifikasi otomatis ke WhatsApp Business, Slack, Microsoft Teams, dan Email saat klaim disetujui atau kuota anggaran mendekati pagu batas." data-badge="Omnichannel Push">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-teal-50/95 via-white/90 to-teal-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-teal-950/40 backdrop-blur-2xl border border-white/90 dark:border-teal-500/20 shadow-lg shadow-teal-500/5 hover:border-teal-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(20,184,166,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 500ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-teal-500/15 border border-teal-500/30 text-teal-600 dark:text-teal-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-compass text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-500/10 text-teal-600 dark:text-teal-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> CSV / XLSX
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Auto Journal Export</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Generate format jurnal akuntansi siap pakai ke dalam format CSV, Excel, atau PDF resmi.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-teal-600 dark:text-teal-400">COA Auto-Mapping</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-teal-500/10 dark:bg-teal-500/20 border border-teal-500/30 text-teal-600 dark:text-teal-300 text-xs font-bold hover:bg-teal-500 hover:text-white dark:hover:bg-teal-500 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Auto Journal Export Engine" data-desc="Sinkronisasi satu-klik ke software ERP (SAP, Oracle, Jurnal, Accurate) dengan pemetaan Chart of Accounts (COA) debit/kredit yang otomatis dan rapi." data-badge="ERP Ready">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-cyan-50/95 via-white/90 to-cyan-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-cyan-950/40 backdrop-blur-2xl border border-white/90 dark:border-cyan-500/20 shadow-lg shadow-cyan-500/5 hover:border-cyan-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(6,182,212,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 600ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-600 dark:text-cyan-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-wallet text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Cap Budget
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Petty Cash Limit</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Pembatasan pagu anggaran otomatis per divisi guna menghindari over-budgeting.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-cyan-600 dark:text-cyan-400">Imprest System</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/20 border border-cyan-500/30 text-cyan-600 dark:text-cyan-300 text-xs font-bold hover:bg-cyan-500 hover:text-white dark:hover:bg-cyan-500 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Petty Cash Limit & Imprest Flow" data-desc="Mendukung sistem kas kecil Fluktuasi dan Imprest Fund. Sistem otomatis mengunci pengajuan baru apabila batas plafon bulanan divisi telah tercapai." data-badge="Cost Control">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 7 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-indigo-50/95 via-white/90 to-indigo-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-indigo-950/40 backdrop-blur-2xl border border-white/90 dark:border-indigo-500/20 shadow-lg shadow-indigo-500/5 hover:border-indigo-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(99,102,241,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 700ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-shield-halved text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Immutable
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">Audit Trail Logs</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Pencatatan riwayat setiap aksi dan verifikasi klaim untuk kepatuhan audit perusahaan.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400">SOC-2 Certified</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 border border-indigo-500/30 text-indigo-600 dark:text-indigo-300 text-xs font-bold hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="Audit Trail Logs & Compliance" data-desc="Catatan log terenkripsi SHA-256 yang tidak dapat diubah (immutable). Memenuhi standard audit internal, KAP eksternal, dan kepatuhan perpajakan." data-badge="Enterprise Security">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Card 8 -->
                <div class="reveal-on-scroll feature-card p-6 rounded-[30px] bg-gradient-to-b from-emerald-50/95 via-white/90 to-emerald-100/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-emerald-950/40 backdrop-blur-2xl border border-white/90 dark:border-emerald-500/20 shadow-lg shadow-emerald-500/5 hover:border-emerald-500/60 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgba(16,185,129,0.2)] flex flex-col justify-between group transition-all duration-300 relative overflow-hidden" style="transition-delay: 800ms;">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-lock text-base"></i>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-300 text-[10px] font-extrabold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Disbursed
                            </span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-2">One-Click Settlement</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-6">Penyelesaian transfer reimbursement dan penutupan buku hanya dengan satu klik aman.</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400">Direct Bank API</span>
                        <button type="button" class="feature-explore-btn px-3.5 py-1.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-600 dark:text-emerald-300 text-xs font-bold hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 dark:hover:text-white active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer" data-title="One-Click Settlement & Disbursement" data-desc="Integrasi ke payment gateway dan API perbankan nasional untuk transfer reimbursement instan ke rekening karyawan secara massal (batch transfer)." data-badge="Bank Grade API">
                            <span>Learn more</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>

            </div>
        </main>

        <!-- ========================================================================= -->
        <!-- MODAL DRAWER PREVIEW FOR STEPS & FEATURES CTA                             -->
        <!-- ========================================================================= -->
        <div id="featureModal" class="fixed inset-0 z-[1000] bg-slate-950/60 backdrop-blur-md hidden flex items-center justify-center p-4 transition-all duration-300">
            <div class="relative w-full max-w-lg rounded-[32px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 shadow-2xl transform transition-all scale-95 opacity-0" id="featureModalCard">
                <button type="button" id="closeFeatureModal" class="absolute top-6 right-6 w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-red-500 hover:text-white dark:hover:bg-red-500 dark:hover:text-white transition flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
                <div class="inline-block px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-sky-400 text-[10px] font-black uppercase tracking-wider mb-3" id="modalBadge">
                    Module Capability
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-3" id="modalTitle">Feature Title</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-8" id="modalDesc">Detailed module explanation goes here.</p>
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 pt-5">
                    <button type="button" id="dismissFeatureModalBtn" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Tutup</button>
                    <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-black shadow-lg shadow-blue-500/30 hover:from-blue-500 hover:to-indigo-500 transition">Coba di Portal &rarr;</a>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- FAQ SECTION                                                               -->
        <!-- ========================================================================= -->
        <section id="faq" class="max-w-5xl mx-auto px-6 py-24 relative z-10">
            
            <div class="text-center mb-12">
                <div class="reveal-on-scroll inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-sky-500/10 border border-sky-500/25 text-sky-600 dark:text-sky-400 text-xs font-black uppercase tracking-widest mb-4 shadow-sm" style="transition-delay: 100ms;">
                    <i class="fa-solid fa-sparkles text-sky-500"></i>
                    AI-Powered Knowledge Center
                </div>
                <h2 class="reveal-on-scroll text-3xl sm:text-5xl font-black text-slate-900 dark:text-white mb-4 tracking-tight" style="transition-delay: 200ms;">
                    Frequently Asked <span class="animate-shimmer">Questions</span>
                </h2>
                <p class="reveal-on-scroll text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-2xl mx-auto" style="transition-delay: 300ms;">
                    Jawaban komprehensif seputar arsitektur keamanan, akurasi model OCR AI, alur approval multi-tier, dan integrasi akuntansi perusahaan.
                </p>
            </div>

            <div class="reveal-on-scroll mb-10 flex flex-col md:flex-row items-center justify-between gap-4" style="transition-delay: 400ms;">
                <div class="relative w-full md:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-xs"></i>
                    <input type="text" id="faqSearchInput" placeholder="Cari pertanyaan / topik..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all shadow-sm">
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-start md:justify-end text-[11px] font-bold">
                    <button type="button" class="faq-filter-btn px-3.5 py-1.5 rounded-xl bg-blue-600 text-white shadow-md shadow-blue-500/20 transition cursor-pointer" data-filter="all">Semua</button>
                    <button type="button" class="faq-filter-btn px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-sky-500 hover:text-sky-500 transition cursor-pointer" data-filter="ocr">OCR AI</button>
                    <button type="button" class="faq-filter-btn px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-sky-500 hover:text-sky-500 transition cursor-pointer" data-filter="approval">Approval</button>
                    <button type="button" class="faq-filter-btn px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-sky-500 hover:text-sky-500 transition cursor-pointer" data-filter="security">Keamanan</button>
                </div>
            </div>

            <div class="space-y-4 reveal-on-scroll" id="faqAccordionContainer" style="transition-delay: 500ms;">
                <div class="faq-item group p-6 rounded-[28px] bg-gradient-to-b from-white/95 via-white/85 to-sky-50/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-slate-950/60 backdrop-blur-2xl border border-white/90 dark:border-slate-800 shadow-[0_10px_30px_rgba(56,189,248,0.06)] hover:border-sky-500/50 dark:hover:border-sky-500/40 transition-all duration-300 cursor-pointer" data-category="ocr">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-600 dark:text-sky-400 text-xs font-black flex items-center justify-center shrink-0">01</span>
                            <div>
                                <span class="px-2 py-0.5 rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-400 text-[10px] font-extrabold uppercase tracking-wider mb-1 inline-block">OCR & Machine Learning</span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Bagaimana cara kerja teknologi OCR AI dalam membaca kwitansi?</h3>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:bg-sky-500 group-hover:text-white transition-all shrink-0">
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 faq-chevron"></i>
                        </div>
                    </div>
                    <div class="faq-answer hidden mt-4 pt-4 border-t border-slate-200/70 dark:border-slate-800/80 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-12">
                        Sistem OCR secara otomatis memindai gambar atau foto nota yang diunggah oleh karyawan, lalu mengekstrak data penting seperti nama merchant/toko, tanggal transaksi, nomor kwitansi, serta nominal total secara presisi tanpa input manual. Dilengkapi validasi multi-bahasa dan deteksi otomatis fraud duplicate-receipt.
                    </div>
                </div>

                <div class="faq-item group p-6 rounded-[28px] bg-gradient-to-b from-white/95 via-white/85 to-blue-50/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-slate-950/60 backdrop-blur-2xl border border-white/90 dark:border-slate-800 shadow-[0_10px_30px_rgba(59,130,246,0.06)] hover:border-blue-500/50 dark:hover:border-blue-500/40 transition-all duration-300 cursor-pointer" data-category="approval">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-600 dark:text-blue-400 text-xs font-black flex items-center justify-center shrink-0">02</span>
                            <div>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[10px] font-extrabold uppercase tracking-wider mb-1 inline-block">Approval Hierarchy</span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Apakah sistem mendukung approval multi-level sesuai hierarki divisi?</h3>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:bg-blue-600 group-hover:text-white transition-all shrink-0">
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 faq-chevron"></i>
                        </div>
                    </div>
                    <div class="faq-answer hidden mt-4 pt-4 border-t border-slate-200/70 dark:border-slate-800/80 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-12">
                        Ya. Pengajuan klaim akan dialirkan secara otomatis berjenjang mulai dari persetujuan Manajer/Lead Divisi hingga diverifikasi final oleh Tim Finance sebelum proses pencairan dana dilakukan. Rule approval dapat dikustomisasi per limit nominal tertentu.
                    </div>
                </div>

                <div class="faq-item group p-6 rounded-[28px] bg-gradient-to-b from-white/95 via-white/85 to-indigo-50/60 dark:from-slate-800/90 dark:via-slate-900/90 dark:to-slate-950/60 backdrop-blur-2xl border border-white/90 dark:border-slate-800 shadow-[0_10px_30px_rgba(99,102,241,0.06)] hover:border-indigo-500/50 dark:hover:border-indigo-500/40 transition-all duration-300 cursor-pointer" data-category="security">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="w-8 h-8 rounded-xl bg-indigo-500/15 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 text-xs font-black flex items-center justify-center shrink-0">03</span>
                            <div>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold uppercase tracking-wider mb-1 inline-block">Security & Export</span>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">Format apa saja yang didukung untuk ekspor laporan jurnal keuangan?</h3>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 faq-chevron"></i>
                        </div>
                    </div>
                    <div class="faq-answer hidden mt-4 pt-4 border-t border-slate-200/70 dark:border-slate-800/80 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-12">
                        Admin dapat mengekspor rekapitulasi jurnal pengeluaran kas kecil ke dalam format CSV, format spreadsheet Excel (.xlsx), serta dokumen PDF resmi bertandatangan digital yang siap dimasukkan ke buku besar akuntansi perusahaan.
                    </div>
                </div>
            </div>

            <!-- HIGH LEVEL SUPPORT CTA BENTO CARD -->
            <div class="reveal-on-scroll mt-14 p-8 sm:p-10 rounded-[32px] bg-gradient-to-r from-blue-700 via-indigo-600 to-violet-700 text-white shadow-2xl shadow-indigo-600/30 flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden group border border-indigo-400/30" style="transition-delay: 600ms;">
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
                <div class="relative z-10 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-cyan-200 text-[11px] font-extrabold uppercase tracking-wider mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Tim Support Online 24/7
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black tracking-tight">Masih Memiliki Pertanyaan Khusus?</h3>
                    <p class="text-xs sm:text-sm text-blue-100 mt-1 max-w-md">Diskusikan kebutuhan arsitektur sistem keuangan kantor Anda langsung bersama tim spesialis kami.</p>
                </div>
                <div class="relative z-10 flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <a href="mailto:support@pettyclaim.ai" class="btn-ripple w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white text-indigo-700 font-black text-xs hover:bg-sky-50 shadow-lg active:scale-95 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-headset text-sm"></i>
                        <span>Live Chat Support</span>
                    </a>
                    <a href="{{ route('login') }}" class="btn-ripple w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/30 text-white font-bold text-xs backdrop-blur-md active:scale-95 transition flex items-center justify-center gap-2">
                        <span>Hubungi Sales</span>
                    </a>
                </div>
            </div>

        </section>

    </div>

    <!-- ========================================================================= -->
    <!-- 4. FOOTER                                                                 -->
    <!-- ========================================================================= -->
    <footer class="relative z-10 overflow-hidden border-t border-blue-500/20 bg-gradient-to-b from-sky-100/80 via-indigo-50/70 to-slate-100/90 dark:from-[#030922] dark:via-[#020617] dark:to-[#010410] pt-16 pb-10 transition-colors duration-300">
        
        <div class="absolute -top-24 -left-20 w-96 h-96 bg-gradient-to-tr from-sky-400/30 to-blue-600/20 dark:from-sky-500/15 dark:to-blue-600/10 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
        <div class="absolute top-1/2 -right-20 w-96 h-96 bg-gradient-to-bl from-purple-500/25 to-pink-500/20 dark:from-indigo-600/15 dark:to-purple-800/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 left-1/3 w-80 h-80 bg-gradient-to-r from-teal-400/20 to-emerald-500/15 dark:from-teal-500/10 dark:to-emerald-700/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            
            <div class="reveal-on-scroll p-8 sm:p-10 rounded-[32px] bg-gradient-to-r from-white/80 via-sky-50/60 to-indigo-50/80 dark:from-slate-900/80 dark:via-slate-800/70 dark:to-sky-950/50 backdrop-blur-2xl border border-white dark:border-sky-500/20 shadow-[0_20px_50px_rgba(56,189,248,0.12)] mb-16 flex flex-col lg:flex-row items-center justify-between gap-8 group hover:border-sky-500/40 transition-all duration-300">
                <div class="max-w-xl text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-[11px] font-bold tracking-wide uppercase mb-3 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        All Systems Operational &bull; 99.98% Uptime
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Siap Mengotomasi Kas Kecil Kantor?
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                        Dapatkan demo interaktif, update fitur OCR terbaru, serta laporan keuangan bebas human-error sekarang.
                    </p>
                </div>
                
                <form onsubmit="event.preventDefault();" class="w-full lg:w-auto flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i class="fa-regular fa-envelope text-sm"></i>
                        </div>
                        <input type="email" placeholder="nama@perusahaan.com" class="w-full sm:w-72 pl-11 pr-4 py-3.5 rounded-2xl bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all shadow-inner">
                    </div>
                    <button type="submit" class="btn-ripple px-6 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 via-sky-500 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs tracking-wide shadow-lg shadow-sky-500/25 active:scale-95 transition-all flex items-center justify-center gap-2 group cursor-pointer whitespace-nowrap">
                        <span>Jadwalkan Demo</span>
                        <i class="fa-solid fa-arrow-trend-up text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                    </button>
                </form>
            </div>

            <div class="reveal-on-scroll grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-12 mb-16" style="transition-delay: 200ms;">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-5">
                        <!-- LOGO FOOTER REPLACEMENT -->
                        <img src="{{ asset('assets/logo_pettyclaim.png') }}" alt="Logo PettyClaim" class="w-11 h-11 rounded-xl object-cover border-2 border-sky-400 shadow-[0_0_15px_rgba(56,189,248,0.7)] dark:shadow-[0_0_20px_rgba(56,189,248,0.9)]">
                        <div>
                            <span class="text-2xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-indigo-600 to-sky-600 dark:from-white dark:via-sky-100 dark:to-sky-400">PettyClaim.Ai</span>
                            <div class="text-[9px] font-black uppercase tracking-widest text-sky-600 dark:text-sky-400">Next-Gen SaaS Finance</div>
                        </div>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed mb-6 max-w-sm">
                        Enterprise AI system untuk otomatisasi klaim struk, multi-level approval dinamis, dan sinkronisasi jurnal akuntansi kas kecil tanpa repot.
                    </p>
                    
                    <div class="flex items-center gap-3 mb-8">
                        <a href="#" aria-label="LinkedIn" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-gradient-to-br hover:from-blue-600 hover:to-sky-500 hover:text-white hover:border-transparent dark:hover:border-transparent hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 transition-all duration-300 group">
                            <i class="fa-brands fa-linkedin-in text-sm group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="#" aria-label="GitHub" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-gradient-to-br hover:from-purple-600 hover:to-indigo-600 hover:text-white hover:border-transparent dark:hover:border-transparent hover:-translate-y-1 hover:shadow-lg hover:shadow-purple-500/30 transition-all duration-300 group">
                            <i class="fa-brands fa-github text-sm group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="#" aria-label="YouTube" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-gradient-to-br hover:from-red-600 hover:to-rose-500 hover:text-white hover:border-transparent dark:hover:border-transparent hover:-translate-y-1 hover:shadow-lg hover:shadow-red-500/30 transition-all duration-300 group">
                            <i class="fa-brands fa-youtube text-sm group-hover:scale-110 transition-transform"></i>
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-700 dark:text-sky-300">
                            <i class="fa-solid fa-shield-halved text-[10px]"></i> SOC-2 Type II
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-700 dark:text-indigo-300">
                            <i class="fa-solid fa-lock text-[10px]"></i> 256-Bit SSL
                        </span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-2 h-4 rounded-full bg-gradient-to-b from-blue-500 to-sky-400"></div>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-xs uppercase tracking-widest">Product Solutions</h4>
                    </div>
                    <ul class="space-y-3.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-medium">
                        <li>
                            <a href="#features" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>AI OCR Kwitansi</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#approval-flow" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Multi-Tier Approval</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Smart Route Reimburse</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Auto Journal Export</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold">XLSX/PDF</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Audit Trail & Security</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-2 h-4 rounded-full bg-gradient-to-b from-purple-500 to-indigo-400"></div>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-xs uppercase tracking-widest">Resources</h4>
                    </div>
                    <ul class="space-y-3.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-medium">
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Dokumentasi API</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#faq" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Pusat Bantuan (FAQ)</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Panduan Integrasi ERP</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Komunitas Finance Lead</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Status Layanan Cloud</span>
                                <span class="px-2 py-0.5 rounded-md bg-sky-500/15 text-sky-600 dark:text-sky-400 text-[10px] font-bold">Live</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-2 h-4 rounded-full bg-gradient-to-b from-sky-500 to-indigo-500"></div>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-xs uppercase tracking-widest">Enterprise</h4>
                    </div>
                    <ul class="space-y-3.5 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-medium">
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Tentang Kami</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Karir & Lowongan</span>
                                <span class="px-2 py-0.5 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[9px] font-extrabold">HIRING</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Hubungi Tim Sales</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Kebijakan Privasi</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="hover:text-blue-600 dark:hover:text-sky-400 transition-colors flex items-center justify-between group">
                                <span>Syarat & Ketentuan</span>
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-sky-500"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="reveal-on-scroll pt-8 border-t border-slate-300/70 dark:border-slate-800/80 flex flex-col md:flex-row justify-between items-center gap-5" style="transition-delay: 400ms;">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span>&copy; 2026 PettyClaim.Ai Enterprise Inc. Hak Cipta Dilindungi Undang-Undang.</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/80 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:border-sky-500 transition shadow-sm cursor-pointer">
                        <i class="fa-solid fa-globe text-sky-500"></i>
                        <span>Indonesia (ID)</span>
                        <i class="fa-solid fa-angle-down text-[10px] text-slate-400"></i>
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/80 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 hover:border-sky-500 transition shadow-sm cursor-pointer">
                        <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                        <span>IDR (Rp)</span>
                    </button>
                </div>
            </div>

        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        // 1. Theme Toggle & Switch Indicator Synchronization
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleIndicator = document.getElementById('theme-toggle-indicator');
        const darkIcon = document.getElementById('theme-icon-dark');
        const lightIcon = document.getElementById('theme-icon-light');

        function updateToggleIcon() {
            if (document.documentElement.classList.contains('dark')) {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
                themeToggleIndicator.classList.add('translate-x-6');
            } else {
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
                themeToggleIndicator.classList.remove('translate-x-6');
            }
        }
        updateToggleIcon();

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
            updateToggleIcon();
        });

        // 2. Material Ripple Effect Generator
        document.querySelectorAll('.btn-ripple').forEach(button => {
            button.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const circle = document.createElement('span');
                const diameter = Math.max(rect.width, rect.height);
                const radius = diameter / 2;

                circle.style.width = circle.style.height = `${diameter}px`;
                circle.style.left = `${e.clientX - rect.left - radius}px`;
                circle.style.top = `${e.clientY - rect.top - radius}px`;
                circle.classList.add('ripple-span');

                const existingRipple = this.querySelector('.ripple-span');
                if (existingRipple) {
                    existingRipple.remove();
                }

                this.appendChild(circle);

                setTimeout(() => {
                    circle.remove();
                }, 600);
            });
        });

        // 4. Hero Ribbon Canvas Particle Effect
        const heroCanvas = document.getElementById('heroParticleCanvas');
        const heroCtx = heroCanvas.getContext('2d');
        let heroParticles = [];

        function resizeHeroCanvas() {
            if (heroCanvas.parentElement) {
                heroCanvas.width = heroCanvas.parentElement.offsetWidth;
                heroCanvas.height = heroCanvas.parentElement.offsetHeight;
            }
        }
        resizeHeroCanvas();
        window.addEventListener('resize', resizeHeroCanvas);

        class HeroParticle {
            constructor() {
                this.x = Math.random() * heroCanvas.width;
                this.y = Math.random() * heroCanvas.height;
                this.size = Math.random() * 1.5 + 0.5;
                this.speedX = (Math.random() - 0.5) * 0.3;
                this.speedY = (Math.random() - 0.5) * 0.3;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x < 0 || this.x > heroCanvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > heroCanvas.height) this.speedY *= -1;
            }
            draw() {
                heroCtx.fillStyle = 'rgba(56, 189, 248, 0.35)';
                heroCtx.beginPath();
                heroCtx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                heroCtx.fill();
            }
        }

        for (let i = 0; i < 50; i++) { heroParticles.push(new HeroParticle()); }

        function animateHeroParticles() {
            heroCtx.clearRect(0, 0, heroCanvas.width, heroCanvas.height);
            heroParticles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animateHeroParticles);
        }
        animateHeroParticles();

        // 5. Interactive Features & Steps Preview Modal Drawer
        const featureModal = document.getElementById('featureModal');
        const featureModalCard = document.getElementById('featureModalCard');
        const closeFeatureModal = document.getElementById('closeFeatureModal');
        const dismissFeatureModalBtn = document.getElementById('dismissFeatureModalBtn');
        const modalTitle = document.getElementById('modalTitle');
        const modalDesc = document.getElementById('modalDesc');
        const modalBadge = document.getElementById('modalBadge');

        function openModal(title, desc, badge) {
            modalTitle.innerText = title;
            modalDesc.innerText = desc;
            modalBadge.innerText = badge;
            featureModal.classList.remove('hidden');
            setTimeout(() => {
                featureModalCard.classList.remove('scale-95', 'opacity-0');
                featureModalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            featureModalCard.classList.remove('scale-100', 'opacity-100');
            featureModalCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                featureModal.classList.add('hidden');
            }, 250);
        }

        document.querySelectorAll('.feature-explore-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const title = btn.getAttribute('data-title');
                const desc = btn.getAttribute('data-desc');
                const badge = btn.getAttribute('data-badge');
                openModal(title, desc, badge);
            });
        });

        document.querySelectorAll('.step-cta-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const title = btn.getAttribute('data-title');
                const desc = btn.getAttribute('data-desc');
                const badge = btn.getAttribute('data-badge');
                openModal(title, desc, badge);
            });
        });

        closeFeatureModal.addEventListener('click', closeModal);
        dismissFeatureModalBtn.addEventListener('click', closeModal);
        featureModal.addEventListener('click', (e) => {
            if (e.target === featureModal) closeModal();
        });

        // 6. Interactive FAQ Accordion, Category Filter & Search Engine
        const faqItems = document.querySelectorAll('.faq-item');
        const filterBtns = document.querySelectorAll('.faq-filter-btn');
        const searchInput = document.getElementById('faqSearchInput');

        faqItems.forEach(item => {
            item.addEventListener('click', () => {
                const answer = item.querySelector('.faq-answer');
                const chevron = item.querySelector('.faq-chevron');
                const isOpen = !answer.classList.contains('hidden');

                faqItems.forEach(otherItem => {
                    otherItem.querySelector('.faq-answer').classList.add('hidden');
                    otherItem.querySelector('.faq-chevron').classList.remove('rotate-180');
                });

                if (!isOpen) {
                    answer.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            });
        });

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                    b.classList.add('bg-white', 'dark:bg-slate-900', 'text-slate-600', 'dark:text-slate-400');
                });
                btn.classList.add('bg-blue-600', 'text-white', 'shadow-md');
                btn.classList.remove('bg-white', 'dark:bg-slate-900', 'text-slate-600', 'dark:text-slate-400');

                const filter = btn.getAttribute('data-filter');
                faqItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            faqItems.forEach(item => {
                const text = item.innerText.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // 7. High-Tier SaaS Interactivity: Reveal On Scroll Engine
        const revealOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target); // Hanya trigger satu kali per elemen (SaaS Standard)
                }
            });
        }, revealOptions);

        document.querySelectorAll('.reveal-on-scroll').forEach((el) => {
            revealObserver.observe(el);
        });
    </script>
</body>
</html>