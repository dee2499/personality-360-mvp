<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Error') — Change Quo Assessment</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 bg-slate-50 selection:bg-purple-500 selection:text-white">

    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 transition hover:opacity-90">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center p-1.5 shadow-xs border border-purple-100 shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="ChangeQuo" class="w-7 h-7 object-contain">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-black tracking-tight text-purple-950 leading-none">
                        change<span class="text-purple-600">quo</span>
                    </span>
                    <span class="text-[8px] font-bold tracking-tight text-purple-700 mt-0.5">
                        Unlocking Possibilities
                    </span>
                </div>
            </a>

            <!-- Right Header Controls -->
            <div class="flex items-center gap-3">
                @auth
                    <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->isAdmin())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                Admin
                            </span>
                        @elseif(auth()->user()->isManager())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/60">
                                Manager
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                Participant
                            </span>
                        @endif
                    </div>

                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 hover:text-purple-700 hover:bg-purple-50 transition border border-slate-200">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Dashboard</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition border border-transparent hover:border-red-100">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Sign Out</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition shadow-xs">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                        <span>Sign In</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative overflow-hidden">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-purple-200/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-xl mx-auto relative z-10 py-8">
            <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/60 p-6 sm:p-10 text-center">
                @yield('content')
            </div>

            <!-- Contextual Help Box -->
            <div class="mt-6 text-center text-xs text-slate-500">
                <span>Need help with your assessment access?</span>
                <span class="mx-1.5 text-slate-300">•</span>
                <a href="mailto:support@changequo.com" class="font-semibold text-purple-700 hover:text-purple-800 underline underline-offset-2">
                    Contact Support
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
            <span>&copy; {{ date('Y') }} Change Quo Assessment System. All rights reserved.</span>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span>CQ Sync & 360 Adaptability Platform</span>
            </div>
        </div>
    </footer>

</body>
</html>
