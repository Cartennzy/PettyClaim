<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Portal - PettyClaim Admin</title>

    <!-- Favicon Logo PettyClaim -->
    <link rel="icon" type="image/png" href="{{ asset('logo_pettyclaim.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
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

        /* HIGH-TIER CYBER GRID BACKGROUND PATTERN */
        .bg-cyber-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(56, 189, 248, 0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(56, 189, 248, 0.07) 1px, transparent 1px);
        }
        .dark .bg-cyber-grid {
            background-image: 
                linear-gradient(to right, rgba(56, 189, 248, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(56, 189, 248, 0.05) 1px, transparent 1px);
        }

        /* LASER SCANNING BEAM ANIMATION */
        @keyframes scanBeam {
            0% { transform: translateY(-100%); opacity: 0; }
            50% { opacity: 0.8; }
            100% { transform: translateY(1000%); opacity: 0; }
        }
        .animate-scan-beam {
            animation: scanBeam 8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        /* MULTI-ORB AURORA ROTATION & BREATHING GLOW */
        @keyframes orbMorph {
            0%, 100% { transform: translate(0px, 0px) scale(1) rotate(0deg); opacity: 0.55; }
            33% { transform: translate(40px, -50px) scale(1.2) rotate(120deg); opacity: 0.8; }
            66% { transform: translate(-30px, 30px) scale(0.9) rotate(240deg); opacity: 0.6; }
        }
        .animate-aurora-1 { animation: orbMorph 12s ease-in-out infinite; }
        .animate-aurora-2 { animation: orbMorph 16s ease-in-out infinite reverse; }

        /* ERROR SHAKE ANIMATION */
        @keyframes shakeCard {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        .animate-shake {
            animation: shakeCard 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        /* 3D PERSPECTIVE CONTAINER FOR PARALLAX */
        .perspective-container {
            perspective: 1200px;
        }
        .tilt-card {
            transform-style: preserve-3d;
            transition: transform 0.15s cubic-bezier(0.2, 0, 0.2, 1), box-shadow 0.3s ease;
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #1e293b; }
        ::-webkit-scrollbar-thumb:hover { background: #38bdf8; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-[#020617] text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden bg-cyber-grid selection:bg-sky-500 selection:text-white transition-colors duration-300">

    <div class="fixed top-5 right-5 z-40">
        <button id="theme-toggle" type="button" aria-label="Toggle Theme" class="relative w-14 h-8 rounded-full p-1 bg-white/80 dark:bg-slate-900/80 border border-slate-300/80 dark:border-sky-500/30 backdrop-blur-xl transition-all duration-300 cursor-pointer focus:outline-none focus:ring-2 focus:ring-sky-400 flex items-center justify-between shadow-lg hover:shadow-sky-500/10">
            <i class="fa-solid fa-sun text-[11px] text-amber-500 ml-1"></i>
            <i class="fa-solid fa-moon text-[11px] text-sky-400 mr-1"></i>
            <span id="theme-toggle-indicator" class="absolute left-1 top-1 w-6 h-6 rounded-full bg-white dark:bg-sky-400 shadow-md flex items-center justify-center transform transition-transform duration-300">
                <i id="theme-icon-dark" class="fa-solid fa-moon text-[10px] text-sky-950 hidden"></i>
                <i id="theme-icon-light" class="fa-solid fa-sun text-[10px] text-amber-500 hidden"></i>
            </span>
        </button>
    </div>

    <canvas id="loginCanvas" class="fixed inset-0 w-full h-full opacity-60 dark:opacity-45 pointer-events-none z-0"></canvas>
    
    <div class="fixed top-1/4 left-1/3 w-[550px] h-[550px] bg-gradient-to-tr from-sky-400/30 via-blue-600/20 to-purple-600/20 dark:from-sky-500/20 dark:via-blue-600/15 dark:to-indigo-800/25 rounded-full blur-[100px] pointer-events-none z-0 animate-aurora-1"></div>
    <div class="fixed bottom-1/4 right-1/4 w-[450px] h-[450px] bg-gradient-to-br from-cyan-400/25 via-indigo-500/20 to-emerald-400/15 dark:from-cyan-500/15 dark:via-indigo-600/15 dark:to-sky-900/20 rounded-full blur-[90px] pointer-events-none z-0 animate-aurora-2"></div>
    
    <div class="fixed inset-x-0 top-0 h-40 bg-gradient-to-b from-sky-400/10 via-sky-500/5 to-transparent pointer-events-none z-0 animate-scan-beam"></div>

    <div class="w-full max-w-md relative z-10 perspective-container">
        <div class="absolute -inset-2 rounded-[32px] bg-gradient-to-r from-sky-400/30 via-blue-600/25 to-indigo-500/30 blur-2xl opacity-75 -z-10 animate-pulse"></div>

        <div id="loginCard" class="tilt-card bg-white/90 dark:bg-slate-900/85 backdrop-blur-2xl border border-white dark:border-slate-800/90 rounded-3xl p-7 sm:p-9 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] dark:shadow-[0_25px_60px_-15px_rgba(2,6,23,0.8)] transition-all duration-300 {{ $errors->any() ? 'animate-shake' : '' }}">
            
            <div class="text-center mb-8">
                <div class="relative w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-400 via-blue-600 to-indigo-600 flex items-center justify-center p-0.5 mx-auto mb-4 shadow-xl shadow-sky-500/30 group">
                    <div class="absolute inset-0 rounded-2xl bg-sky-400 blur-md opacity-40 group-hover:opacity-75 transition-opacity"></div>
                    <div class="relative w-full h-full bg-white dark:bg-[#070e30] rounded-[14px] flex items-center justify-center">
                        <svg class="w-7 h-7 text-sky-500 dark:text-sky-400 drop-shadow-[0_0_12px_rgba(56,189,248,0.7)] transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Portal Admin</h2>
                <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 font-medium">Masuk untuk mengelola sistem PettyClaim</p>
            </div>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-xl bg-slate-50/90 dark:bg-slate-950/70 border {{ $errors->has('email') ? 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-sky-500 focus:ring-sky-500/20' }} text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-medium outline-none focus:ring-4 transition duration-200"
                            placeholder="admin@pettyclaim.com">
                        
                        @if($errors->has('email'))
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-rose-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    @error('email')
                        <div class="mt-2 flex items-center gap-1.5 text-rose-600 dark:text-rose-400 text-[11px] font-semibold bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 px-3 py-1.5 rounded-lg">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 rounded-xl bg-slate-50/90 dark:bg-slate-950/70 border {{ $errors->has('password') ? 'border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-sky-500 focus:ring-sky-500/20' }} text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-xs font-medium outline-none focus:ring-4 transition duration-200"
                            placeholder="••••••••">

                        @if($errors->has('password'))
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-rose-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    @error('password')
                        <div class="mt-2 flex items-center gap-1.5 text-rose-600 dark:text-rose-400 text-[11px] font-semibold bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 px-3 py-1.5 rounded-lg">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <button type="submit"
                    class="btn-ripple w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-sky-400 via-sky-500 to-blue-600 hover:from-sky-300 hover:to-blue-500 text-slate-950 font-black text-xs uppercase tracking-wider transition duration-300 shadow-xl shadow-sky-500/25 active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                    <span>Masuk ke Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-200 dark:border-slate-800/80 pt-5">
                <a href="{{ route('landing') }}" class="text-xs text-slate-500 dark:text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 font-semibold transition duration-200 inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Landing Page</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        // 1. Theme Toggle & Switch Indicator Sync
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

        // 2. High-Tier Interactive Magnetic Particle Constellation Engine
        const canvas = document.getElementById('loginCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let mouse = { x: null, y: null, radius: 170 };

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        window.addEventListener('mousemove', (e) => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });

        window.addEventListener('mouseleave', () => {
            mouse.x = null;
            mouse.y = null;
        });

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2 + 0.8;
                this.baseX = this.x;
                this.baseY = this.y;
                this.speedX = (Math.random() - 0.5) * 0.7;
                this.speedY = (Math.random() - 0.5) * 0.7;
                this.hue = Math.random() * 50 + 190; // Sky blue to deep cobalt
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;

                // Mouse Magnet Attraction & Repulsion
                if (mouse.x !== null && mouse.y !== null) {
                    let dx = mouse.x - this.x;
                    let dy = mouse.y - this.y;
                    let distance = Math.sqrt(dx * dx + dy * dy);
                    if (distance < mouse.radius) {
                        let force = (mouse.radius - distance) / mouse.radius;
                        let directionX = (dx / distance) * force * 3;
                        let directionY = (dy / distance) * force * 3;
                        this.x += directionX;
                        this.y += directionY;
                    }
                }
            }
            draw() {
                const isDark = document.documentElement.classList.contains('dark');
                ctx.fillStyle = isDark ? `hsla(${this.hue}, 90%, 65%, 0.7)` : `hsla(${this.hue}, 85%, 45%, 0.65)`;
                ctx.shadowBlur = 8;
                ctx.shadowColor = `hsl(${this.hue}, 90%, 60%)`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
                ctx.shadowBlur = 0;
            }
        }

        for (let i = 0; i < 70; i++) {
            particles.push(new Particle());
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            const isDark = document.documentElement.classList.contains('dark');

            // Draw Constellation Connecting Lines
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    let dx = particles[i].x - particles[j].x;
                    let dy = particles[i].y - particles[j].y;
                    let dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 120) {
                        let alpha = (1 - dist / 120) * (isDark ? 0.35 : 0.25);
                        ctx.strokeStyle = isDark ? `rgba(56, 189, 248, ${alpha})` : `rgba(37, 99, 235, ${alpha})`;
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }

                // Connect particles directly to Mouse cursor if nearby
                if (mouse.x !== null && mouse.y !== null) {
                    let mdx = particles[i].x - mouse.x;
                    let mdy = particles[i].y - mouse.y;
                    let mdist = Math.sqrt(mdx * mdx + mdy * mdy);
                    if (mdist < mouse.radius) {
                        let mAlpha = (1 - mdist / mouse.radius) * 0.55;
                        ctx.strokeStyle = `rgba(56, 189, 248, ${mAlpha})`;
                        ctx.lineWidth = 1.2;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(mouse.x, mouse.y);
                        ctx.stroke();
                    }
                }
            }

            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animateParticles);
        }
        animateParticles();

        // 3. Interactive 3D Card Parallax Tilt Effect
        const card = document.getElementById('loginCard');
        window.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.clientX) / 35;
            const yAxis = (window.innerHeight / 2 - e.clientY) / 35;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });

        window.addEventListener('mouseleave', () => {
            card.style.transform = `rotateY(0deg) rotateX(0deg)`;
        });
    </script>
</body>
</html>