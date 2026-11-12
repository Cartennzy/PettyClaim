@extends('layouts.app')

@section('title', 'Dashboard Admin Keuangan - PettyClaim')

@section('content')
    <!-- MENU 1: DRAF JURNAL PEMBUKUAN -->
    <div x-show="activeTab === 'jurnal'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         class="space-y-6 select-none transition-colors duration-200"
         x-data="{ searchQuery: '' }">
        
        <!-- Metric Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Card 1: Total Catatan Jurnal -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-indigo-600 dark:bg-indigo-700 text-white shadow-sm border border-indigo-500/30">
                <i class="fa-solid fa-receipt absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-100">Total Catatan Jurnal</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-receipt text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white">{{ count($journals) }}</div>
                <div class="relative z-10 mt-3 text-xs text-indigo-100">
                    <span class="font-bold text-white">100% Sah</span> — Hasil konversi otomatis dari klaim
                </div>
            </div>

            <!-- Card 2: Total Kas Terjurnal -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-emerald-600 dark:bg-emerald-700 text-white shadow-sm border border-emerald-500/30">
                <i class="fa-solid fa-money-bill-wave absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Total Kas Terjurnal</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-money-bill-wave text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white">
                    Rp {{ number_format($journals->sum('amount'), 0, ',', '.') }}
                </div>
                <div class="relative z-10 mt-3 text-xs text-emerald-100">
                    <span class="font-bold text-white">Akun 1001</span> — Kredit Pengeluaran Kas Kecil
                </div>
            </div>

            <!-- Card 3: Pencegah Struk Ganda -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-purple-600 dark:bg-purple-700 text-white shadow-sm border border-purple-500/30">
                <i class="fa-solid fa-shield-halved absolute -bottom-3 -right-2 text-7xl text-white/10 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-100">Pencegah Struk Ganda</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-extrabold tracking-tight text-white">Aktif</div>
                <div class="relative z-10 mt-3 text-xs text-purple-100">
                    <span class="font-bold text-white">Deteksi Otomatis</span> — Batas Kemiripan &ge; 85%
                </div>
            </div>
        </div>

        <!-- MAIN TABLE CARD: JURNAL UMUM -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden transition-colors duration-200">
            
            <!-- Header Panel Jurnal -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Draf Jurnal Umum</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pencatatan pembukuan otomatis berbasis sistem klaim kas kecil.</p>
                </div>
                <div class="relative w-full md:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input x-model="searchQuery" 
                           type="text" 
                           placeholder="Cari no. bukti, merchant, akun..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-200 placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] transition shadow-sm">
                </div>
            </div>

            <!-- TABEL JURNAL AKUNTANSI (FULL WARNA SOLID BLOK) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#144272] dark:bg-slate-950 text-[11px] uppercase tracking-wider font-extrabold text-white">
                        <tr>
                            <th scope="col" class="py-4 px-5 whitespace-nowrap">Tanggal & No. Bukti</th>
                            <th scope="col" class="py-4 px-4 min-w-[180px]">Merchant & Keterangan</th>
                            <th scope="col" class="py-4 px-4">Nama Akun (COA)</th>
                            <th scope="col" class="py-4 px-4 text-center">Ref</th>
                            <th scope="col" class="py-4 px-4 text-right">Debit (Rp)</th>
                            <th scope="col" class="py-4 px-4 text-right">Kredit (Rp)</th>
                            <th scope="col" class="py-4 px-4 text-center">Status</th>
                            <th scope="col" class="py-4 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    
                    @forelse($journals as $journal)
                    @php
                        $debitParts = explode('-', $journal->account_debit, 2);
                        $debitRef = count($debitParts) > 1 ? $debitParts[0] : '1101';
                        $debitName = count($debitParts) > 1 ? $debitParts[1] : $journal->account_debit;

                        $creditParts = explode('-', $journal->account_credit, 2);
                        $creditRef = count($creditParts) > 1 ? $creditParts[0] : '1001';
                        $creditName = count($creditParts) > 1 ? $creditParts[1] : 'Kas Kecil';

                        $claimStatus = $journal->claim->status ?? 'APPROVED';

                        $rawPath = $journal->claim->receipt_image_path ?? null;
                        if ($rawPath) {
                            if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://')) {
                                $receiptUrl = $rawPath;
                            } else {
                                $cleanPath = ltrim($rawPath, '/');
                                $receiptUrl = asset(str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath);
                            }
                        } else {
                            $receiptUrl = '';
                        }
                    @endphp

                    <!-- 1 TRANSAKSI = 1 TBODY DENGAN BORDER BAWAH TEBAL -->
                    <tbody class="border-b-[6px] border-white dark:border-slate-900 group"
                           x-show="searchQuery === '' || '{{ strtolower($journal->journal_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($debitName) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($journal->claim->merchant_name ?? '') }}'.includes(searchQuery.toLowerCase())">
                        
                        <!-- BARIS 1: DEBIT (FULL BLOK WARNA BIRU MUDA) -->
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            
                            <!-- Kolom Informasi Umum (Rowspan 2) -->
                            <td class="py-4 px-5 align-top border-r-2 border-white dark:border-slate-900 whitespace-nowrap" rowspan="2">
                                <div class="font-extrabold text-[#144272] dark:text-blue-400 text-sm">
                                    {{ $journal->journal_number }}
                                </div>
                                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 mt-1">
                                    {{ \Carbon\Carbon::parse($journal->entry_date)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-4 px-4 align-top border-r-2 border-white dark:border-slate-900" rowspan="2">
                                <div class="font-extrabold text-slate-900 dark:text-white text-xs">
                                    {{ $journal->claim->merchant_name ?? 'Operasional Umum' }}
                                </div>
                                <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 mt-1">
                                    Pemohon: {{ $journal->claim->user->name ?? 'Karyawan' }}
                                </div>
                            </td>

                            <!-- Kolom Data Debit -->
                            <td class="py-3 px-4 text-blue-900 dark:text-blue-100 font-extrabold border-r border-white dark:border-slate-900 whitespace-nowrap">
                                {{ $debitName }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[11px] text-blue-700/70 border-r border-white dark:border-slate-900">
                                {{ $debitRef }}
                            </td>
                            
                            <!-- DITAMBAHKAN whitespace-nowrap AGAR RP DAN ANGKA SEJAJAR -->
                            <td class="py-3 px-4 text-right font-extrabold text-blue-800 dark:text-blue-400 text-sm border-r border-white dark:border-slate-900 whitespace-nowrap">
                                Rp {{ number_format($journal->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-blue-300 dark:text-blue-900/50 whitespace-nowrap">
                                -
                            </td>

                            <!-- Kolom Status & Aksi (Rowspan 2) -->
                            <td class="py-4 px-4 align-middle text-center border-l-2 border-white dark:border-slate-900" rowspan="2">
                                @if(strtoupper($claimStatus) === 'APPROVED')
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-xs uppercase tracking-widest">Disetujui</span>
                                @elseif(strtoupper($claimStatus) === 'PENDING' || strtoupper($claimStatus) === 'PENDING_VERIFICATION')
                                    <span class="font-extrabold text-amber-600 dark:text-amber-400 text-xs uppercase tracking-widest">Review</span>
                                @else
                                    <span class="font-extrabold text-rose-600 dark:text-rose-400 text-xs uppercase tracking-widest">Ditolak</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 align-middle text-center border-l-2 border-white dark:border-slate-900" rowspan="2">
                                <button type="button" 
                                        @click="activeModal = 'detail'; modalData = { number: '{{ $journal->journal_number }}', merchant: '{{ $journal->claim->merchant_name ?? 'Tidak Ada' }}', amount: 'Rp {{ number_format($journal->amount, 0, ',', '.') }}', date: '{{ $journal->entry_date }}', debit: '{{ $journal->account_debit }}', credit: '{{ $journal->account_credit }}', user: '{{ $journal->claim->user->name ?? 'Karyawan Pemohon' }}', status: '{{ $claimStatus }}', receipt_url: '{{ $receiptUrl }}' }" 
                                        class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-[#144272] dark:text-blue-400 hover:bg-[#144272] hover:text-white dark:hover:bg-blue-400 dark:hover:text-slate-900 transition-all duration-200 shadow-sm" 
                                        title="Lihat Detail Transaksi">
                                    <i class="fa-solid fa-file-invoice text-sm"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- BARIS 2: KREDIT (FULL BLOK WARNA HIJAU MUDA) -->
                        <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                            <!-- Kolom Data Kredit -->
                            <td class="py-3 px-4 pl-10 text-emerald-900 dark:text-emerald-100 font-extrabold border-r border-white dark:border-slate-900 whitespace-nowrap">
                                {{ $creditName }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-[11px] text-emerald-700/70 border-r border-white dark:border-slate-900">
                                {{ $creditRef }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-300 dark:text-emerald-900/50 border-r border-white dark:border-slate-900 whitespace-nowrap">
                                -
                            </td>
                            
                            <!-- DITAMBAHKAN whitespace-nowrap AGAR RP DAN ANGKA SEJAJAR -->
                            <td class="py-3 px-4 text-right font-extrabold text-emerald-800 dark:text-emerald-400 text-sm whitespace-nowrap">
                                Rp {{ number_format($journal->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                    
                    @empty
                    <!-- EMPTY STATE -->
                    <tbody>
                        <tr>
                            <td colspan="8" class="py-20 px-4 text-center bg-white dark:bg-slate-900">
                                <div class="max-w-md mx-auto space-y-4">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center mx-auto text-slate-300 dark:text-slate-500 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-2xl"></i>
                                    </div>
                                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Belum Ada Transaksi Jurnal</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                                        Seluruh pengajuan klaim kas kecil yang disetujui Manajer akan otomatis diposting ke tabel pembukuan di sini secara double-entry.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    @endforelse
                </table>
            </div>

        </div>
    </div>

    <!-- MEMANGGIL COMPONENT PARTIALS LAINNYA -->
    <div x-show="activeTab === 'claims'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0">
        @include('admin.partials.claims')
    </div>

    <div x-show="activeTab === 'reports'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0">
        @include('admin.partials.reports')
    </div>

    <div x-show="activeTab === 'limits'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0">
        @include('admin.partials.limits')
    </div>

    <div x-show="activeTab === 'users'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0 translate-y-2" 
         x-transition:enter-end="opacity-100 translate-y-0">
        @include('admin.partials.users')
    </div>

    <!-- MEMANGGIL MODAL DETAIL JURNAL & ADD USER -->
    @include('admin.modals.detail_journal')
    @include('admin.modals.add_user')
    
@endsection