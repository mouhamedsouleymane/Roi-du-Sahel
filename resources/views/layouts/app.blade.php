<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Les Rois du Sahel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_roi.jpeg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-900 min-h-screen" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
    toggleCollapse() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
    }
}">

    <!-- Fixed Desktop Sidebar -->
    @include('layouts.sidebar')

    <!-- Mobile Drawer Overlay -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40 md:hidden" style="display: none;"></div>

    <!-- Mobile Sidebar Drawer -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 w-54 bg-purple-950 z-50 md:hidden overflow-y-auto" style="display: none;">
        @include('layouts.sidebar')
    </div>

    <!-- Main Content Area: Offset by margin-left dynamically (md:ml-64 when expanded, md:ml-20 when collapsed mini-bar) -->
    <div :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'"
        class="transition-all duration-300 ease-in-out flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- Top Sticky Navbar -->
        <header class="bg-yellow-500 border-b border-slate-200/80 sticky top-0 z-20 shadow-xs flex-shrink-0">
            <div class="px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
                <!-- Mobile Hamburger Button & Brand Logo -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 rounded-lg text-purple-950 hover:bg-purple-50 md:hidden focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/logo_roi.jpeg') }}" alt="Logo"
                            class="w-7 h-7 rounded-full object-cover ring-2 ring-amber-400">
                        <span class="font-extrabold text-sm text-purple-950 tracking-wide hidden sm:inline">CSP Les Rois
                            du Sahel</span>
                    </div>
                </div>

                <!-- User Profile Top Dropdown & Brand Tag -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-950 border border-amber-300/60 rounded-full text-xs font-black hover:bg-amber-100 transition">
                        🌐 Site Web / Vitrine
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 transition text-xs font-bold text-slate-700">
                                <div
                                    class="w-7 h-7 rounded-full bg-gradient-to-tr from-amber-400 to-purple-800 text-white font-bold flex items-center justify-center text-xs">
                                    {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                👤 {{ __('Profil') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    🚪 {{ __('Se déconnecter') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </header>

        <!-- Main Page Slot -->
        <main class="flex-1 bg-slate-50">
            @isset($header)
                <header class="bg-white border-b border-slate-200/80 shadow-xs">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{ $slot }}
        </main>

    </div>

    <!-- Global Confirmation Modal -->
    <x-confirm-modal />
</body>

</html>
