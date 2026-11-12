<style>
    /* ==========================================================
       UPGRADED ENTERPRISE SAAS SPLASH SCREEN - PRODUCTION TIER
       ========================================================== */
       
    /* 1. Holographic Laser Scan Effect */
    @keyframes scanLaser {
        0% { top: -100%; opacity: 0; }
        15% { opacity: 1; }
        85% { opacity: 1; }
        100% { top: 200%; opacity: 0; }
    }
    .animate-ocr-scan {
        position: absolute;
        left: 0;
        width: 100%;
        height: 50%;
        background: linear-gradient(to bottom, transparent, rgba(56, 189, 248, 0.4), rgba(56, 189, 248, 0.95), rgba(56, 189, 248, 0.4), transparent);
        animation: scanLaser 1.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        pointer-events: none;
        z-index: 20;
    }

    /* 2. Concentric Radar Pulse */
    @keyframes radarPulse {
        0% { transform: scale(0.85); opacity: 0.9; border-width: 2px; }
        100% { transform: scale(2.4); opacity: 0; border-width: 0px; }
    }
    .radar-ring {
        position: absolute;
        border-radius: 50%;
        border: 2px solid rgba(56, 189, 248, 0.5);
        animation: radarPulse 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
        pointer-events: none;
    }
    .radar-ring:nth-child(2) { animation-delay: 0.6s; }
    .radar-ring:nth-child(3) { animation-delay: 1.2s; }

    /* 3. Cinematic Exit Transition */
    .splash-cinematic-exit {
        opacity: 0 !important;
        transform: scale(1.12) !important;
        filter: blur(25px) !important;
        pointer-events: none;
        transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), 
                    transform 1s cubic-bezier(0.16, 1, 0.3, 1), 
                    filter 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* 4. Smooth Status Text Reveal */
    .status-text-slide {
        animation: slideUpFade 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- MAIN HIGH-LEVEL ENTERPRISE SPLASH WRAPPER -->
<div id="enterprise-splash" class="fixed inset-0 z-[9999] bg-[#f8faff] dark:bg-[#020617] flex flex-col items-center justify-center overflow-hidden transition-all duration-300 transform scale-100 opacity-100">
    
    <!-- A. NEURAL DATA STREAM CANVAS -->
    <canvas id="splashNeuralCanvas" class="absolute inset-0 w-full h-full z-0 opacity-75 dark:opacity-45"></canvas>
    
    <!-- Ambient Glow Hubs -->
    <div class="absolute w-[650px] h-[650px] rounded-full bg-gradient-to-r from-sky-400/20 via-blue-600/15 to-indigo-600/10 dark:from-sky-500/15 dark:via-blue-600/10 dark:to-indigo-900/25 blur-[90px] animate-pulse pointer-events-none z-0"></div>

    <div class="relative z-10 flex flex-col items-center justify-center w-full max-w-md px-6">
        
        <!-- B. LOGO WITH OCR LASER & RADAR HUD -->
        <div class="relative flex items-center justify-center mb-10">
            <!-- Radar Rings -->
            <div class="radar-ring absolute inset-0 w-full h-full"></div>
            <div class="radar-ring absolute inset-0 w-full h-full"></div>
            
            <!-- Logo Box -->
            <div class="relative w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl p-3.5 shadow-[0_0_60px_rgba(56,189,248,0.35)] border border-white/60 dark:border-sky-500/35 overflow-hidden flex items-center justify-center group z-10">
                <div class="absolute inset-0 bg-gradient-to-tr from-sky-500/10 to-indigo-500/10"></div>
                <div class="animate-ocr-scan"></div>
                <img src="{{ asset('assets/logo_pettyclaim.png') }}" alt="PettyClaim Logo" class="w-full h-full rounded-2xl object-cover relative z-10 transform scale-95 group-hover:scale-105 transition-transform duration-700 ease-out drop-shadow-2xl">
            </div>
        </div>

        <!-- C. BRANDING & DYNAMIC TERMINAL STATUS -->
        <div class="flex flex-col items-center justify-center w-full mb-8">
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white mb-2 flex items-center gap-1">
                Petty<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-sky-400 dark:from-sky-400 dark:to-blue-500">Claim.Ai</span>
            </h1>
            
            <div class="h-5 flex items-center justify-center overflow-hidden w-full mb-1">
                <p id="splash-status-text" class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-slate-500 dark:text-slate-400 text-center"></p>
            </div>
        </div>

        <!-- D. PRECISION FILLED PROGRESS BAR & METRICS -->
        <div class="w-full sm:w-[350px] flex flex-col gap-2.5">
            <div class="flex items-center justify-between w-full text-[10px] sm:text-xs font-mono font-bold text-slate-500 dark:text-slate-400 px-1 uppercase tracking-wider">
                <span class="flex items-center gap-1.5 text-blue-600 dark:text-sky-400">
                    <i class="fa-solid fa-microchip animate-pulse"></i>
                    <span>Secure Core Init</span>
                </span>
                <span id="splash-percentage" class="text-slate-900 dark:text-white font-extrabold text-sm">0%</span>
            </div>
            
            <!-- Exact Width-Controlled Bar Container -->
            <div class="w-full h-2 bg-slate-200/70 dark:bg-slate-800/90 rounded-full overflow-hidden relative shadow-inner p-0.5 border border-slate-300/30 dark:border-sky-500/20">
                <!-- Inner Bar with Real-Time Width Injection -->
                <div id="enterprise-progress-fill" class="h-full rounded-full bg-gradient-to-r from-sky-400 via-blue-500 to-indigo-500 dark:from-sky-300 dark:via-blue-400 dark:to-indigo-400 shadow-[0_0_15px_rgba(56,189,248,0.9)] transition-all duration-75 ease-out" style="width: 0%;"></div>
            </div>
            
            <div class="flex items-center justify-between w-full text-[9px] font-semibold text-slate-400 dark:text-slate-500 px-1 mt-1 uppercase">
                <span>Enterprise Ed. v2.4</span>
                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-lock"></i> AES-256 Encrypted</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        
        // --- 1. HIGH-TIER NEURAL CANVAS STREAM ---
        const sCanvas = document.getElementById('splashNeuralCanvas');
        const sCtx = sCanvas.getContext('2d');
        let splashNodes = [];

        function resizeSCanvas() {
            sCanvas.width = window.innerWidth;
            sCanvas.height = window.innerHeight;
        }
        resizeSCanvas();
        window.addEventListener('resize', resizeSCanvas);

        class NeuralNode {
            constructor() {
                this.x = Math.random() * sCanvas.width;
                this.y = Math.random() * sCanvas.height;
                this.size = Math.random() * 1.8 + 0.6;
                this.speedX = (Math.random() - 0.5) * 0.4;
                this.speedY = -Math.random() * 1.2 - 0.3; 
                this.hue = Math.random() * 40 + 195;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.x < 0) this.x = sCanvas.width;
                if (this.x > sCanvas.width) this.x = 0;
                if (this.y < 0) this.y = sCanvas.height; 
            }
            draw() {
                const isDark = document.documentElement.classList.contains('dark');
                sCtx.fillStyle = isDark ? `hsla(${this.hue}, 90%, 70%, 0.65)` : `hsla(${this.hue}, 85%, 50%, 0.5)`;
                sCtx.beginPath();
                sCtx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                sCtx.fill();
            }
        }

        for (let i = 0; i < 65; i++) { splashNodes.push(new NeuralNode()); }

        function animateNeuralNodes() {
            sCtx.clearRect(0, 0, sCanvas.width, sCanvas.height);
            const isDark = document.documentElement.classList.contains('dark');

            for (let i = 0; i < splashNodes.length; i++) {
                for (let j = i + 1; j < splashNodes.length; j++) {
                    const dx = splashNodes[i].x - splashNodes[j].x;
                    const dy = splashNodes[i].y - splashNodes[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < 130) {
                        const alpha = (1 - dist / 130) * (isDark ? 0.35 : 0.2);
                        sCtx.strokeStyle = isDark ? `rgba(56, 189, 248, ${alpha})` : `rgba(37, 99, 235, ${alpha})`;
                        sCtx.lineWidth = 0.8;
                        sCtx.beginPath();
                        sCtx.moveTo(splashNodes[i].x, splashNodes[i].y);
                        sCtx.lineTo(splashNodes[j].x, splashNodes[j].y);
                        sCtx.stroke();
                    }
                }
            }

            splashNodes.forEach(node => { node.update(); node.draw(); });
            requestAnimationFrame(animateNeuralNodes);
        }
        animateNeuralNodes();

        // --- 2. PRECISION WIDTH-INJECTION PROGRESS & STATUS LOGIC ---
        const statusTextElement = document.getElementById('splash-status-text');
        const percentageElement = document.getElementById('splash-percentage');
        const progressFill = document.getElementById('enterprise-progress-fill');
        
        const statuses = [
            "Initializing Enterprise Core...",
            "Loading Neural OCR Models...",
            "Establishing Secure Ledger...",
            "Verifying Compliance Protocol...",
            "System Ready."
        ];
        
        let currentStatusIndex = 0;
        
        function updateStatus() {
            if (currentStatusIndex < statuses.length) {
                statusTextElement.innerText = statuses[currentStatusIndex];
                statusTextElement.classList.remove('status-text-slide');
                void statusTextElement.offsetWidth; 
                statusTextElement.classList.add('status-text-slide');
                currentStatusIndex++;
            }
        }

        updateStatus();
        const statusInterval = setInterval(updateStatus, 600);

        // Precise Percentage & Width Synchronization (0% to 100% in 2.8s)
        let currentPercent = 0;
        const totalDuration = 2800; 
        const frameRate = 20; // 20ms updates
        const totalFrames = totalDuration / frameRate;
        const percentStep = 100 / totalFrames;

        const progressInterval = setInterval(() => {
            currentPercent += percentStep;
            if (currentPercent >= 100) {
                currentPercent = 100;
                clearInterval(progressInterval);
                clearInterval(statusInterval);
                statusTextElement.innerText = "System Ready.";
                statusTextElement.classList.add('text-emerald-500');
            }
            
            const roundedVal = Math.floor(currentPercent);
            percentageElement.innerText = roundedVal + "%";
            progressFill.style.width = roundedVal + "%"; // Menjamin garis terisi penuh secara akurat
        }, frameRate);

        // --- 3. CINEMATIC EXIT TRIGGER ---
        setTimeout(() => {
            const splashScreen = document.getElementById('enterprise-splash');
            if(splashScreen) {
                splashScreen.classList.add('splash-cinematic-exit');
                setTimeout(() => { 
                    splashScreen.remove(); 
                }, 1000);
            }
        }, 3400);

    });
</script>