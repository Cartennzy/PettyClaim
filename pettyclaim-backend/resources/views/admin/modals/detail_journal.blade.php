<!-- OVERLAY MODAL DETAIL JURNAL -->
<div x-data="{ showImageModal: false }"
     x-show="activeModal === 'detail'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 dark:bg-slate-950/85 backdrop-blur-md" 
     style="display: none;"
     x-cloak>

    <div x-on:click.outside="activeModal = null" class="w-full max-w-4xl bg-white dark:bg-[#0B132B] border border-slate-200 dark:border-slate-800/90 rounded-3xl shadow-2xl p-6 relative space-y-6 transition-colors duration-200">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4">
            <div class="flex items-center gap-4">
                <!-- Icon Header (Solid Block, NO badge/transparan) -->
                <div class="w-12 h-12 rounded-xl bg-[#144272] flex items-center justify-center text-white shadow-sm shrink-0">
                    <i class="fa-solid fa-file-invoice text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Rincian Draf Jurnal Pembukuan</h3>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Detail posting jurnal double-entry dan status klaim transaksi.</p>
                </div>
            </div>
            <!-- Tombol Tutup (X) -->
            <button type="button" x-on:click="activeModal = null" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900 dark:hover:text-rose-400 transition-colors cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Upper Grid Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KOTAK 1: Detail Transaksi Struk (Left Panel) -->
            <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-5">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-[#144272] dark:text-blue-400 border-b border-slate-200 dark:border-slate-800/60 pb-3">
                    Detail Transaksi Struk
                </div>

                <div class="grid grid-cols-2 gap-y-5 gap-x-4 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">No. Transaksi / Kode</span>
                        <span class="font-extrabold text-[#144272] dark:text-blue-400 text-sm" x-text="modalData.number || '-'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Tanggal Transaksi</span>
                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="modalData.date || '-'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Nama Toko / Merchant</span>
                        <span class="font-extrabold text-slate-900 dark:text-white text-sm" x-text="modalData.merchant || '-'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Diajukan Oleh</span>
                        <span class="font-extrabold text-slate-900 dark:text-white" x-text="modalData.user || 'Karyawan Pemohon'"></span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60 flex items-end justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1">Total Pengeluaran</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xl" x-text="modalData.amount || 'Rp 0'"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="modalData.receipt_url && modalData.receipt_url.length > 5">
                            <button type="button" x-on:click="showImageModal = true" class="px-4 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-[#144272] dark:text-blue-400 font-extrabold text-xs rounded-xl transition-colors border border-blue-100 dark:border-blue-800 flex items-center gap-2 shadow-sm cursor-pointer">
                                <i class="fa-solid fa-image"></i> Lihat Struk
                            </button>
                        </template>
                        <template x-if="!modalData.receipt_url || modalData.receipt_url.length <= 5">
                            <span class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 text-[11px] font-bold shadow-sm">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Struk Belum Diunggah
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- KOTAK 2: Status Persetujuan (Right Panel) -->
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 flex flex-col h-full">
                <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800/60 pb-3 mb-4">
                    Status Persetujuan
                </div>

                <!-- SOLID COLOR BLOCK UNTUK STATUS (TANPA BADGE TRANSPARAN) -->
                <div class="flex-1 flex flex-col items-center justify-center text-center p-4 rounded-xl border"
                     :class="
                       modalData.status.toUpperCase() === 'APPROVED' ? 'bg-emerald-600 border-emerald-500 text-white shadow-lg shadow-emerald-600/20' :
                       (modalData.status.toUpperCase() === 'PENDING' || modalData.status.toUpperCase() === 'PENDING_VERIFICATION') ? 'bg-amber-500 border-amber-400 text-white shadow-lg shadow-amber-500/20' :
                       'bg-rose-600 border-rose-500 text-white shadow-lg shadow-rose-600/20'
                     ">
                     <i class="fa-solid text-3xl mb-2"
                        :class="
                           modalData.status.toUpperCase() === 'APPROVED' ? 'fa-circle-check' :
                           (modalData.status.toUpperCase() === 'PENDING' || modalData.status.toUpperCase() === 'PENDING_VERIFICATION') ? 'fa-clock-rotate-left' :
                           'fa-circle-xmark'
                        "></i>
                     <h5 class="font-extrabold text-sm uppercase tracking-widest"
                         x-text="
                           modalData.status.toUpperCase() === 'APPROVED' ? 'Disetujui' :
                           (modalData.status.toUpperCase() === 'PENDING' || modalData.status.toUpperCase() === 'PENDING_VERIFICATION') ? 'Review' :
                           'Ditolak'
                         "></h5>
                     <p class="text-[10px] font-bold mt-2 opacity-90 leading-relaxed"
                        x-text="
                           modalData.status.toUpperCase() === 'APPROVED' ? 'Klaim sah & telah terposting ke jurnal otomatis.' :
                           (modalData.status.toUpperCase() === 'PENDING' || modalData.status.toUpperCase() === 'PENDING_VERIFICATION') ? 'Menunggu verifikasi lanjutan dari Manajer.' :
                           'Klaim ditolak atau terdeteksi ganda.'
                        "></p>
                </div>
            </div>
        </div>

        <!-- Lower Panel: Tabel Jurnal (FULL SOLID BLOCKS) -->
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800/80 overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#0B132B]">
                <h4 class="text-[10px] font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-widest">Posting Draf Jurnal (Debit & Kredit)</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#144272] text-white text-[10px] uppercase tracking-wider font-extrabold">
                        <tr>
                            <th class="py-3.5 px-5 w-12 text-center">NO</th>
                            <th class="py-3.5 px-4">AKUN PEMBUKUAN</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap">DEBIT (RP)</th>
                            <th class="py-3.5 px-5 text-right whitespace-nowrap">KREDIT (RP)</th>
                        </tr>
                    </thead>
                    <tbody class="border-b-[4px] border-white dark:border-slate-900">
                        <!-- BARIS 1: DEBIT (FULL WARNA BIRU MUDA) -->
                        <tr class="bg-blue-50 dark:bg-blue-900/20 border-b border-white dark:border-slate-900">
                            <td class="py-3.5 px-5 text-center font-extrabold text-blue-900/50 dark:text-blue-400/50">1</td>
                            <!-- NAMA AKUN MURNI CETAK TEBAL (TANPA BADGE) -->
                            <td class="py-3.5 px-4 font-extrabold text-[#144272] dark:text-blue-400 text-[13px]" x-text="modalData.debit || '1101-Beban Operasional'"></td>
                            <td class="py-3.5 px-5 text-right font-extrabold text-[#144272] dark:text-blue-400 text-[13px] whitespace-nowrap" x-text="modalData.amount || 'Rp 0'"></td>
                            <td class="py-3.5 px-5 text-right font-bold text-blue-300 dark:text-blue-900/50 whitespace-nowrap">-</td>
                        </tr>
                        
                        <!-- BARIS 2: KREDIT (FULL WARNA HIJAU MUDA) -->
                        <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                            <td class="py-3.5 px-5 text-center font-extrabold text-emerald-900/50 dark:text-emerald-400/50">2</td>
                            <!-- NAMA AKUN MURNI CETAK TEBAL MENJOROK (TANPA BADGE) -->
                            <td class="py-3.5 px-4 pl-10 font-extrabold text-emerald-700 dark:text-emerald-400 text-[13px]" x-text="modalData.credit || '1001-Kas Kecil'"></td>
                            <td class="py-3.5 px-5 text-right font-bold text-emerald-300 dark:text-emerald-900/50 whitespace-nowrap">-</td>
                            <td class="py-3.5 px-5 text-right font-extrabold text-emerald-700 dark:text-emerald-400 text-[13px] whitespace-nowrap" x-text="modalData.amount || 'Rp 0'"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 dark:bg-[#0B132B]">
                            <td colspan="2" class="py-4 px-5 font-extrabold text-slate-800 dark:text-white uppercase tracking-widest text-[10px]">TOTAL BALANCE</td>
                            <td class="py-4 px-5 text-right font-extrabold text-slate-900 dark:text-white text-[13px] whitespace-nowrap" x-text="modalData.amount || 'Rp 0'"></td>
                            <td class="py-4 px-5 text-right font-extrabold text-slate-900 dark:text-white text-[13px] whitespace-nowrap" x-text="modalData.amount || 'Rp 0'"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- LIGHTBOX PREVIEW -->
        <div x-show="showImageModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/90 dark:bg-slate-950/95 backdrop-blur-2xl" 
             style="display: none;"
             x-cloak>
            
            <div x-on:click.outside="showImageModal = false" class="relative max-w-3xl w-full max-h-[90vh] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/90 rounded-2xl overflow-hidden p-4 flex flex-col items-center shadow-2xl space-y-3">
                
                <div class="w-full flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800/80 px-2">
                    <div class="flex items-center gap-2">
                        <!-- TITIK KEDAP-KEDIP (ANIMATE-PULSE) SUDAH DIBUANG DARI SINI -->
                        <i class="fa-solid fa-image text-slate-400"></i>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Bukti Struk Transaksi Fisik Karyawan</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="modalData.receipt_url" download target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-sky-600 dark:text-sky-400 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-download"></i> Unduh Struk
                        </a>
                        <button type="button" x-on:click="showImageModal = false" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white transition cursor-pointer shadow-sm">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="w-full flex-1 overflow-auto flex items-center justify-center p-2 bg-slate-100 dark:bg-slate-950/80 rounded-xl border border-slate-200 dark:border-slate-800/60 min-h-[300px]">
                    <img :src="modalData.receipt_url" alt="Foto Struk Transaksi Asli" class="max-h-[72vh] w-auto object-contain rounded-lg shadow-md border border-slate-200 dark:border-slate-800">
                </div>

            </div>
        </div>

    </div>
</div>