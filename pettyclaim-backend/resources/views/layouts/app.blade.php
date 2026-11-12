<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin - PettyClaim')</title>

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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #1e293b; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen antialiased selection:bg-indigo-600 selection:text-white transition-colors duration-200" 
      x-data="{ 
          activeTab: '{{ session('activeTab', 'jurnal') }}', 
          searchQuery: '', 
          activeModal: null, 
          modalData: {}, 
          sidebarOpen: false, 
          isDark: document.documentElement.classList.contains('dark') 
      }">

    <!-- Mobile Overlay Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-on:click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-950/60 z-40 lg:hidden" 
         x-cloak></div>

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- MEMANGGIL COMPONENT SIDEBAR SESUAI STRUKTUR FOLDER -->
        @include('components.sidebar')

        <!-- Main Wrapper Konten -->
        <div class="flex-1 lg:ml-64 min-h-screen flex flex-col w-full min-w-0">
            
            <!-- MEMANGGIL COMPONENT NAVBAR SESUAI STRUKTUR FOLDER -->
            @include('components.navbar')

            <main class="p-4 sm:p-6 lg:p-8 flex-1 w-full max-w-full overflow-x-hidden">
                @yield('content')
            </main>
        </div>

    </div>

    <!-- Script SweetAlert2 -->
    <script>
        function confirmLogout() {
            const isDarkTheme = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem PettyClaim?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                background: isDarkTheme ? '#0f172a' : '#ffffff',
                color: isDarkTheme ? '#f8fafc' : '#0f172a',
                customClass: {
                    popup: 'rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl',
                    confirmButton: 'px-4 py-2 rounded-lg text-xs font-semibold',
                    cancelButton: 'px-4 py-2 rounded-lg text-xs font-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDarkTheme = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                background: isDarkTheme ? '#0f172a' : '#ffffff',
                color: isDarkTheme ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#10b981',
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl'
                }
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDarkTheme = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Perhatian',
                text: "{{ session('error') }}",
                icon: 'error',
                background: isDarkTheme ? '#0f172a' : '#ffffff',
                color: isDarkTheme ? '#f8fafc' : '#0f172a',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl'
                }
            });
        });
    </script>
    @endif

</body>
</html>