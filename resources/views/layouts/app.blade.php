<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Personality 360 Assessment' }} — 360 Assessment</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col">
    <!-- Top Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('participant.assessments.index') }}" 
                       class="flex items-center gap-2.5 font-bold text-lg text-slate-900 group">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-extrabold shadow-sm group-hover:bg-indigo-700 transition">
                            <i data-lucide="compass" class="w-5 h-5"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="leading-tight tracking-tight text-slate-900 font-extrabold">Personality 360</span>
                            <span class="text-[10px] font-semibold tracking-wider uppercase text-slate-400">Enterprise Feedback</span>
                        </div>
                    </a>

                    <!-- Navigation for Admin -->
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <nav class="hidden md:flex items-center space-x-1 pl-4 border-l border-slate-200">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.surveys.index') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.surveys.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Surveys
                            </a>
                            <a href="{{ route('admin.people.index') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.people.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                People
                            </a>
                            <a href="{{ route('admin.assessments.index') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.assessments.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Assessments
                            </a>
                        </nav>
                    @elseif(auth()->check())
                        <nav class="hidden md:flex items-center space-x-1 pl-4 border-l border-slate-200">
                            <a href="{{ route('participant.assessments.index') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('participant.assessments.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                My Assessments
                            </a>
                        </nav>
                    @endif
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs border border-slate-200">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-semibold text-slate-800 leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-slate-400 capitalize font-medium">{{ auth()->user()->role }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition"
                                title="Sign out">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="mb-4 flex items-center gap-3 p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 shadow-xs" role="alert">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 flex items-center gap-3 p-4 text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200 shadow-xs" role="alert">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 flex items-center gap-3 p-4 text-sm text-sky-800 rounded-xl bg-sky-50 border border-sky-200 shadow-xs" role="alert">
                <i data-lucide="info" class="w-5 h-5 text-sky-600 shrink-0"></i>
                <div class="font-medium">{{ session('info') }}</div>
            </div>
        @endif
    </div>

    <!-- Main Page Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
            <span>&copy; {{ date('Y') }} Personality 360 Assessment System. All rights reserved.</span>
            <span class="flex items-center gap-2">
                <span>Categories:</span>
                <span class="inline-flex items-center gap-1 font-medium">🍏 Apple (0-20%)</span>
                <span class="inline-flex items-center gap-1 font-medium">🍊 Orange (>20-40%)</span>
                <span class="inline-flex items-center gap-1 font-medium">🍅 Tomato (>40-60%)</span>
                <span class="inline-flex items-center gap-1 font-medium">🍋 Lemon (>60-80%)</span>
                <span class="inline-flex items-center gap-1 font-medium">🥒 Cucumber (>80-100%)</span>
            </span>
        </div>
    </footer>
</body>
</html>
