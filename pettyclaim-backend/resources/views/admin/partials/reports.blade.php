<template x-if="activeTab === 'reports'">
    <div class="space-y-6 select-none transition-colors duration-200"
         x-data="{ 
            claimsData: {{ json_encode($claims ?? []) }},
            periodFilter: 'ALL',
            reportSearch: '',
            previewModalOpen: false,
            previewImage: null,
            previewTitle: '',

            openReceiptModal(imageUrl, title) {
                if (!imageUrl) return;
                this.previewImage = imageUrl;
                this.previewTitle = title;
                this.previewModalOpen = true;
            },

            getReceiptUrl(rawPath) {
                if (!rawPath || typeof rawPath !== 'string') return null;
                const pathTrimmed = rawPath.trim();
                
                if (pathTrimmed.startsWith('http://') || pathTrimmed.startsWith('https://') || pathTrimmed.startsWith('data:image/')) {
                    return pathTrimmed;
                }
                
                const clean = pathTrimmed.replace(/^\/+/, '');
                if (clean.startsWith('storage/')) {
                    return '/' + clean;
                }
                return '/storage/' + clean;
            },

            formatReportDate(dateStr) {
                if (!dateStr) return '-';
                try {
                    const d = new Date(dateStr);
                    if (isNaN(d.getTime())) return dateStr;
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                } catch (e) {
                    return dateStr;
                }
            },

            isAtkMerchant(merchant) {
                if (!merchant) return false;
                const m = merchant.toLowerCase();
                return m.includes('gramedia') || m.includes('atk') || m.includes('kertas') || m.includes('fotocopy') || m.includes('print') || m.includes('stationery') || m.includes('buku');
            },

            get filteredClaims() {
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();

                return this.claimsData.filter(claim => {
                    if (!claim.transaction_date && !claim.created_at) return false;
                    const dateVal = claim.transaction_date || claim.created_at;
                    const cDate = new Date(dateVal);
                    const cYear = cDate.getFullYear();
                    const cMonth = cDate.getMonth();

                    let matchPeriod = true;
                    if (this.periodFilter === 'THIS_MONTH') {
                        matchPeriod = (cYear === currentYear && cMonth === currentMonth);
                    } else if (this.periodFilter === 'LAST_MONTH') {
                        const lastMonthDate = new Date(currentYear, currentMonth - 1, 1);
                        matchPeriod = (cYear === lastMonthDate.getFullYear() && cMonth === lastMonthDate.getMonth());
                    } else if (this.periodFilter === 'Q1') {
                        matchPeriod = (cYear === currentYear && cMonth >= 0 && cMonth <= 2);
                    } else if (this.periodFilter === 'Q2') {
                        matchPeriod = (cYear === currentYear && cMonth >= 3 && cMonth <= 5);
                    } else if (this.periodFilter === 'Q3') {
                        matchPeriod = (cYear === currentYear && cMonth >= 6 && cMonth <= 8);
                    } else if (this.periodFilter === 'Q4') {
                        matchPeriod = (cYear === currentYear && cMonth >= 9 && cMonth <= 11);
                    } else if (this.periodFilter === 'THIS_YEAR') {
                        matchPeriod = (cYear === currentYear);
                    }

                    const matchSearch = !this.reportSearch || 
                        (claim.merchant_name && claim.merchant_name.toLowerCase().includes(this.reportSearch.toLowerCase())) ||
                        (claim.id && claim.id.toString().toLowerCase().includes(this.reportSearch.toLowerCase())) ||
                        (claim.user && claim.user.name && claim.user.name.toLowerCase().includes(this.reportSearch.toLowerCase())) ||
                        (claim.amount && claim.amount.toString().includes(this.reportSearch));

                    return matchPeriod && matchSearch;
                });
            },

            get periodStats() {
                const list = this.filteredClaims;
                const approved = list.filter(c => c.status === 'APPROVED');
                const pending = list.filter(c => c.status === 'PENDING' || c.status === 'PENDING_VERIFICATION' || c.status === 'VERIFIED');
                const rejected = list.filter(c => c.status === 'REJECTED');

                const totalApprovedAmt = approved.reduce((acc, c) => acc + Number(c.amount || 0), 0);
                const totalPendingAmt = pending.reduce((acc, c) => acc + Number(c.amount || 0), 0);
                const totalRejectedAmt = rejected.reduce((acc, c) => acc + Number(c.amount || 0), 0);

                const activeList = list.filter(c => c.status !== 'REJECTED');
                const totalActiveAmt = activeList.reduce((acc, c) => acc + Number(c.amount || 0), 0);

                let atkAmt = 0;
                let operasionalAmt = 0;

                activeList.forEach(c => {
                    const amt = Number(c.amount || 0);
                    if (this.isAtkMerchant(c.merchant_name)) {
                        atkAmt += amt;
                    } else {
                        operasionalAmt += amt;
                    }
                });

                let atkPct = 0;
                let operasionalPct = 0;
                if (totalActiveAmt > 0) {
                    atkPct = Math.round((atkAmt / totalActiveAmt) * 100);
                    operasionalPct = 100 - atkPct;
                }

                return {
                    totalCount: list.length,
                    approvedCount: approved.length,
                    approvedAmount: totalApprovedAmt,
                    pendingCount: pending.length,
                    pendingAmount: totalPendingAmt,
                    rejectedCount: rejected.length,
                    rejectedAmount: totalRejectedAmt,
                    totalActiveAmount: totalActiveAmt,
                    atkAmount: atkAmt,
                    atkPercent: atkPct,
                    operasionalAmount: operasionalAmt,
                    operasionalPercent: operasionalPct
                };
            },

            get monthlyTrends() {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const now = new Date();
                const trendData = [];

                for (let i = 5; i >= 0; i--) {
                    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                    const y = d.getFullYear();
                    const m = d.getMonth();
                    
                    const totalMonth = this.claimsData
                        .filter(c => {
                            if (c.status === 'REJECTED') return false;
                            const claimD = new Date(c.transaction_date || c.created_at);
                            return claimD.getFullYear() === y && claimD.getMonth() === m;
                        })
                        .reduce((sum, c) => sum + Number(c.amount || 0), 0);

                    trendData.push({
                        label: months[m] + ' ' + y.toString().substring(2),
                        amount: totalMonth
                    });
                }

                const maxAmt = Math.max(...trendData.map(t => t.amount), 1);
                return trendData.map(t => ({
                    ...t,
                    percentage: Math.round((t.amount / maxAmt) * 100)
                }));
            },

            get departmentBreakdown() {
                const depts = {};
                this.filteredClaims
                    .filter(c => c.status !== 'REJECTED')
                    .forEach(c => {
                        const dName = (c.user && c.user.department) ? c.user.department : 'Umum / Lainnya';
                        depts[dName] = (depts[dName] || 0) + Number(c.amount || 0);
                    });

                const total = Object.values(depts).reduce((a, b) => a + b, 0) || 1;
                return Object.keys(depts)
                    .map(name => ({
                        name: name,
                        amount: depts[name],
                        percent: Math.round((depts[name] / total) * 100)
                    }))
                    .sort((a, b) => b.amount - a.amount)
                    .slice(0, 5);
            },
            
            isClaimDisbursed(c) {
                return c && c.status === 'APPROVED' && typeof c.notes === 'string' && c.notes.includes('[DISBURSEMENT FINANCE');
            }
         }">
        
        <!-- Header & Toolbar Action: Clean Solid Layout -->
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 border flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div>
                <h3 class="text-base font-extrabold text-[#144272] dark:text-blue-400 tracking-tight">Laporan Analytics Pengeluaran</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Audit pengeluaran kas kecil dengan visualisasi grafik performa dan alokasi dana.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <select x-model="periodFilter" 
                            class="appearance-none pl-9 pr-9 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] cursor-pointer shadow-sm transition">
                        <option value="ALL">Semua Periode</option>
                        <option value="THIS_MONTH">Bulan Ini</option>
                        <option value="LAST_MONTH">Bulan Lalu</option>
                        <option value="Q1">Triwulan 1 (Jan - Mar)</option>
                        <option value="Q2">Triwulan 2 (Apr - Jun)</option>
                        <option value="Q3">Triwulan 3 (Jul - Sep)</option>
                        <option value="Q4">Triwulan 4 (Okt - Des)</option>
                        <option value="THIS_YEAR">Tahun Ini</option>
                    </select>
                    <i class="fa-regular fa-calendar absolute left-3.5 top-1/2 -translate-y-1/2 text-[#144272] dark:text-blue-400 text-xs pointer-events-none"></i>
                    <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Metric Overview Cards: 4 PURE SOLID COLOR CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1 -->
            <div class="relative overflow-hidden p-5 rounded-2xl bg-indigo-600 dark:bg-indigo-700 text-white shadow-md border border-indigo-500 transition-all hover:scale-[1.01]">
                <i class="fa-solid fa-calculator absolute -bottom-3 -right-2 text-6xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-100">Total Pengeluaran</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-calculator text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-2xl font-black font-mono tracking-tight text-white whitespace-nowrap"
                     x-text="'Rp ' + Number(periodStats.approvedAmount > 0 ? periodStats.approvedAmount : periodStats.totalActiveAmount).toLocaleString('id-ID')">
                </div>
                <div class="relative z-10 mt-3 text-[11px] text-indigo-100 font-semibold flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold" x-text="(periodStats.approvedCount > 0 ? periodStats.approvedCount : periodStats.totalCount) + ' Total Transaksi'"></span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="relative overflow-hidden p-5 rounded-2xl bg-emerald-600 dark:bg-emerald-700 text-white shadow-md border border-emerald-500 transition-all hover:scale-[1.01]">
                <i class="fa-solid fa-circle-check absolute -bottom-3 -right-2 text-6xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-100">Disetujui</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-2xl font-black font-mono tracking-tight text-white whitespace-nowrap"
                     x-text="'Rp ' + Number(periodStats.approvedAmount).toLocaleString('id-ID')">
                </div>
                <div class="relative z-10 mt-3 text-[11px] text-emerald-100 font-semibold flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold" x-text="periodStats.approvedCount + ' Transaksi Sah'"></span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="relative overflow-hidden p-5 rounded-2xl bg-amber-600 dark:bg-amber-700 text-white shadow-md border border-amber-500 transition-all hover:scale-[1.01]">
                <i class="fa-solid fa-hourglass-half absolute -bottom-3 -right-2 text-6xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-100">Menunggu Review</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-hourglass-half text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-2xl font-black font-mono tracking-tight text-white whitespace-nowrap"
                     x-text="'Rp ' + Number(periodStats.pendingAmount).toLocaleString('id-ID')">
                </div>
                <div class="relative z-10 mt-3 text-[11px] text-amber-100 font-semibold flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold" x-text="periodStats.pendingCount + ' Transaksi Pending'"></span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="relative overflow-hidden p-5 rounded-2xl bg-rose-600 dark:bg-rose-700 text-white shadow-md border border-rose-500 transition-all hover:scale-[1.01]">
                <i class="fa-solid fa-ban absolute -bottom-3 -right-2 text-6xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-100">Ditolak</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-ban text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-2xl font-black font-mono tracking-tight text-white whitespace-nowrap"
                     x-text="'Rp ' + Number(periodStats.rejectedAmount).toLocaleString('id-ID')">
                </div>
                <div class="relative z-10 mt-3 text-[11px] text-rose-100 font-semibold flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded bg-white/20 font-bold" x-text="periodStats.rejectedCount + ' Transaksi Ditolak'"></span>
                </div>
            </div>
        </div>

        <!-- SECTION GRAFIK RELEVAN 1: TREND BULANAN & ALOKASI KATEGORI -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- GRAFIK 1: DIAGRAM BATANG TREN PENGELUARAN (6 BULAN TERAKHIR) -->
            <div class="lg:col-span-7 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Tren Realisasi Pengeluaran (6 Bulan)</h4>
                        <span class="text-[11px] text-slate-500 font-medium">Berdasarkan Tanggal Transaksi</span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Perbandingan total serapan kas kecil antar bulan untuk analisis anggaran.</p>
                </div>

                <div class="pt-8 pb-3">
                    <div class="h-44 flex items-end justify-between gap-3 sm:gap-6 px-2">
                        <template x-for="(bar, index) in monthlyTrends" :key="index">
                            <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity text-[10px] font-mono font-bold px-2 py-1 rounded bg-slate-950 text-white dark:bg-white dark:text-slate-950 shadow-md pointer-events-none whitespace-nowrap mb-1"
                                     x-text="'Rp ' + Number(bar.amount).toLocaleString('id-ID')"></div>

                                <div class="w-full max-w-[48px] rounded-t-lg transition-all duration-500 relative"
                                     :class="bar.amount > 0 ? 'bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-500 dark:hover:bg-indigo-400' : 'bg-slate-100 dark:bg-slate-800'"
                                     :style="`height: ${bar.amount > 0 ? Math.max(bar.percentage, 12) : 4}%;`">
                                </div>

                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1 whitespace-nowrap" x-text="bar.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded bg-indigo-600"></span>
                        Realisasi Kas
                    </span>
                    <span class="font-mono text-slate-700 dark:text-slate-300 font-bold"
                          x-text="'Rata-rata: Rp ' + Number(monthlyTrends.reduce((a,b)=>a+b.amount,0) / (monthlyTrends.length || 1)).toLocaleString('id-ID') + '/bln'"></span>
                </div>
            </div>

            <!-- GRAFIK 2: DONUT CHART ALOKASI BEBAN -->
            <div class="lg:col-span-5 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Alokasi Beban per Kategori</h4>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Komposisi pengeluaran kas kecil pada periode aktif.</p>
                </div>

                <div class="py-6 flex items-center justify-center">
                    <div class="relative w-40 h-40 rounded-full flex items-center justify-center transition-all duration-300 shadow-sm"
                         :style="`background: conic-gradient(#4f46e5 0% ${periodStats.operasionalPercent}%, #9333ea ${periodStats.operasionalPercent}% 100%);`">
                        <div class="w-28 h-28 rounded-full bg-white dark:bg-slate-900 flex flex-col items-center justify-center text-center border border-slate-200 dark:border-slate-800 shadow-inner">
                            <span class="text-xl font-black text-slate-900 dark:text-white font-mono tracking-tight"
                                  x-text="periodStats.totalActiveAmount > 0 ? '100%' : '0%'"></span>
                            <span class="text-[9px] text-slate-400 uppercase font-extrabold tracking-wider mt-0.5"
                                  x-text="periodStats.approvedAmount > 0 ? 'TERVERIFIKASI' : (periodStats.totalActiveAmount > 0 ? 'DIPROSES' : 'KOSONG')"></span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Operasional</span>
                        </div>
                        <span class="font-mono text-xs font-extrabold text-indigo-600 dark:text-indigo-400" x-text="periodStats.operasionalPercent + '%'"></span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">ATK & Cetak</span>
                        </div>
                        <span class="font-mono text-xs font-extrabold text-purple-600 dark:text-purple-400" x-text="periodStats.atkPercent + '%'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION GRAFIK RELEVAN 2: TOP DEPARTEMEN & HEALTH RATIO -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <!-- GRAFIK 3: TOP 5 DEPARTEMEN PENGGUNA DANA (HORIZONTAL BARS) -->
            <div class="lg:col-span-7 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">Top 5 Departemen Pengaju Klaim</h4>
                    <span class="text-[11px] text-slate-500 font-medium">Berdasarkan Total Nominal</span>
                </div>

                <div class="space-y-5">
                    <template x-for="(dept, idx) in departmentBreakdown" :key="idx">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-800 text-[10px] font-mono flex items-center justify-center text-slate-500 font-bold" x-text="idx + 1"></span>
                                    <span x-text="dept.name"></span>
                                </span>
                                <div class="font-mono text-xs flex items-center gap-2">
                                    <span class="text-[#144272] dark:text-blue-400 font-extrabold" x-text="'Rp ' + Number(dept.amount).toLocaleString('id-ID')"></span>
                                    <span class="text-slate-400 text-[11px] font-bold" x-text="'(' + dept.percent + '%)'"></span>
                                </div>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-indigo-600 transition-all duration-500"
                                     :style="`width: ${dept.percent}%;`"></div>
                            </div>
                        </div>
                    </template>

                    <template x-if="departmentBreakdown.length === 0">
                        <div class="py-8 text-center text-slate-400 text-xs font-medium">
                            Belum ada transaksi klaim departemen yang tercatat.
                        </div>
                    </template>
                </div>
            </div>

            <!-- GRAFIK 4: HEALTH RATIO STATUS AUDIT -->
            <div class="lg:col-span-5 p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h4 class="font-bold text-slate-900 dark:text-white text-sm">Rasio Status Pengajuan Klaim</h4>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Tingkat persetujuan, penundaan review, dan penolakan berkas.</p>
                </div>

                <div class="my-6 space-y-5">
                    <div class="w-full h-5 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex shadow-inner">
                        <div class="h-full bg-emerald-600 transition-all duration-300"
                             :style="`width: ${periodStats.totalCount > 0 ? (periodStats.approvedCount / periodStats.totalCount) * 100 : 0}%;`"></div>
                        <div class="h-full bg-amber-500 transition-all duration-300"
                             :style="`width: ${periodStats.totalCount > 0 ? (periodStats.pendingCount / periodStats.totalCount) * 100 : 0}%;`"></div>
                        <div class="h-full bg-rose-600 transition-all duration-300"
                             :style="`width: ${periodStats.totalCount > 0 ? (periodStats.rejectedCount / periodStats.totalCount) * 100 : 0}%;`"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40">
                            <span class="block text-emerald-600 dark:text-emerald-400 font-extrabold text-sm mb-0.5"
                                  x-text="periodStats.totalCount > 0 ? Math.round((periodStats.approvedCount / periodStats.totalCount) * 100) + '%' : '0%'"></span>
                            <span class="text-[10px] text-emerald-700/70 font-bold uppercase tracking-wider">Disetujui</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/40">
                            <span class="block text-amber-600 dark:text-amber-400 font-extrabold text-sm mb-0.5"
                                  x-text="periodStats.totalCount > 0 ? Math.round((periodStats.pendingCount / periodStats.totalCount) * 100) + '%' : '0%'"></span>
                            <span class="text-[10px] text-amber-700/70 font-bold uppercase tracking-wider">Pending</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40">
                            <span class="block text-rose-600 dark:text-rose-400 font-extrabold text-sm mb-0.5"
                                  x-text="periodStats.totalCount > 0 ? Math.round((periodStats.rejectedCount / periodStats.totalCount) * 100) + '%' : '0%'"></span>
                            <span class="text-[10px] text-rose-700/70 font-bold uppercase tracking-wider">Ditolak</span>
                        </div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 font-medium">
                    <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i> Kepatuhan verifikasi klaim kas kecil terjaga dengan sistem audit otomatis.
                </div>
            </div>
        </div>

        <!-- Section 3: Detail Pengeluaran Table (FULL SOLID BLOCKS) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900">
                <div>
                    <h4 class="font-extrabold text-[#144272] dark:text-blue-400 text-base">Histori Rekap Pengeluaran & Bukti Struk</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Rincian seluruh transaksi klaim kas kecil beserta foto nota asli di sistem.</p>
                </div>

                <div class="relative w-full md:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#144272] text-xs"></i>
                    <input x-model="reportSearch" 
                           type="text" 
                           placeholder="Cari transaksi, merchant, nominal..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] transition shadow-sm">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#144272] text-white uppercase text-[10px] font-extrabold tracking-wider">
                        <tr>
                            <th class="py-4 px-4 text-center">Foto Struk</th>
                            <th class="py-4 px-4">Tanggal</th>
                            <th class="py-4 px-4">Kode Transaksi</th>
                            <th class="py-4 px-4">Merchant / Keterangan</th>
                            <th class="py-4 px-4">Kategori</th>
                            <th class="py-4 px-4 text-right">Nominal</th>
                            <th class="py-4 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900">
                        <template x-for="claim in filteredClaims" :key="claim.id">
                            <!-- BORDER TEBAL DAN BACKGROUND WARNA SOLID SESUAI STATUS -->
                            <tr class="border-b-[4px] border-white dark:border-slate-900 transition-colors"
                                :class="{
                                    'bg-slate-100 dark:bg-slate-800/50': isClaimDisbursed(claim),
                                    'bg-emerald-50 dark:bg-emerald-900/20': claim.status === 'APPROVED' && !isClaimDisbursed(claim),
                                    'bg-rose-50 dark:bg-rose-900/20': claim.status === 'REJECTED',
                                    'bg-sky-50 dark:bg-sky-900/20': claim.status === 'VERIFIED',
                                    'bg-amber-50 dark:bg-amber-900/20': claim.status === 'PENDING_VERIFICATION' || claim.status === 'PENDING'
                                }">
                                
                                <td class="py-3 px-4 text-center border-r-2 border-white dark:border-slate-900" x-data="{ imgFailed: false }">
                                    <template x-if="claim.receipt_image_path && !imgFailed">
                                        <button type="button" 
                                                x-on:click="openReceiptModal(getReceiptUrl(claim.receipt_image_path), claim.merchant_name || 'Bukti Struk')"
                                                class="relative inline-flex items-center justify-center w-12 h-12 rounded-xl overflow-hidden border-2 border-white dark:border-slate-700 hover:border-[#144272] transition shadow-md cursor-pointer bg-slate-200 dark:bg-slate-950 mx-auto">
                                            <img :src="getReceiptUrl(claim.receipt_image_path)" 
                                                 x-on:error="imgFailed = true"
                                                 alt="Struk" 
                                                 class="w-full h-full object-cover" 
                                                 loading="lazy" />
                                        </button>
                                    </template>
                                    <template x-if="!claim.receipt_image_path || imgFailed">
                                        <div class="inline-flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-slate-200 dark:bg-slate-800/80 border-2 border-white dark:border-slate-700 text-slate-400 mx-auto shadow-sm" title="Foto struk belum tersedia">
                                            <i class="fa-solid fa-file-invoice text-sm"></i>
                                        </div>
                                    </template>
                                </td>

                                <td class="py-3 px-4 font-bold text-slate-700 dark:text-slate-300 border-r-2 border-white dark:border-slate-900 whitespace-nowrap">
                                    <span x-text="formatReportDate(claim.transaction_date || claim.created_at)"></span>
                                </td>

                                <td class="py-3 px-4 font-mono font-extrabold text-[#144272] dark:text-blue-400 border-r-2 border-white dark:border-slate-900 whitespace-nowrap"
                                    x-text="'DJ-' + (claim.id ? claim.id.toString().substring(0, 8) : '')">
                                </td>

                                <td class="py-3 px-4 border-r-2 border-white dark:border-slate-900">
                                    <div class="font-extrabold text-slate-900 dark:text-white flex items-center gap-1.5 text-[13px]">
                                        <i class="fa-solid fa-store text-[10px] text-slate-400"></i>
                                        <span x-text="claim.merchant_name || 'Tidak Ada'"></span>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-0.5"
                                         x-text="claim.user?.name || 'Karyawan'">
                                    </div>
                                </td>

                                <td class="py-3 px-4 font-extrabold text-slate-700 dark:text-slate-300 border-r-2 border-white dark:border-slate-900 whitespace-nowrap">
                                    <template x-if="isAtkMerchant(claim.merchant_name)">
                                        <span>ATK & Cetak</span>
                                    </template>
                                    <template x-if="!isAtkMerchant(claim.merchant_name)">
                                        <span>Operasional</span>
                                    </template>
                                </td>

                                <td class="py-3 px-4 text-right font-mono font-extrabold text-[#144272] dark:text-blue-400 border-r-2 border-white dark:border-slate-900 whitespace-nowrap text-sm"
                                    x-text="'Rp ' + Number(claim.amount || 0).toLocaleString('id-ID')">
                                </td>

                                <!-- Status Murni Teks Cetak Tebal, No Badge -->
                                <td class="py-3 px-4 text-center border-r-2 border-white dark:border-slate-900 font-extrabold uppercase tracking-widest text-[11px]"
                                    :class="{
                                        'text-emerald-700 dark:text-emerald-400': claim.status === 'APPROVED' && !isClaimDisbursed(claim),
                                        'text-slate-500 dark:text-slate-400': isClaimDisbursed(claim),
                                        'text-rose-700 dark:text-rose-400': claim.status === 'REJECTED',
                                        'text-amber-700 dark:text-amber-400': claim.status === 'PENDING' || claim.status === 'PENDING_VERIFICATION' || claim.status === 'VERIFIED'
                                    }">
                                    <template x-if="claim.status === 'APPROVED' && !isClaimDisbursed(claim)">
                                        <span>Disetujui</span>
                                    </template>
                                    <template x-if="isClaimDisbursed(claim)">
                                        <span>Cair (Selesai)</span>
                                    </template>
                                    <template x-if="claim.status === 'PENDING' || claim.status === 'PENDING_VERIFICATION' || claim.status === 'VERIFIED'">
                                        <span>Proses Audit</span>
                                    </template>
                                    <template x-if="claim.status === 'REJECTED'">
                                        <span>Ditolak</span>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        <template x-if="filteredClaims.length === 0">
                            <tr>
                                <td colspan="7" class="py-16 px-4 text-center bg-white dark:bg-slate-900">
                                    <div class="max-w-xs mx-auto space-y-4">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-300 dark:text-slate-500 mx-auto shadow-inner">
                                            <i class="fa-solid fa-folder-open text-2xl"></i>
                                        </div>
                                        <div class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Tidak ada data ditemukan</div>
                                        <p class="text-xs font-medium text-slate-500 leading-relaxed">Belum ada catatan pengeluaran pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 4: Rekap Pengeluaran per Kategori Table (FULL SOLID BLOCKS) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900">
                <div>
                    <h4 class="font-extrabold text-[#144272] dark:text-blue-400 text-base">Rekapitulasi Anggaran Kategori</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Persentase penyerapan anggaran per tipe pengeluaran operasional.</p>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-b-2xl overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#144272] text-white uppercase text-[10px] font-extrabold tracking-wider">
                        <tr>
                            <th class="py-4 px-5">Kategori Pengeluaran</th>
                            <th class="py-4 px-5 text-right">Total Nominal</th>
                            <th class="py-4 px-5 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="border-b-[4px] border-white dark:border-slate-900">
                        <!-- BEBAN OPERASIONAL (SOLID INDIGO BLOCK) -->
                        <tr class="bg-indigo-50 dark:bg-indigo-900/20 border-b-[4px] border-white dark:border-slate-900">
                            <td class="py-4 px-5 font-extrabold text-indigo-900 dark:text-indigo-100 flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-indigo-600 shadow-sm"></span>
                                <span>Beban Operasional</span>
                            </td>
                            <td class="py-4 px-5 text-right font-mono font-extrabold text-indigo-700 dark:text-indigo-400 text-sm whitespace-nowrap"
                                x-text="'Rp ' + Number(periodStats.operasionalAmount).toLocaleString('id-ID')"></td>
                            <td class="py-4 px-5 text-right font-mono font-extrabold text-indigo-600 dark:text-indigo-400 whitespace-nowrap"
                                x-text="periodStats.operasionalPercent + '%'"></td>
                        </tr>
                        
                        <!-- BEBAN ATK (SOLID PURPLE BLOCK) -->
                        <tr class="bg-purple-50 dark:bg-purple-900/20 border-b-[4px] border-white dark:border-slate-900">
                            <td class="py-4 px-5 font-extrabold text-purple-900 dark:text-purple-100 flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-purple-600 shadow-sm"></span>
                                <span>Beban ATK & Cetak</span>
                            </td>
                            <td class="py-4 px-5 text-right font-mono font-extrabold text-purple-700 dark:text-purple-400 text-sm whitespace-nowrap"
                                x-text="'Rp ' + Number(periodStats.atkAmount).toLocaleString('id-ID')"></td>
                            <td class="py-4 px-5 text-right font-mono font-extrabold text-purple-600 dark:text-purple-400 whitespace-nowrap"
                                x-text="periodStats.atkPercent + '%'"></td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-100 dark:bg-[#0B132B] font-extrabold text-slate-900 dark:text-white">
                        <tr>
                            <td class="py-4 px-5 uppercase text-[10px] tracking-widest text-[#144272] dark:text-blue-400">Total Pengeluaran Aktif</td>
                            <td class="py-4 px-5 text-right font-mono text-emerald-600 dark:text-emerald-400 text-base whitespace-nowrap"
                                x-text="'Rp ' + Number(periodStats.totalActiveAmount).toLocaleString('id-ID')"></td>
                            <td class="py-4 px-5 text-right font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap"
                                x-text="periodStats.totalActiveAmount > 0 ? '100%' : '0%'"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- MODAL PREVIEW FOTO STRUK ZOOM -->
        <div x-show="previewModalOpen" 
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             style="display: none;"
             x-on:keydown.escape.window="previewModalOpen = false"
             x-cloak>
            
            <div x-on:click.outside="previewModalOpen = false" 
                 class="relative max-w-2xl w-full bg-white dark:bg-[#0B132B] rounded-3xl overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#0B132B]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#144272] flex items-center justify-center text-white shadow-sm shrink-0">
                            <i class="fa-solid fa-image text-lg"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-base" x-text="previewTitle || 'Bukti Struk'"></h4>
                    </div>
                    <button type="button" x-on:click="previewModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900 dark:hover:text-rose-400 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Bagian Preview Struk (Tanpa efek pulse kedap-kedip) -->
                <div class="p-6 flex items-center justify-center bg-slate-50 dark:bg-slate-950 max-h-[70vh] overflow-auto shadow-inner">
                    <img :src="previewImage" alt="Bukti Struk Pembelian" class="max-w-full max-h-[65vh] rounded-xl object-contain shadow-md border border-slate-200 dark:border-slate-800" />
                </div>

                <div class="px-6 py-4 bg-white dark:bg-[#0B132B] border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <a :href="previewImage" target="_blank" download class="text-[11px] font-extrabold uppercase tracking-widest text-[#144272] dark:text-blue-400 hover:text-blue-600 flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Gambar Baru
                    </a>
                    <button type="button" x-on:click="previewModalOpen = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl text-[11px] font-extrabold uppercase tracking-widest text-slate-800 dark:text-white shadow-sm transition cursor-pointer">
                        Tutup Panel
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>