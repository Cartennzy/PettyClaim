<div x-data="{
    toasts: [],
    addToast(toast) {
        const id = Date.now() + Math.random();
        const newToast = {
            id,
            type: toast.type || 'info', // 'success', 'error', 'warning', 'info'
            title: toast.title || (toast.type === 'success' ? 'Berhasil' : (toast.type === 'error' ? 'Gagal' : 'Informasi')),
            message: toast.message || '',
            duration: toast.duration || 4000,
            progress: 100,
            isPaused: false,
            interval: null
        };

        this.toasts.push(newToast);

        this.$nextTick(() => {
            const step = 25;
            const decrement = (step / newToast.duration) * 100;
            
            newToast.interval = setInterval(() => {
                if (!newToast.isPaused) {
                    newToast.progress -= decrement;
                    if (newToast.progress <= 0) {
                        this.removeToast(newToast.id);
                    }
                }
            }, step);
        });
    },
    removeToast(id) {
        const index = this.toasts.findIndex(t => t.id === id);
        if (index > -1) {
            clearInterval(this.toasts[index].interval);
            this.toasts.splice(index, 1);
        }
    }
}" 
@notify.window="addToast($event.detail)"
class="fixed top-6 right-6 z-[99999] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-12 opacity-0 scale-95"
             x-transition:enter-end="translate-x-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0 opacity-100 scale-100"
             x-transition:leave-end="translate-x-12 opacity-0 scale-95"
             @mouseenter="toast.isPaused = true"
             @mouseleave="toast.isPaused = false"
             class="pointer-events-auto w-full rounded-2xl bg-slate-900/95 backdrop-blur-xl border border-slate-800 shadow-[0_20px_50px_rgba(0,0,0,0.6)] overflow-hidden relative group">
            
            <div class="p-4 flex items-start gap-3.5">
                <!-- Icon Indicator Badge with Glow -->
                <div class="shrink-0 p-2 rounded-xl border"
                     :class="{
                         'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.2)]': toast.type === 'success',
                         'bg-rose-500/10 border-rose-500/30 text-rose-400 shadow-[0_0_15px_rgba(239,68,68,0.2)]': toast.type === 'error',
                         'bg-amber-500/10 border-amber-500/30 text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.2)]': toast.type === 'warning',
                         'bg-sky-500/10 border-sky-500/30 text-sky-400 shadow-[0_0_15px_rgba(56,189,248,0.2)]': toast.type === 'info'
                     }">
                    <i class="text-sm"
                       :class="{
                           'fa-solid fa-circle-check': toast.type === 'success',
                           'fa-solid fa-triangle-exclamation': toast.type === 'error',
                           'fa-solid fa-circle-exclamation': toast.type === 'warning',
                           'fa-solid fa-circle-info': toast.type === 'info'
                       }"></i>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pt-0.5">
                    <h5 class="text-xs font-bold text-white tracking-tight" x-text="toast.title"></h5>
                    <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed break-words font-medium" x-text="toast.message"></p>
                </div>

                <!-- Close Button -->
                <button @click="removeToast(toast.id)" 
                        class="shrink-0 text-slate-500 hover:text-slate-200 p-1 rounded-lg hover:bg-slate-800/80 transition">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>

            <!-- Animated SaaS Countdown Bar -->
            <div class="w-full bg-slate-800/40 h-1 relative overflow-hidden">
                <div class="h-full transition-all duration-75 ease-linear rounded-full"
                     :style="`width: ${toast.progress}%;`"
                     :class="{
                         'bg-gradient-to-r from-emerald-500 to-teal-400': toast.type === 'success',
                         'bg-gradient-to-r from-rose-500 to-red-400': toast.type === 'error',
                         'bg-gradient-to-r from-amber-500 to-orange-400': toast.type === 'warning',
                         'bg-gradient-to-r from-sky-500 to-indigo-400': toast.type === 'info'
                     }"></div>
            </div>
        </div>
    </template>
</div>