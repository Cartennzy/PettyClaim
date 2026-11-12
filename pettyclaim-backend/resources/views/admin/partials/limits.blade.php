<template x-if="activeTab === 'limits' || activeTab === 'batas'">
    <div x-data="{ 
        limitValue: '{{ $claimLimit ?? 5000000 }}',
        totalUsed: {{ $totalApprovedAmount ?? 0 }},
        isSaving: false,
        
        // Mengambil nama bulan dan tahun berjalan otomatis
        get currentMonthName() {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const now = new Date();
            return months[now.getMonth()] + ' ' + now.getFullYear();
        },

        formatRupiah(val) {
            if (!val || isNaN(val)) return 'Rp 0';
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        },

        get remainingBudget() {
            const limit = Number(this.limitValue) || 0;
            return Math.max(0, limit - this.totalUsed);
        },

        get usagePercentage() {
            const limit = Number(this.limitValue) || 0;
            if (limit <= 0) return 0;
            return Math.min(100, Math.round((this.totalUsed / limit) * 100));
        },

        setPreset(val) {
            this.limitValue = val;
        },

        async saveLimit() {
            const isDarkTheme = document.documentElement.classList.contains('dark');
            const formattedNominal = this.formatRupiah(this.limitValue);

            const confirmResult = await Swal.fire({
                title: 'Konfirmasi Perubahan Pagu',
                html: `Apakah Anda yakin ingin menetapkan batas anggaran kas kecil bulan <b class='text-indigo-500'>${this.currentMonthName}</b> menjadi <br><b class='text-indigo-500 text-base'>${formattedNominal}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                background: isDarkTheme ? '#0f172a' : '#ffffff',
                color: isDarkTheme ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl'
                }
            });

            if (!confirmResult.isConfirmed) return;

            this.isSaving = true;
            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '{{ csrf_token() }}';
                const response = await fetch('{{ route('admin.limits.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ limit: this.limitValue })
                });
                
                const result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({
                        title: 'Berhasil Disimpan!',
                        text: `Batas pagu kas perusahaan periode ${this.currentMonthName} berhasil diperbarui ke ${formattedNominal}.`,
                        icon: 'success',
                        background: isDarkTheme ? '#0f172a' : '#ffffff',
                        color: isDarkTheme ? '#f8fafc' : '#0f172a',
                        confirmButtonColor: '#10b981',
                        timer: 2500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal Menyimpan',
                        text: result.message || 'Terjadi kesalahan saat memperbarui batas kas.',
                        icon: 'error',
                        background: isDarkTheme ? '#0f172a' : '#ffffff',
                        color: isDarkTheme ? '#f8fafc' : '#0f172a',
                        confirmButtonColor: '#ef4444',
                        customClass: {
                            popup: 'rounded-2xl border border-slate-200 dark:border-slate-800'
                        }
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Kesalahan Jaringan',
                    text: 'Tidak dapat menghubungi server database.',
                    icon: 'error',
                    background: isDarkTheme ? '#0f172a' : '#ffffff',
                    color: isDarkTheme ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#ef4444',
                    customClass: {
                        popup: 'rounded-2xl border border-slate-200 dark:border-slate-800'
                    }
                });
            } finally {
                this.isSaving = false;
            }
        }
    }" class="space-y-6 select-none transition-colors duration-200">

        <!-- KARTU MONITORING ANGGARAN DENGAN KETERANGAN BULAN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Card 1: Pagu Kas Perusahaan (Solid Sky Blue) -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-sky-600 dark:bg-sky-700 text-white shadow-sm border border-sky-500/30">
                <i class="fa-solid fa-building-columns absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-sky-100">Pagu Kas Perusahaan</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-building-columns text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white font-mono" 
                     x-text="formatRupiah(limitValue)">
                    Rp {{ number_format($claimLimit ?? 5000000, 0, ',', '.') }}
                </div>
                <div class="relative z-10 mt-3 flex items-center gap-1.5 text-xs text-sky-100">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold">Ketetapan Bulanan</span>
                    <span class="text-white font-semibold" x-text="'Bulan ' + currentMonthName"></span>
                </div>
            </div>

            <!-- Card 2: Total Kas Terpakai (Solid Amber) -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-amber-600 dark:bg-amber-700 text-white shadow-sm border border-amber-500/30">
                <i class="fa-solid fa-receipt absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-100">Total Kas Disetujui</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-receipt text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white font-mono" 
                     x-text="formatRupiah(totalUsed)">
                    Rp {{ number_format($totalApprovedAmount ?? 0, 0, ',', '.') }}
                </div>
                <div class="relative z-10 mt-3 flex items-center gap-1.5 text-xs text-amber-100">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold" x-text="usagePercentage + '% Terpakai'"></span>
                    <span class="text-white font-semibold" x-text="'di ' + currentMonthName"></span>
                </div>
            </div>

            <!-- Card 3: Sisa Anggaran Tersedia (Solid Emerald) -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-emerald-600 dark:bg-emerald-700 text-white shadow-sm border border-emerald-500/30">
                <i class="fa-solid fa-vault absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Sisa Anggaran Tersedia</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-vault text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white font-mono" 
                     x-text="formatRupiah(remainingBudget)">
                    Rp 0
                </div>
                <div class="relative z-10 mt-3 flex items-center gap-1.5 text-xs text-emerald-100">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold">Siap Digunakan</span>
                    <span class="text-white font-semibold" x-text="'Sisa ' + currentMonthName"></span>
                </div>
            </div>
        </div>

        <!-- PROGRESS BAR PENGGUNAAN ANGGARAN: DILENGKAPI NAMA BULAN -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-3">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-indigo-600 dark:text-indigo-400"></i>
                    <span class="font-bold text-slate-900 dark:text-white">
                        Status Realisasi Anggaran Kas Kecil (<span class="text-indigo-600 dark:text-indigo-400" x-text="currentMonthName"></span>)
                    </span>
                </div>
                <span class="font-mono font-bold" 
                      :class="usagePercentage > 85 ? 'text-rose-500' : (usagePercentage > 60 ? 'text-amber-500' : 'text-emerald-500')"
                      x-text="usagePercentage + '%'"></span>
            </div>

            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-500 rounded-full"
                     :style="`width: ${usagePercentage}%;`"
                     :class="usagePercentage > 85 ? 'bg-rose-500' : (usagePercentage > 60 ? 'bg-amber-500' : 'bg-emerald-500')"></div>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1">
                <span>Terpakai: <b class="font-mono text-slate-700 dark:text-slate-300" x-text="formatRupiah(totalUsed)"></b></span>
                <span>Pagu Batas: <b class="font-mono text-slate-700 dark:text-slate-300" x-text="formatRupiah(limitValue)"></b></span>
            </div>
        </div>

        <!-- FORM KONFIGURASI NOMINAL KAS (DILENGKAPI NAMA BULAN) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Konfigurasi Batas Anggaran Kas Perusahaan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Tentukan nominal tertinggi kas kecil yang dapat dicairkan oleh sistem pada periode <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="currentMonthName"></span>.
                    </p>
                </div>
                
                <span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[11px] font-mono text-slate-600 dark:text-slate-300">
                    Database Key: <code class="text-indigo-600 dark:text-indigo-400 font-bold">claim_limit</code>
                </span>
            </div>

            <!-- Preset Quick Select -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Preset Pilihan Cepat</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <button type="button" @click="setPreset(2500000)" 
                            :class="limitValue == 2500000 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="py-2.5 px-3 rounded-xl text-xs font-mono transition cursor-pointer">
                        Rp 2.500.000
                    </button>
                    <button type="button" @click="setPreset(5000000)" 
                            :class="limitValue == 5000000 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="py-2.5 px-3 rounded-xl text-xs font-mono transition cursor-pointer">
                        Rp 5.000.000
                    </button>
                    <button type="button" @click="setPreset(10000000)" 
                            :class="limitValue == 10000000 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="py-2.5 px-3 rounded-xl text-xs font-mono transition cursor-pointer">
                        Rp 10.000.000
                    </button>
                    <button type="button" @click="setPreset(25000000)" 
                            :class="limitValue == 25000000 ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                            class="py-2.5 px-3 rounded-xl text-xs font-mono transition cursor-pointer">
                        Rp 25.000.000
                    </button>
                </div>
            </div>

            <!-- Input & Save Action -->
            <div class="space-y-4 pt-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Input Nominal Batas Kustom (Rp)</label>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-bold text-sm">Rp</span>
                        </div>
                        <input 
                            type="number" 
                            x-model="limitValue" 
                            min="0" 
                            step="100000" 
                            placeholder="Masukkan nominal batas kas perusahaan..." 
                            class="w-full pl-12 pr-4 py-3 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 font-mono font-bold text-base transition shadow-sm"
                        >
                    </div>

                    <button 
                        @click="saveLimit()" 
                        :disabled="isSaving" 
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-xs font-bold rounded-xl text-white transition shadow-sm flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <i class="fa-solid fa-floppy-disk" :class="{ 'animate-spin': isSaving }"></i>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Batas Anggaran'"></span>
                    </button>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-start gap-2">
                    <i class="fa-solid fa-shield-check text-indigo-600 dark:text-indigo-400 mt-0.5"></i>
                    <p>Sistem akan menolak secara otomatis setiap pengajuan klaim baru dari aplikasi mobile karyawan jika nominal pengajuan melebihi sisa pagu anggaran bulan berjalan (<span class="font-semibold text-slate-700 dark:text-slate-300" x-text="currentMonthName"></span>).</p>
                </div>
            </div>
        </div>

        <!-- PANDUAN INTEGRASI & PROTOKOL -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Protokol Keamanan Kas Kecil</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Proteksi otomatis aktif untuk mencegah *overbudget* pengeluaran kas kantor.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-emerald-500">
                <i class="fa-solid fa-circle-check text-xs"></i>
                <span>Sistem Proteksi Aktif</span>
            </div>
        </div>

    </div>
</template>