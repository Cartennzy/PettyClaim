<template x-if="activeTab === 'claims'">
    <div x-data="{ 
        claimsList: {{ json_encode($claims ?? []) }},
        isLoading: false,
        isActionLoading: false,
        selectedClaim: null,
        showDetailModal: false,
        showTransferModal: false,
        verificationNotes: '',
        transferNotes: '',
        statusFilter: 'ALL',
        searchQuery: '',
        zoomLevel: 1,
        rotationDegree: 0,
        
        formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return dateStr;
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch (e) {
                return dateStr;
            }
        },

        isClaimDisbursed(c) {
            return c && c.status === 'APPROVED' && typeof c.notes === 'string' && c.notes.includes('[DISBURSEMENT FINANCE');
        },

        get counts() {
            return {
                all: this.claimsList.length,
                pending: this.claimsList.filter(c => c.status === 'PENDING_VERIFICATION' || c.status === 'PENDING').length,
                verified: this.claimsList.filter(c => c.status === 'VERIFIED').length,
                approved: this.claimsList.filter(c => c.status === 'APPROVED' && !this.isClaimDisbursed(c)).length,
                disbursed: this.claimsList.filter(c => this.isClaimDisbursed(c)).length,
                rejected: this.claimsList.filter(c => c.status === 'REJECTED').length
            };
        },

        resetImageViewer() {
            this.zoomLevel = 1;
            this.rotationDegree = 0;
        },
        zoomIn() {
            if (this.zoomLevel < 3) this.zoomLevel = Math.round((this.zoomLevel + 0.25) * 100) / 100;
        },
        zoomOut() {
            if (this.zoomLevel > 0.5) this.zoomLevel = Math.round((this.zoomLevel - 0.25) * 100) / 100;
        },
        rotateImage() {
            this.rotationDegree = (this.rotationDegree + 90) % 360;
        },

        triggerSweetAlert(type, title, message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title,
                    text: message,
                    background: document.documentElement.classList.contains('dark') ? '#0B0F19' : '#FFFFFF',
                    color: document.documentElement.classList.contains('dark') ? '#F8FAFC' : '#0F172A',
                    confirmButtonColor: '#2563EB',
                    customClass: {
                        popup: 'rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xl'
                    }
                });
            } else {
                alert(title + ': ' + message);
            }
        },

        get filteredClaims() {
            return this.claimsList.filter(c => {
                let matchesStatus = false;
                if (this.statusFilter === 'ALL') {
                    matchesStatus = true;
                } else if (this.statusFilter === 'PENDING_VERIFICATION') {
                    matchesStatus = (c.status === 'PENDING_VERIFICATION' || c.status === 'PENDING');
                } else if (this.statusFilter === 'APPROVED') {
                    matchesStatus = (c.status === 'APPROVED');
                } else {
                    matchesStatus = (c.status === this.statusFilter);
                }
                
                const matchesSearch = !this.searchQuery || 
                    (c.merchant_name && c.merchant_name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                    (c.user?.name && c.user.name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                    (c.user?.nip && c.user.nip.toString().includes(this.searchQuery)) ||
                    (c.user?.department && c.user.department.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
                    (c.amount && c.amount.toString().includes(this.searchQuery)) ||
                    (c.transaction_date && c.transaction_date.includes(this.searchQuery));
                return matchesStatus && matchesSearch;
            });
        },

        getReceiptUrl(path) {
            if (!path) return 'https://placehold.co/400x600?text=Struk+Tidak+Tersedia';
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            return '{{ asset('storage') }}/' + path.replace(/^\/+/, '').replace(/^storage\/+/, '');
        },

        async fetchClaims(silent = false) {
            this.isLoading = true;
            try {
                const res = await fetch('{{ route('admin.claims.data') }}');
                const result = await res.json();
                if(result.success) {
                    this.claimsList = result.data;
                    if (!silent) {
                        this.triggerSweetAlert('info', 'Data Diperbarui', 'Data klaim berhasil disinkronkan dengan database.');
                    }
                }
            } catch (err) {
                console.error('Gagal memperbarui data:', err);
                if (!silent) {
                    this.triggerSweetAlert('error', 'Koneksi Gagal', 'Tidak dapat mengambil data klaim terbaru.');
                }
            } finally {
                this.isLoading = false;
            }
        },

        openDetail(claim) {
            this.selectedClaim = claim;
            this.verificationNotes = '';
            this.resetImageViewer();
            this.showDetailModal = true;
        },

        openTransferModal(claim) {
            if (this.isClaimDisbursed(claim)) return;
            this.selectedClaim = claim;
            this.transferNotes = 'Pencairan reimbursement ' + claim.merchant_name + ' ke Dompet Digital';
            this.showTransferModal = true;
        },

        async executeTransfer() {
            if (!this.selectedClaim) return;
            this.isActionLoading = true;

            try {
                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '{{ csrf_token() }}';
                const res = await fetch(`/admin/claims/${this.selectedClaim.id}/transfer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        notes: this.transferNotes
                    })
                });

                const result = await res.json();

                if (res.ok && result.success) {
                    this.showTransferModal = false;
                    this.showDetailModal = false;
                    await this.fetchClaims(true);
                    this.triggerSweetAlert(
                        'success', 
                        'Disbursement Berhasil', 
                        `Dana Rp ${Number(this.selectedClaim.amount).toLocaleString('id-ID')} telah berhasil dicairkan.`
                    );
                } else {
                    this.triggerSweetAlert('error', 'Gagal Transfer', result.message || 'Terjadi kesalahan sistem.');
                }
            } catch (err) {
                console.error('Gagal memproses transfer:', err);
                this.triggerSweetAlert('error', 'Kesalahan Server', 'Tidak dapat memproses pengiriman saldo.');
            } finally {
                this.isActionLoading = false;
            }
        },

        async submitVerification(actionStatus) {
            if (!this.selectedClaim) return;
            this.isActionLoading = true;
            
            try {
                let url = '';
                let payload = {};

                if (actionStatus === 'VERIFIED') {
                    url = `/admin/claims/${this.selectedClaim.id}/verify`;
                    payload = { notes: this.verificationNotes || this.selectedClaim.notes };
                } else if (actionStatus === 'REJECTED') {
                    url = `/admin/claims/${this.selectedClaim.id}/reject`;
                    payload = { 
                        notes: this.verificationNotes || 'Pengajuan klaim ditolak oleh tim Finance.' 
                    };
                }

                const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '{{ csrf_token() }}';

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const result = await res.json();

                if (res.ok && result.success) {
                    const verifiedClaimName = this.selectedClaim.merchant_name;
                    const verifiedAmount = Number(this.selectedClaim.amount).toLocaleString('id-ID');
                    
                    this.showDetailModal = false;
                    await this.fetchClaims(true);

                    if (actionStatus === 'VERIFIED') {
                        this.triggerSweetAlert(
                            'success', 
                            'Validasi Disetujui', 
                            `Klaim ${verifiedClaimName} (Rp ${verifiedAmount}) diteruskan ke Manager Divisi.`
                        );
                    } else {
                        this.triggerSweetAlert(
                            'warning', 
                            'Klaim Ditolak', 
                            `Pengajuan klaim ${verifiedClaimName} berhasil ditolak.`
                        );
                    }
                } else {
                    this.triggerSweetAlert('error', 'Gagal Memproses', result.message || 'Terjadi kesalahan sistem.');
                }
            } catch (err) {
                console.error('Gagal memproses verifikasi:', err);
                this.triggerSweetAlert('error', 'Kesalahan Server', 'Tidak dapat menghubungkan ke server.');
            } finally {
                this.isActionLoading = false;
            }
        }
    }" class="space-y-6 select-none transition-colors duration-200">

        <!-- METRIC OVERVIEW CARDS: PURE SOLID COLOR CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: Verifikasi Finance (Solid Amber) -->
            <div x-on:click="statusFilter = 'PENDING_VERIFICATION'" 
                 :class="statusFilter === 'PENDING_VERIFICATION' ? 'ring-4 ring-amber-200 dark:ring-amber-900 scale-[1.02]' : 'hover:scale-[1.01]'" 
                 class="relative overflow-hidden p-5 rounded-2xl bg-amber-600 dark:bg-amber-700 text-white shadow-md border border-amber-500 cursor-pointer transition-all duration-200">
                <i class="fa-solid fa-clipboard-check absolute -bottom-3 -right-2 text-6xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-100">Verifikasi Finance</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-clipboard-check text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white" x-text="counts.pending"></div>
            </div>

            <!-- Card 2: Review Manager (Solid Sky Blue) -->
            <div x-on:click="statusFilter = 'VERIFIED'" 
                 :class="statusFilter === 'VERIFIED' ? 'ring-4 ring-sky-200 dark:ring-sky-900 scale-[1.02]' : 'hover:scale-[1.01]'" 
                 class="relative overflow-hidden p-5 rounded-2xl bg-sky-600 dark:bg-sky-700 text-white shadow-md border border-sky-500 cursor-pointer transition-all duration-200">
                <i class="fa-solid fa-user-clock absolute -bottom-3 -right-2 text-6xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-sky-100">Review Manager</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-user-clock text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white" x-text="counts.verified"></div>
            </div>

            <!-- Card 3: Siap Cair / Disbursement (Solid Emerald) -->
            <div x-on:click="statusFilter = 'APPROVED'" 
                 :class="statusFilter === 'APPROVED' ? 'ring-4 ring-emerald-200 dark:ring-emerald-900 scale-[1.02]' : 'hover:scale-[1.01]'" 
                 class="relative overflow-hidden p-5 rounded-2xl bg-emerald-600 dark:bg-emerald-700 text-white shadow-md border border-emerald-500 cursor-pointer transition-all duration-200">
                <i class="fa-solid fa-wallet absolute -bottom-3 -right-2 text-6xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-100">Siap Cair (Disburse)</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-wallet text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white" x-text="counts.approved"></div>
            </div>

            <!-- Card 4: Ditolak (Solid Rose) -->
            <div x-on:click="statusFilter = 'REJECTED'" 
                 :class="statusFilter === 'REJECTED' ? 'ring-4 ring-rose-200 dark:ring-rose-900 scale-[1.02]' : 'hover:scale-[1.01]'" 
                 class="relative overflow-hidden p-5 rounded-2xl bg-rose-600 dark:bg-rose-700 text-white shadow-md border border-rose-500 cursor-pointer transition-all duration-200">
                <i class="fa-solid fa-ban absolute -bottom-3 -right-2 text-6xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-100">Ditolak</span>
                    <span class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-ban text-xs"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white" x-text="counts.rejected"></div>
            </div>
        </div>

        <!-- MAIN TABLE CONTAINER -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden transition-colors duration-200">
            
            <!-- Table Header Toolbar -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900">
                <div>
                    <h3 class="text-base font-extrabold text-[#144272] dark:text-blue-400 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Audit & Validasi Transaksi</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Kelola audit kepatuhan struk, verifikasi fraud AI, dan transfer reimbursement kas kecil.</p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button x-on:click="fetchClaims(false)" :disabled="isLoading" 
                            class="px-4 py-2.5 bg-[#144272] hover:bg-blue-900 text-white active:scale-95 disabled:opacity-50 text-xs font-bold rounded-xl transition flex items-center gap-2 cursor-pointer shadow-sm">
                        <i class="fa-solid fa-arrows-rotate" :class="{ 'animate-spin': isLoading }"></i>
                        <span x-text="isLoading ? 'Menyinkronkan...' : 'Sinkronkan Data'"></span>
                    </button>
                </div>
            </div>

            <!-- Toolbar & Filter Tabs -->
            <div class="p-4 sm:px-6 bg-slate-100/70 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 hide-scrollbar">
                    <button x-on:click="statusFilter = 'ALL'" 
                            :class="statusFilter === 'ALL' ? 'bg-[#144272] text-white font-extrabold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 bg-transparent font-bold'"
                            class="px-3.5 py-2 rounded-xl text-[11px] transition whitespace-nowrap flex items-center gap-2 cursor-pointer border border-transparent"
                            :class="statusFilter !== 'ALL' && 'border-slate-200 dark:border-slate-700'">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Semua</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-black/20" x-text="counts.all"></span>
                    </button>
                    <button x-on:click="statusFilter = 'PENDING_VERIFICATION'" 
                            :class="statusFilter === 'PENDING_VERIFICATION' ? 'bg-amber-600 text-white font-extrabold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 bg-transparent font-bold'"
                            class="px-3.5 py-2 rounded-xl text-[11px] transition whitespace-nowrap flex items-center gap-2 cursor-pointer border border-transparent"
                            :class="statusFilter !== 'PENDING_VERIFICATION' && 'border-slate-200 dark:border-slate-700'">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>Perlu Validasi</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-black/20" x-text="counts.pending"></span>
                    </button>
                    <button x-on:click="statusFilter = 'VERIFIED'" 
                            :class="statusFilter === 'VERIFIED' ? 'bg-sky-600 text-white font-extrabold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 bg-transparent font-bold'"
                            class="px-3.5 py-2 rounded-xl text-[11px] transition whitespace-nowrap flex items-center gap-2 cursor-pointer border border-transparent"
                            :class="statusFilter !== 'VERIFIED' && 'border-slate-200 dark:border-slate-700'">
                        <i class="fa-solid fa-user-clock"></i>
                        <span>Review Manager</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-black/20" x-text="counts.verified"></span>
                    </button>
                    <button x-on:click="statusFilter = 'APPROVED'" 
                            :class="statusFilter === 'APPROVED' ? 'bg-emerald-600 text-white font-extrabold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 bg-transparent font-bold'"
                            class="px-3.5 py-2 rounded-xl text-[11px] transition whitespace-nowrap flex items-center gap-2 cursor-pointer border border-transparent"
                            :class="statusFilter !== 'APPROVED' && 'border-slate-200 dark:border-slate-700'">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span>Siap Disbursement</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-black/20" x-text="counts.approved"></span>
                    </button>
                    <button x-on:click="statusFilter = 'REJECTED'" 
                            :class="statusFilter === 'REJECTED' ? 'bg-rose-600 text-white font-extrabold shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-800 bg-transparent font-bold'"
                            class="px-3.5 py-2 rounded-xl text-[11px] transition whitespace-nowrap flex items-center gap-2 cursor-pointer border border-transparent"
                            :class="statusFilter !== 'REJECTED' && 'border-slate-200 dark:border-slate-700'">
                        <i class="fa-solid fa-ban"></i>
                        <span>Ditolak</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-black/20" x-text="counts.rejected"></span>
                    </button>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-full md:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari merchant, nama, nominal..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] transition shadow-sm">
                </div>
            </div>

            <!-- TABLE KLAIM (FULL WARNA SOLID BLOK) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    
                    <!-- THEAD BERWARNA BIRU GELAP -->
                    <thead class="bg-[#144272] text-white text-[10px] uppercase tracking-wider font-extrabold">
                        <tr>
                            <th class="py-4 px-4 w-16 text-center">Struk</th>
                            <th class="py-4 px-4">Merchant & Karyawan</th>
                            <th class="py-4 px-4">Tanggal</th>
                            <th class="py-4 px-4 text-right">Nominal Klaim</th>
                            <th class="py-4 px-4 text-center">Analisis Fraud AI</th>
                            <th class="py-4 px-4 text-center">Status Alur</th>
                            <th class="py-4 px-4 text-center w-48">Tindakan</th>
                        </tr>
                    </thead>
                    
                    <!-- TBODY DENGAN ROW FULL WARNA -->
                    <tbody class="bg-white dark:bg-slate-900">
                        <template x-for="claim in filteredClaims" :key="claim.id">
                            
                            <!-- BORDER TEBAL ANTAR BARIS DAN BACKGROUND DINAMIS -->
                            <tr class="border-b-[4px] border-white dark:border-slate-900 transition-colors"
                                :class="{
                                    'bg-slate-100 dark:bg-slate-800/50': isClaimDisbursed(claim),
                                    'bg-emerald-50 dark:bg-emerald-900/20': claim.status === 'APPROVED' && !isClaimDisbursed(claim),
                                    'bg-rose-50 dark:bg-rose-900/20': claim.status === 'REJECTED',
                                    'bg-sky-50 dark:bg-sky-900/20': claim.status === 'VERIFIED',
                                    'bg-amber-50 dark:bg-amber-900/20': claim.status === 'PENDING_VERIFICATION' || claim.status === 'PENDING'
                                }">
                                
                                <!-- Kolom Struk -->
                                <td class="py-3 px-4 text-center border-r-2 border-white dark:border-slate-900">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border-2 border-white dark:border-slate-700 bg-slate-200 dark:bg-slate-950 cursor-pointer shadow-md mx-auto"
                                         x-on:click="openDetail(claim)">
                                        <img :src="getReceiptUrl(claim.receipt_image_path)" 
                                             alt="Struk" 
                                             class="w-full h-full object-cover"
                                             onerror="this.onerror=null;this.src='https://placehold.co/100x100?text=Struk';"
                                             loading="lazy">
                                    </div>
                                </td>

                                <!-- Kolom Merchant -->
                                <td class="py-3 px-4 border-r-2 border-white dark:border-slate-900">
                                    <div class="font-extrabold text-slate-900 dark:text-white text-[13px]" x-text="claim.merchant_name || 'Tidak Ada'"></div>
                                    <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">
                                        Pemohon: <span class="text-slate-700 dark:text-slate-300" x-text="claim.user?.name || 'Karyawan'"></span>
                                    </div>
                                </td>

                                <!-- Kolom Tanggal -->
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-bold border-r-2 border-white dark:border-slate-900 whitespace-nowrap">
                                    <span x-text="formatDate(claim.transaction_date || claim.created_at)"></span>
                                </td>

                                <!-- Kolom Nominal -->
                                <td class="py-3 px-4 text-right border-r-2 border-white dark:border-slate-900 whitespace-nowrap">
                                    <span class="font-mono font-extrabold text-[#144272] dark:text-blue-400 text-sm" x-text="'Rp ' + Number(claim.amount || 0).toLocaleString('id-ID')"></span>
                                </td>

                                <!-- Kolom Fraud AI (SOLID BLOCK) -->
                                <td class="py-3 px-4 text-center border-r-2 border-white dark:border-slate-900">
                                    <template x-if="claim.is_flagged_duplicate">
                                        <span class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-extrabold bg-rose-600 text-white shadow-sm uppercase tracking-wider w-24">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                            <span>Duplikat</span>
                                        </span>
                                    </template>
                                    <template x-if="!claim.is_flagged_duplicate">
                                        <span class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-[10px] font-extrabold bg-emerald-600 text-white shadow-sm uppercase tracking-wider w-24">
                                            <i class="fa-solid fa-shield-halved"></i>
                                            <span>Otentik</span>
                                        </span>
                                    </template>
                                </td>

                                <!-- Kolom Status Alur (MURNI TEKS TEGAS, TANPA BADGE) -->
                                <td class="py-3 px-4 text-center border-r-2 border-white dark:border-slate-900">
                                    <span class="font-extrabold text-[11px] uppercase tracking-widest"
                                          :class="{
                                              'text-slate-500 dark:text-slate-400': isClaimDisbursed(claim),
                                              'text-emerald-700 dark:text-emerald-400': claim.status === 'APPROVED' && !isClaimDisbursed(claim),
                                              'text-rose-700 dark:text-rose-400': claim.status === 'REJECTED',
                                              'text-sky-700 dark:text-sky-400': claim.status === 'VERIFIED',
                                              'text-amber-700 dark:text-amber-400': claim.status === 'PENDING_VERIFICATION' || claim.status === 'PENDING'
                                          }"
                                          x-text="isClaimDisbursed(claim) ? 'Cair (Selesai)' : (claim.status === 'APPROVED' ? 'Disetujui' : (claim.status === 'PENDING_VERIFICATION' || claim.status === 'PENDING' ? 'Perlu Audit' : (claim.status === 'VERIFIED' ? 'Review Manager' : 'Ditolak')))">
                                    </span>
                                </td>

                                <!-- Kolom Tindakan (SOLID BUTTONS) -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        
                                        <!-- Tombol Cairkan (Disbursement) -->
                                        <template x-if="claim.status === 'APPROVED' && !isClaimDisbursed(claim)">
                                            <button x-on:click="openTransferModal(claim)" 
                                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-wider shadow-sm transition cursor-pointer flex items-center gap-1.5">
                                                <i class="fa-solid fa-paper-plane"></i>
                                                <span>Cairkan</span>
                                            </button>
                                        </template>

                                        <!-- Tombol Validasi (Review) -->
                                        <template x-if="claim.status === 'PENDING_VERIFICATION' || claim.status === 'PENDING'">
                                            <button x-on:click="openDetail(claim)" 
                                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-wider shadow-sm transition cursor-pointer flex items-center gap-1.5">
                                                <i class="fa-solid fa-magnifying-glass-chart"></i>
                                                <span>Validasi</span>
                                            </button>
                                        </template>

                                        <!-- Tombol Detail Standar -->
                                        <button x-on:click="openDetail(claim)" 
                                                class="px-4 py-2 bg-[#144272] hover:bg-blue-900 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-wider shadow-sm transition cursor-pointer flex items-center gap-1.5">
                                            <i class="fa-regular fa-eye"></i>
                                            <span>Detail</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- STATE KOSONG -->
                        <template x-if="filteredClaims.length === 0">
                            <tr>
                                <td colspan="7" class="py-20 px-4 text-center bg-white dark:bg-slate-900">
                                    <div class="max-w-xs mx-auto space-y-4">
                                        <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center text-slate-300 dark:text-slate-500 mx-auto shadow-inner">
                                            <i class="fa-solid fa-receipt text-2xl"></i>
                                        </div>
                                        <div class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Tidak ada klaim ditemukan</div>
                                        <p class="text-xs font-medium text-slate-500 leading-relaxed">Sesuaikan filter pencarian atau sinkronkan data terbaru dari server.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL AUDIT & VERIFIKASI (PRODUCTION SUITE) -->
        <div x-show="showDetailModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-sm"
             style="display: none;"
             x-cloak>
            
            <div class="bg-white dark:bg-[#0B132B] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-5xl w-full max-h-[92vh] overflow-hidden shadow-2xl flex flex-col"
                 x-on:click.outside="showDetailModal = false">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-[#0B132B] flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-[#144272] flex items-center justify-center text-white shadow-sm shrink-0">
                            <i class="fa-solid fa-clipboard-check text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-extrabold text-slate-900 dark:text-white">Panel Verifikasi Dokumen & Fraud</h4>
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-0.5 uppercase tracking-widest flex items-center gap-2">
                                <span>KLAIM ID:</span>
                                <span class="font-mono text-[#144272] dark:text-blue-400" x-text="selectedClaim?.id ? '#' + selectedClaim.id.toString().substring(0, 8) : ''"></span>
                            </div>
                        </div>
                    </div>
                    <button x-on:click="showDetailModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900 dark:hover:text-rose-400 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <template x-if="selectedClaim">
                    <div class="flex-1 overflow-y-auto grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-slate-200 dark:divide-slate-800">
                        
                        <!-- Left Column: Receipt Canvas -->
                        <div class="lg:col-span-7 p-6 flex flex-col bg-slate-50 dark:bg-slate-950/50">
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-extrabold text-[#144272] dark:text-blue-400 uppercase tracking-widest">
                                    Pratinjau Fisik Struk
                                </span>
                                
                                <div class="flex items-center gap-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-1.5 shadow-sm">
                                    <button x-on:click="zoomIn()" title="Perbesar" class="w-7 h-7 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-plus"></i></button>
                                    <span class="text-[10px] font-mono px-2 font-bold text-slate-500" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                                    <button x-on:click="zoomOut()" title="Perkecil" class="w-7 h-7 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-minus"></i></button>
                                    <button x-on:click="rotateImage()" title="Putar" class="w-7 h-7 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-rotate-right"></i></button>
                                    <button x-on:click="resetImageViewer()" title="Reset" class="w-7 h-7 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center text-xs cursor-pointer"><i class="fa-solid fa-arrows-rotate"></i></button>
                                </div>
                            </div>

                            <div class="flex-1 min-h-[340px] max-h-[440px] rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center relative p-3 shadow-inner">
                                <img :src="getReceiptUrl(selectedClaim.receipt_image_path)" 
                                     alt="Dokumen Struk" 
                                     :style="`transform: scale(${zoomLevel}) rotate(${rotationDegree}deg); transition: transform 0.2s ease;`"
                                     class="max-w-full max-h-[400px] object-contain select-none cursor-grab active:cursor-grabbing rounded-xl shadow-md border border-slate-200 dark:border-slate-800"
                                     onerror="this.onerror=null;this.src='https://placehold.co/400x600?text=Gambar+Struk+Tidak+Ditemukan';">
                            </div>

                            <!-- Fraud AI Forensic Card (SOLID) -->
                            <div class="mt-5 p-5 rounded-2xl border"
                                 :class="selectedClaim.is_flagged_duplicate ? 'bg-rose-600 text-white border-rose-500 shadow-lg shadow-rose-600/20' : 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-600/20'">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1 text-3xl">
                                        <i :class="selectedClaim.is_flagged_duplicate ? 'fa-solid fa-triangle-exclamation' : 'fa-solid fa-shield-halved'"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-extrabold uppercase tracking-widest text-[11px] mb-1" x-text="selectedClaim.is_flagged_duplicate ? 'Terindikasi Duplikasi Transaksi (Fraud)' : 'Lolos Verifikasi Otentik AI'"></div>
                                        <div class="text-[11px] font-bold opacity-90 leading-relaxed" x-text="selectedClaim.is_flagged_duplicate ? 'Dokumen ini memiliki kemiripan visual piksel (pHash) >= 75% atau kesamaan data dengan struk terdahulu.' : 'Sidik jari visual dan data transaksi diverifikasi unik tanpa indikasi klaim ganda.'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Detail & Audit Actions -->
                        <div class="lg:col-span-5 p-6 flex flex-col justify-between space-y-6 bg-white dark:bg-slate-900/50">
                            <div class="space-y-5">
                                
                                <div class="pb-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Metadata Struk</span>
                                    <span class="font-mono text-[11px] font-extrabold text-[#144272] dark:text-blue-400" x-text="selectedClaim.user?.department || 'Karyawan'"></span>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-950 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 space-y-4 text-xs">
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Merchant</span>
                                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="selectedClaim.merchant_name || '-'"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Nama Pemohon</span>
                                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="selectedClaim.user?.name || '-'"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">NIP</span>
                                        <span class="font-mono text-slate-900 dark:text-white font-extrabold" x-text="selectedClaim.user?.nip || '-'"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Tanggal Transaksi</span>
                                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="formatDate(selectedClaim.transaction_date || selectedClaim.created_at)"></span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pt-3.5 border-t border-slate-200 dark:border-slate-800">
                                        <span class="text-slate-900 dark:text-white font-extrabold uppercase tracking-widest text-[11px]">Total Nilai Struk</span>
                                        <span class="font-mono font-extrabold text-emerald-600 dark:text-emerald-400 text-lg" x-text="'Rp ' + Number(selectedClaim.amount || 0).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>

                                <template x-if="selectedClaim.status === 'PENDING_VERIFICATION' || selectedClaim.status === 'PENDING'">
                                    <div>
                                        <label class="text-[10px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2 block">Catatan Verifikasi Finance:</label>
                                        <textarea x-model="verificationNotes" 
                                                  rows="3" 
                                                  placeholder="Tuliskan catatan kelayakan atau alasan audit..." 
                                                  class="w-full p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-medium text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#144272] transition shadow-sm"></textarea>
                                    </div>
                                </template>
                            </div>

                            <div class="pt-5 border-t border-slate-200 dark:border-slate-800 space-y-3">
                                
                                <template x-if="selectedClaim.status === 'PENDING_VERIFICATION' || selectedClaim.status === 'PENDING'">
                                    <div class="grid grid-cols-2 gap-3">
                                        <button x-on:click="submitVerification('REJECTED')" 
                                                :disabled="isActionLoading" 
                                                class="w-full py-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-widest transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer shadow-sm border border-rose-500">
                                            <i class="fa-solid fa-xmark"></i>
                                            <span>Tolak Klaim</span>
                                        </button>
                                        
                                        <button x-on:click="submitVerification('VERIFIED')" 
                                                :disabled="isActionLoading" 
                                                class="w-full py-3 bg-[#144272] hover:bg-blue-900 active:bg-blue-950 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-widest transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer shadow-sm border border-blue-800">
                                            <i class="fa-solid fa-check" :class="{ 'animate-spin': isActionLoading }"></i>
                                            <span x-text="isActionLoading ? 'Memproses...' : 'Setujui Data'"></span>
                                        </button>
                                    </div>
                                </template>

                                <template x-if="selectedClaim.status === 'APPROVED' && !isClaimDisbursed(selectedClaim)">
                                    <button x-on:click="showDetailModal = false; openTransferModal(selectedClaim)" 
                                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] font-extrabold uppercase tracking-widest transition flex items-center justify-center gap-2 cursor-pointer shadow-sm border border-emerald-500">
                                        <i class="fa-solid fa-money-bill-transfer"></i>
                                        <span>Cairkan Saldo ke Karyawan</span>
                                    </button>
                                </template>

                                <button x-on:click="showDetailModal = false" 
                                        class="w-full py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-[11px] font-extrabold uppercase tracking-widest rounded-xl transition flex items-center justify-center cursor-pointer shadow-sm">
                                    <span>Tutup Panel</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL DISBURSEMENT SALDO -->
        <div x-show="showTransferModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             style="display: none;"
             x-cloak>
            
            <div class="bg-white dark:bg-[#0B132B] border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-7 shadow-2xl space-y-6"
                 x-on:click.outside="showTransferModal = false">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-wallet text-sm"></i>
                        </div>
                        <span>Konfirmasi Pencairan Dana</span>
                    </h4>
                    <button x-on:click="showTransferModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900 dark:hover:text-rose-400 transition-colors cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <template x-if="selectedClaim">
                    <div class="space-y-4 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-950 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3.5 shadow-inner">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Penerima</span>
                                <span class="font-extrabold text-slate-900 dark:text-white" x-text="selectedClaim.user?.name || 'Karyawan'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Merchant Asal</span>
                                <span class="font-extrabold text-slate-900 dark:text-white" x-text="selectedClaim.merchant_name"></span>
                            </div>
                            <div class="flex justify-between items-center pt-3.5 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-[11px] font-extrabold uppercase tracking-widest text-slate-900 dark:text-white">Nominal Transfer</span>
                                <span class="font-mono font-extrabold text-emerald-600 dark:text-emerald-400 text-lg" x-text="'Rp ' + Number(selectedClaim.amount || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2 block">Catatan Mutasi Dompet:</label>
                            <input type="text" x-model="transferNotes" class="w-full p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white font-medium text-xs focus:outline-none focus:border-emerald-600 transition shadow-sm">
                        </div>
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center gap-3">
                    <button x-on:click="showTransferModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white rounded-xl text-[11px] uppercase tracking-widest font-extrabold transition cursor-pointer shadow-sm">
                        Batal
                    </button>
                    <button x-on:click="executeTransfer()" 
                            :disabled="isActionLoading" 
                            class="flex-2 py-3 px-5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] uppercase tracking-widest font-extrabold transition disabled:opacity-50 flex items-center justify-center gap-2 cursor-pointer shadow-sm border border-emerald-500">
                        <i class="fa-solid fa-paper-plane" :class="{ 'animate-spin': isActionLoading }"></i>
                        <span x-text="isActionLoading ? 'Memproses...' : 'Kirim Saldo'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>