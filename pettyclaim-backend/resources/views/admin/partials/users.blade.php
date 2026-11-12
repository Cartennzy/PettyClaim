<template x-if="activeTab === 'users'">
    <div class="space-y-6 select-none transition-colors duration-200"
         x-data="{ 
            userSearch: '',
            selectedDepartment: 'all',
            userCounts: {
                total: {{ count($users ?? []) }},
                karyawan: {{ collect($users ?? [])->where('role', 'karyawan')->count() }},
                manager: {{ collect($users ?? [])->where('role', 'manager')->count() }},
                admin: {{ collect($users ?? [])->where('role', 'admin')->count() }}
            },

            async deleteUserConfirm(userId, userName) {
                const isDarkTheme = document.documentElement.classList.contains('dark');
                
                const confirmResult = await Swal.fire({
                    title: 'Konfirmasi Hapus Akun',
                    html: `Apakah Anda yakin ingin menghapus akun <b class='text-rose-500'>${userName}</b> secara permanen dari database?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal',
                    background: isDarkTheme ? '#0B132B' : '#ffffff',
                    color: isDarkTheme ? '#f8fafc' : '#0f172a',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl'
                    }
                });

                if (confirmResult.isConfirmed) {
                    const form = document.getElementById('delete-user-form-' + userId);
                    if (form) form.submit();
                }
            }
         }">
        
        <!-- Header & Action Button Bar -->
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 rounded-2xl bg-white dark:bg-slate-900 border flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
            <div>
                <h3 class="text-base font-extrabold text-[#144272] dark:text-blue-400 tracking-tight">Kelola Pengguna & Hak Akses</h3>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Kelola akun terkelompok per divisi untuk Karyawan (Pemohon), Manajer (Approver), dan Tim Finance.</p>
            </div>
            
            <button type="button" 
                    x-on:click="activeModal = 'addUser'" 
                    class="px-5 py-2.5 bg-[#144272] hover:bg-blue-900 active:scale-95 text-xs font-extrabold tracking-widest uppercase rounded-xl text-white shadow-sm transition flex items-center gap-2 cursor-pointer self-start md:self-auto whitespace-nowrap">
                <i class="fa-solid fa-user-plus text-sm"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>

        <!-- Metric Overview Cards: 3 PURE SOLID COLOR CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            
            <!-- Card 1: Karyawan -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-sky-600 dark:bg-sky-700 text-white shadow-md border border-sky-500 hover:scale-[1.01] transition-transform">
                <i class="fa-solid fa-user absolute -bottom-3 -right-2 text-7xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-sky-100">Karyawan (Pemohon)</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-user text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-black tracking-tight text-white" x-text="userCounts.karyawan"></div>
                <div class="relative z-10 mt-3 text-[11px] font-medium text-sky-100">Hak akses pengajuan klaim & scan struk</div>
            </div>

            <!-- Card 2: Manajer -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-amber-600 dark:bg-amber-700 text-white shadow-md border border-amber-500 hover:scale-[1.01] transition-transform">
                <i class="fa-solid fa-user-check absolute -bottom-3 -right-2 text-7xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-100">Manajer (Approver)</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-user-check text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-black tracking-tight text-white" x-text="userCounts.manager"></div>
                <div class="relative z-10 mt-3 text-[11px] font-medium text-amber-100">Hak akses verifikasi persetujuan divisi</div>
            </div>

            <!-- Card 3: Admin Keuangan -->
            <div class="relative overflow-hidden p-6 rounded-2xl bg-indigo-600 dark:bg-indigo-700 text-white shadow-md border border-indigo-500 hover:scale-[1.01] transition-transform">
                <i class="fa-solid fa-user-shield absolute -bottom-3 -right-2 text-7xl text-white/15 pointer-events-none"></i>
                <div class="relative z-10 flex items-center justify-between mb-3">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-100">Admin Keuangan</span>
                    <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white">
                        <i class="fa-solid fa-user-shield text-sm"></i>
                    </span>
                </div>
                <div class="relative z-10 text-3xl font-black tracking-tight text-white" x-text="userCounts.admin"></div>
                <div class="relative z-10 mt-3 text-[11px] font-medium text-indigo-100">Hak akses audit, jurnal & disbursement</div>
            </div>
        </div>

        <?php
            $rawUsers =$users ?? [];
            $groupedUsers = [];
            foreach ($rawUsers as$item) {
                $d =$item->department ?? (strtolower($item->role) === 'admin' ? 'Finance Accounting' : 'Operasional');$groupedUsers[$d][] =$item;
            }
        ?>

        <!-- TABEL PENGGUNA TERKELOMPOK PER DIVISI -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden">
            
            <!-- Toolbar Filter & Search -->
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900">
                <div>
                    <h4 class="font-extrabold text-[#144272] dark:text-blue-400 text-sm">Daftar Akun Pengguna Terkelompok</h4>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Seluruh akun terdaftar yang dikelompokkan berdasarkan unit departemen kerja.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Selector Filter Divisi -->
                    <div class="relative">
                        <select x-model="selectedDepartment" 
                                class="w-full sm:w-auto pl-4 pr-10 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-200 text-xs font-extrabold focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] transition shadow-sm cursor-pointer appearance-none">
                            <option value="all">Semua Divisi (<?php echo count($groupedUsers); ?>)</option>
                            <?php foreach ($groupedUsers as $deptKey =>$uList): ?>
                                <option value="<?php echo htmlspecialchars($deptKey); ?>"><?php echo htmlspecialchars($deptKey); ?> (<?php echo count($uList); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64 md:w-72">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[#144272] text-xs"></i>
                        <input x-model="userSearch" 
                               type="text" 
                               placeholder="Cari nama, NIP, email, divisi..." 
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 text-xs font-medium focus:outline-none focus:border-[#144272] focus:ring-1 focus:ring-[#144272] transition shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Tabel Data Pengguna (FULL COLOR BLOCKS) -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-[#144272] text-white text-[10px] font-extrabold tracking-wider uppercase">
                        <tr>
                            <th class="py-4 px-6 w-[35%] whitespace-nowrap">Pengguna & NIP</th>
                            <th class="py-4 px-6 w-[25%] whitespace-nowrap">Kontak & Email</th>
                            <th class="py-4 px-6 w-[20%] whitespace-nowrap">Divisi / Departemen</th>
                            <th class="py-4 px-6 text-center w-[10%] whitespace-nowrap">Peran (Role)</th>
                            <th class="py-4 px-6 text-center w-[10%] whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-900">
                        <?php if (empty($groupedUsers)): ?>
                            <tr>
                                <td colspan="5" class="py-16 px-4 text-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 flex items-center justify-center mx-auto text-slate-300 dark:text-slate-500 shadow-inner mb-4">
                                        <i class="fa-solid fa-users text-2xl"></i>
                                    </div>
                                    <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">Belum Ada Akun Pengguna</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Belum ada akun yang didaftarkan dalam sistem.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($groupedUsers as $deptName =>$usersInDept): ?>
                                
                                <?php
                                    // Logika Dinamis Penentuan Icon Departemen (Aman dari Parser)
                                    $iconClass = 'fa-building'; 
                                    $dLower = strtolower($deptName);
                                    
                                    $itKeywords = ['it', 'teknologi', 'information'];$finKeywords = ['finance', 'accounting', 'keuangan', 'akuntansi'];
                                    $hrKeywords = ['hr', 'human', 'personalia'];$mktKeywords = ['marketing', 'sales', 'pemasaran'];
                                    $opsKeywords = ['operasional', 'operation'];$logKeywords = ['logistik', 'gudang', 'warehouse'];

                                    foreach($itKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-laptop-code'; break; } }
                                    foreach($finKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-file-invoice-dollar'; break; } }
                                    foreach($hrKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-users-gear'; break; } }
                                    foreach($mktKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-bullhorn'; break; } }
                                    foreach($opsKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-boxes-stacked'; break; } }
                                    foreach($logKeywords as $k) { if(str_contains($dLower, $k)) {$iconClass = 'fa-warehouse'; break; } }
                                ?>

                                <!-- Baris Pembatas Grup Divisi (Solid Block Background dengan Ikon Departemen) -->
                                <tr class="bg-slate-100 dark:bg-[#0B132B] border-b-[4px] border-white dark:border-slate-900 transition-colors"
                                    x-show="selectedDepartment === 'all' || selectedDepartment === '<?php echo addslashes($deptName); ?>'">
                                    <td colspan="5" class="py-3 px-6 border-r-2 border-white dark:border-slate-900">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 text-[#144272] dark:text-blue-400">
                                                <i class="fa-solid <?php echo $iconClass; ?> text-sm"></i>
                                                <span class="font-extrabold text-xs uppercase tracking-widest text-slate-900 dark:text-white whitespace-nowrap">
                                                    <?php echo htmlspecialchars($deptName); ?>
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-extrabold text-[#144272] dark:text-blue-400 whitespace-nowrap">
                                                <?php echo count($usersInDept); ?> Anggota
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Anggota di Bawah Divisi Tersebut -->
                                <?php foreach ($usersInDept as$user): ?>
                                    <?php
                                        $uDept = $user->department ?? (strtolower($user->role) === 'admin' ? 'Finance Accounting' : 'Operasional');
                                        // Pengecekan aman untuk user search (tanpa OR)
                                        $s1 = strtolower($user->name);
                                        $s2 = strtolower($user->nip ?? '');
                                        $s3 = strtolower($user->email);
                                        $s4 = strtolower($uDept);
                                        $s5 = strtolower($user->role);
                                        $uSearchable =$s1 . ' ' . $s2 . ' ' .$s3 . ' ' . $s4 . ' ' .$s5;
                                    ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 border-b-[4px] border-slate-50 dark:border-slate-950 transition-colors"
                                        x-show="(selectedDepartment === 'all' || selectedDepartment === '<?php echo addslashes($deptName); ?>') && (userSearch === '' ? true : '<?php echo addslashes($uSearchable); ?>'.includes(userSearch.toLowerCase()))">
                                        
                                        <!-- Kolom Profil User -->
                                        <td class="py-4 px-6 border-r-2 border-slate-50 dark:border-slate-950">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 border-2 border-white dark:border-slate-700 flex items-center justify-center font-black text-[#144272] dark:text-blue-400 text-sm shadow-sm shrink-0">
                                                    <?php echo strtoupper(substr($user->name, 0, 1)); ?>
                                                </div>
                                                <div class="whitespace-nowrap">
                                                    <p class="font-extrabold text-slate-900 dark:text-white text-[13px]"><?php echo htmlspecialchars($user->name); ?></p>
                                                    <span class="font-mono font-bold text-[10px] text-slate-400 mt-0.5 block">NIP: <?php echo htmlspecialchars($user->nip ?? '-'); ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Kontak -->
                                        <td class="py-4 px-6 border-r-2 border-slate-50 dark:border-slate-950">
                                            <p class="text-slate-900 dark:text-slate-200 font-extrabold text-xs whitespace-nowrap"><?php echo htmlspecialchars($user->email); ?></p>
                                            <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-1 whitespace-nowrap">
                                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i> 
                                                <span><?php echo htmlspecialchars($user->phone_number ?? '-'); ?></span>
                                            </span>
                                        </td>

                                        <!-- Kolom Dept -->
                                        <td class="py-4 px-6 font-extrabold text-slate-700 dark:text-slate-300 text-[11px] uppercase tracking-wider border-r-2 border-slate-50 dark:border-slate-950 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-building text-slate-400 text-xs"></i>
                                                <span><?php echo htmlspecialchars($uDept); ?></span>
                                            </div>
                                        </td>

                                        <!-- Kolom Role (TEXT MURNI TEGAS) -->
                                        <td class="py-4 px-6 text-center border-r-2 border-slate-50 dark:border-slate-950 whitespace-nowrap">
                                            <span class="font-black text-[11px] uppercase tracking-widest <?php echo strtolower($user->role) === 'admin' ? 'text-indigo-600 dark:text-indigo-400' : (strtolower($user->role) === 'manager' ? 'text-amber-600 dark:text-amber-500' : 'text-sky-600 dark:text-sky-400'); ?>">
                                                <?php echo htmlspecialchars($user->role); ?>
                                            </span>
                                        </td>

                                        <!-- Kolom Aksi (Tombol Solid) -->
                                        <td class="py-4 px-6 text-center whitespace-nowrap">
                                            <?php if (auth()->id() !== $user->id): ?>
                                                <form id="delete-user-form-<?php echo $user->id; ?>" action="<?php echo route('admin.users.destroy', $user->id); ?>" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" 
                                                            x-on:click="deleteUserConfirm('<?php echo $user->id; ?>', '<?php echo addslashes($user->name); ?>')"
                                                            class="p-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white border border-rose-500 transition-all cursor-pointer shadow-sm"
                                                            title="Hapus Akun Pengguna">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Akun Anda</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>