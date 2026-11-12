<style>
    @keyframes errorShake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-4px); }
        40%, 80% { transform: translateX(4px); }
    }
    .animate-shake {
        animation: errorShake 0.4s cubic-bezier(.36,.07,.19,.97) both;
    }
</style>

<div x-show="activeModal === 'addUser'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 backdrop-blur-none"
     x-transition:enter-end="opacity-100 backdrop-blur-md"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 backdrop-blur-md"
     x-transition:leave-end="opacity-0 backdrop-blur-none"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/75 dark:bg-black/85 backdrop-blur-md" 
     x-cloak
     style="display: none;">

    <div x-on:click.outside="activeModal = null; resetForm()" 
         x-show="activeModal === 'addUser'"
         x-init="
            @if($errors->any()) 
                activeModal = 'addUser'; 
            @endif
            @if(old('phone_number'))
                let oldPhone = '{{ old('phone_number') }}';
                if(oldPhone.startsWith('+62')) {
                    rawPhone = oldPhone.substring(3);
                } else if(oldPhone.startsWith('0')) {
                    rawPhone = oldPhone.substring(1);
                } else {
                    rawPhone = oldPhone;
                }
            @endif
         "
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         x-data="{ 
             showPass: false,
             isSubmitting: false,
             shake: false,
             rawPhone: '',
             form: {
                 nip: '{{ old('nip') }}',
                 name: '{{ old('name') }}',
                 email: '{{ old('email') }}',
                 department: '{{ old('department', 'Operasional') }}',
                 role: '{{ old('role', 'karyawan') }}',
                 password: ''
             },
             errors: {
                 nip: '{{ $errors->first('nip') }}',
                 name: '{{ $errors->first('name') }}',
                 email: '{{ $errors->first('email') }}',
                 phone_number: '{{ $errors->first('phone_number') }}',
                 password: '{{ $errors->first('password') }}'
             },
             
             handlePhoneInput(e) {
                 let val = e.target.value.replace(/\D/g, '');
                 if (val.startsWith('62')) val = val.substring(2);
                 else if (val.startsWith('0')) val = val.substring(1);
                 this.rawPhone = val;
                 this.errors.phone_number = null;
             },

             validate() {
                 this.errors = {};
                 let isValid = true;
                 
                 if (this.form.nip.trim() !== '') {
                     if (this.form.nip.trim().length < 5) {
                         this.errors.nip = 'NIP minimal 5 karakter';
                         isValid = false;
                     } else if (!/^[a-zA-Z0-9-]+$/.test(this.form.nip.trim())) {
                         this.errors.nip = 'NIP hanya boleh huruf, angka, dan strip (-)';
                         isValid = false;
                     }
                 }

                 if (!this.form.name.trim()) {
                     this.errors.name = 'Nama lengkap wajib diisi';
                     isValid = false;
                 } else if (this.form.name.trim().length < 3) {
                     this.errors.name = 'Nama minimal 3 karakter';
                     isValid = false;
                 } else if (!/^[a-zA-Z\s.,']+$/.test(this.form.name.trim())) {
                     this.errors.name = 'Nama hanya boleh huruf dan spasi';
                     isValid = false;
                 }
                 
                 if (!this.form.email.trim()) {
                     this.errors.email = 'Alamat email wajib diisi';
                     isValid = false;
                 } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim())) {
                     this.errors.email = 'Format email tidak valid';
                     isValid = false;
                 }

                 if (this.rawPhone.trim() !== '') {
                     if (this.rawPhone.length < 8 || this.rawPhone.length > 13) {
                         this.errors.phone_number = 'Panjang nomor harus 8-13 digit angka setelah +62';
                         isValid = false;
                     }
                 }
                 
                 if (!this.form.password) {
                     this.errors.password = 'Kata sandi awal wajib diisi';
                     isValid = false;
                 } else if (this.form.password.length < 6) {
                     this.errors.password = 'Kata sandi minimal 6 karakter';
                     isValid = false;
                 }

                 if (!isValid) {
                     this.shake = true;
                     setTimeout(() => this.shake = false, 400);
                 }

                 return isValid;
             },

             submitForm(e) {
                 if (!this.validate()) {
                     e.preventDefault();
                     return;
                 }
                 this.isSubmitting = true;
             },

             resetForm() {
                 this.form = { nip: '', name: '', email: '', department: 'Operasional', role: 'karyawan', password: '' };
                 this.rawPhone = '';
                 this.errors = {};
                 this.isSubmitting = false;
             }
         }"
         :class="{ 'animate-shake': shake }"
         class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl overflow-hidden relative transition-all duration-300">
        
        <div class="h-1.5 w-full bg-gradient-to-r from-sky-500 via-indigo-500 to-blue-600"></div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800/80 pb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg sm:text-xl text-slate-900 dark:text-white tracking-tight">Tambah Pengguna Baru</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftarkan akun karyawan, manajer, atau tim finance ke sistem.</p>
                    </div>
                </div>
                
                <button type="button" x-on:click="activeModal = null; resetForm()" 
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/80 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5" novalidate x-on:submit="submitForm">
                @csrf
                
                <!-- Baris 1: NIP & Nama -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            NIP / ID
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-id-badge absolute left-3.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none transition-colors duration-200"
                               :class="errors.nip ? 'text-rose-400' : 'text-slate-400'"></i>
                            <input type="text" name="nip" x-model="form.nip" x-on:input="errors.nip = null" placeholder="Contoh: EMP-202601" 
                                   class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs font-medium focus:outline-none transition-all"
                                   :class="errors.nip ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'">
                        </div>
                        <div x-show="errors.nip" x-transition x-cloak class="mt-1.5 flex items-center gap-1.5 text-rose-500">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold" x-text="errors.nip"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none transition-colors duration-200"
                               :class="errors.name ? 'text-rose-400' : 'text-slate-400'"></i>
                            <input type="text" name="name" x-model="form.name" x-on:input="errors.name = null" placeholder="Contoh: Nathan Santoso" 
                                   class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs font-medium focus:outline-none transition-all"
                                   :class="errors.name ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'">
                        </div>
                        <div x-show="errors.name" x-transition x-cloak class="mt-1.5 flex items-center gap-1.5 text-rose-500">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold" x-text="errors.name"></p>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: Email & Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none transition-colors duration-200"
                               :class="errors.email ? 'text-rose-400' : 'text-slate-400'"></i>
                            <input type="email" name="email" x-model="form.email" x-on:input="errors.email = null" placeholder="nathan@perusahaan.com" 
                                   class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs font-medium focus:outline-none transition-all"
                                   :class="errors.email ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'">
                        </div>
                        <div x-show="errors.email" x-transition x-cloak class="mt-1.5 flex items-center gap-1.5 text-rose-500">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold" x-text="errors.email"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            No. WhatsApp / HP
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute left-3.5 flex items-center gap-1.5 pointer-events-none">
                                <i class="fa-solid fa-phone text-slate-400 text-xs"></i>
                                <span class="text-slate-900 dark:text-slate-200 text-xs font-bold mt-[0.5px]">+62</span>
                            </div>
                            
                            <input type="text" 
                                   :value="rawPhone" 
                                   x-on:input="handlePhoneInput" 
                                   placeholder="812 3456 7890" 
                                   class="w-full pl-14 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs font-medium focus:outline-none transition-all"
                                   :class="errors.phone_number ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'">
                            
                            <input type="hidden" name="phone_number" :value="rawPhone ? '+62' + rawPhone : ''">
                        </div>
                        <div x-show="errors.phone_number" x-transition x-cloak class="mt-1.5 flex items-center gap-1.5 text-rose-500">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold" x-text="errors.phone_number"></p>
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Departemen & Role -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            Divisi / Departemen
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-building-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <select name="department" x-model="form.department"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs font-medium focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none cursor-pointer">
                                <option value="Operasional">Operasional</option>
                                <option value="Teknologi Informasi (IT)">Teknologi Informasi (IT)</option>
                                <option value="Marketing & Sales">Marketing & Sales</option>
                                <option value="Human Resources (HRD)">Human Resources (HRD)</option>
                                <option value="Finance & Accounting">Finance & Accounting</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                            Peran Pengguna (Role) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user-shield absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <select name="role" x-model="form.role"
                                    class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs font-medium focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all appearance-none cursor-pointer">
                                <option value="karyawan">Karyawan (Pemohon Klaim)</option>
                                <option value="manager">Manajer (Atasan Approver)</option>
                                <option value="admin">Admin Keuangan (Administrator)</option>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- Baris 4: Password -->
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[10px]">
                        Kata Sandi Awal <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-xs pointer-events-none transition-colors duration-200"
                           :class="errors.password ? 'text-rose-400' : 'text-slate-400'"></i>
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="form.password" x-on:input="errors.password = null" placeholder="Minimal 6 karakter" 
                               class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border text-slate-900 dark:text-slate-100 placeholder-slate-400 text-xs font-medium focus:outline-none transition-all"
                               :class="errors.password ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20'">
                        
                        <button type="button" x-on:click="showPass = !showPass" tabindex="-1"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">
                            <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <div x-show="errors.password" x-transition x-cloak class="mt-1.5 flex items-center gap-1.5 text-rose-500">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <p class="text-[10px] font-bold" x-text="errors.password"></p>
                    </div>
                </div>

                <div class="pt-5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-end gap-3 mt-8">
                    <button type="button" x-on:click="activeModal = null; resetForm()" :disabled="isSubmitting"
                            class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 text-xs font-bold text-slate-700 dark:text-slate-300 transition duration-200 cursor-pointer disabled:opacity-50">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white shadow-sm active:scale-95 transition-all duration-200 cursor-pointer flex items-center gap-2 disabled:opacity-75">
                        <i class="fa-solid fa-circle-notch fa-spin" x-show="isSubmitting" x-cloak></i>
                        <i class="fa-solid fa-check" x-show="!isSubmitting"></i>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Pengguna'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>