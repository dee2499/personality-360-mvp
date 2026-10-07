<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Change Quo Assessment' }} — Change Quo</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 {{ auth()->check() ? 'bg-slate-50' : 'flex flex-col' }}" 
      x-data="{ mobileNavOpen: false }" 
      @keydown.window.escape="mobileNavOpen = false">

    @if(auth()->check())
        @php
            $currentUser = auth()->user();
            $isAdmin = $currentUser->isAdmin();
            $isManager = $currentUser->isManager();
            $isStaff = $isAdmin || $isManager;
            $portalLabel = $isAdmin ? 'Admin Console' : ($isManager ? 'Manager Console' : 'Participant Portal');
            
            $currentSection = 'Dashboard';
            if ($isAdmin) {
                if (request()->routeIs('admin.dashboard')) $currentSection = 'Dashboard';
                elseif (request()->routeIs('admin.companies.*')) $currentSection = 'Companies';
                elseif (request()->routeIs('admin.surveys.*')) $currentSection = 'Surveys';
                elseif (request()->routeIs('admin.people.*')) $currentSection = 'People Directory';
                elseif (request()->routeIs('admin.assessments.*')) $currentSection = 'Assessment Matrix';
                elseif (request()->routeIs('admin.categories.*')) $currentSection = 'Score Categories';
                elseif (request()->routeIs('profile.*')) $currentSection = 'User Profile';
            } elseif ($isManager) {
                if (request()->routeIs('admin.dashboard')) $currentSection = 'Dashboard';
                elseif (request()->routeIs('admin.companies.*')) $currentSection = 'My Company';
                elseif (request()->routeIs('admin.surveys.*')) $currentSection = 'Surveys';
                elseif (request()->routeIs('admin.people.*')) $currentSection = 'Team Directory';
                elseif (request()->routeIs('admin.assessments.*')) $currentSection = 'Assessment Matrix';
                elseif (request()->routeIs('profile.*')) $currentSection = 'User Profile';
            } else {
                if (request()->routeIs('participant.assessments.report') || request()->routeIs('participant.cq-report')) $currentSection = 'CQ Report';
                elseif (request()->routeIs('participant.assessments.*') || request()->routeIs('participant.surveys.*')) $currentSection = 'My Assessments';
                elseif (request()->routeIs('profile.*')) $currentSection = 'My Profile';
            }
        @endphp

        <!-- ==================================================== -->
        <!-- AUTHENTICATED LAYOUT: LEFT SIDEBAR + MOBILE DRAWER   -->
        <!-- ==================================================== -->

        <!-- Mobile Off-Canvas Sidebar (Drawer) -->
        <div x-cloak x-show="mobileNavOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div x-show="mobileNavOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                 @click="mobileNavOpen = false"></div>

            <!-- Drawer Panel -->
            <div class="fixed inset-y-0 left-0 flex w-full max-w-xs">
                <div x-show="mobileNavOpen"
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-4 pb-4 shadow-2xl">
                    
                    <!-- Drawer Header & Close Button -->
                    <div class="flex items-center justify-between px-6 pb-4 border-b border-slate-100">
                        <a href="{{ $isStaff ? route('admin.dashboard') : route('participant.assessments.index') }}" class="flex items-center gap-2 font-bold group">
                            <div class="w-8 h-8 flex items-center justify-center shrink-0">
                                <svg class="w-7 h-7" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                                    <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                                    <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                                    <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                                    <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                                    <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                                </svg>
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
                        <button type="button" 
                                @click="mobileNavOpen = false" 
                                class="rounded-xl p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"
                                aria-label="Close menu">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Navigation Links for Mobile Drawer -->
                    <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                        @if($isStaff)
                            <div>
                                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Overview
                                </div>
                                <div class="space-y-1">
                                    <a href="{{ route('admin.dashboard') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </div>
                            </div>

                            <div>
                                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Management
                                </div>
                                <div class="space-y-1">
                                    @if($isAdmin && isset($activeCompany))
                                        <a href="{{ route('admin.companies.show', $activeCompany->id) }}" 
                                           @click="mobileNavOpen = false"
                                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.show') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                            <i data-lucide="building-2" class="w-4 h-4 {{ request()->routeIs('admin.companies.show') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                            <span>My Company</span>
                                        </a>
                                    @elseif($isManager && $currentUser->company_id)
                                        <a href="{{ route('admin.companies.show', $currentUser->company_id) }}" 
                                           @click="mobileNavOpen = false"
                                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                            <i data-lucide="building-2" class="w-4 h-4 {{ request()->routeIs('admin.companies.*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                            <span>My Company</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.surveys.index') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.surveys.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="clipboard-list" class="w-4 h-4 {{ request()->routeIs('admin.surveys.*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>Company Surveys</span>
                                    </a>

                                    <a href="{{ route('admin.people.index') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.people.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.people.*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>Team Directory</span>
                                    </a>

                                    <a href="{{ route('admin.survey-assessment') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.survey-assessment') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.survey-assessment') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>Survey Assessment</span>
                                    </a>
                                </div>
                            </div>

                            @if($isAdmin)
                                <div>
                                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        Admin Actions
                                    </div>
                                    <div class="space-y-1">
                                        <a href="{{ route('admin.companies.index') }}" 
                                           @click="mobileNavOpen = false"
                                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.index') || request()->routeIs('admin.companies.create') || request()->routeIs('admin.companies.edit') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                            <i data-lucide="layers" class="w-4 h-4 {{ request()->routeIs('admin.companies.index') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                            <span>All Companies</span>
                                        </a>

                                        <a href="{{ route('admin.categories.index') }}" 
                                           @click="mobileNavOpen = false"
                                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                            <i data-lucide="sliders-horizontal" class="w-4 h-4 {{ request()->routeIs('admin.categories.*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                            <span>Categories & Recalculation</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div>
                                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Assessments
                                </div>
                                <div class="space-y-1">
                                    @php
                                        $isDashboardActive = (request()->routeIs('participant.assessments.index') && request()->query('view') !== 'matrix') || request()->routeIs('participant.cq-report') || request()->routeIs('participant.assessments.report');
                                        $isMatrixActive = request()->routeIs('participant.assessments.index') && request()->query('view') === 'matrix';
                                    @endphp
                                    <a href="{{ route('participant.assessments.index') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $isDashboardActive ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 {{ $isDashboardActive ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>My Dashboard</span>
                                    </a>

                                    <a href="{{ route('participant.assessments.index', ['view' => 'matrix']) }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $isMatrixActive ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 {{ $isMatrixActive ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>My Assessments</span>
                                    </a>
                                </div>
                            </div>

                            <div>
                                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Account
                                </div>
                                <div class="space-y-1">
                                    <a href="{{ route('profile.show') }}" 
                                       @click="mobileNavOpen = false"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="user" class="w-4 h-4 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                                        <span>My Profile</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Drawer Footer / Profile -->
                    <div class="p-4 border-t border-slate-100 space-y-2">
                        <a href="{{ route('profile.show') }}" 
                           @click="mobileNavOpen = false"
                           class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition border border-slate-100">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100 shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col text-left overflow-hidden">
                                <span class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-400 capitalize font-medium">
                                    {{ auth()->user()->company?->name ?? ($isAdmin ? 'Administrator' : ($isManager ? 'Company Manager' : 'Team Member')) }}
                                    @if($isManager)
                                        <span class="text-purple-600 font-bold ml-1">(Manager)</span>
                                    @endif
                                </span>
                            </div>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 transition cursor-pointer">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Persistent Left Sidebar -->
        <aside class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col border-r border-slate-200 bg-white shadow-xs">
            <!-- Sidebar Brand Header -->
            <div class="flex h-16 shrink-0 items-center justify-between px-6 border-b border-slate-100">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('participant.assessments.index') }}" class="flex items-center gap-2.5 font-bold group">
                    <div class="w-9 h-9 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                            <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                            <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                            <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                            <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                            <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-black tracking-tight text-purple-950 leading-none">
                            change<span class="text-purple-600">quo</span>
                        </span>
                        <span class="text-[9px] font-bold tracking-tight text-purple-700 mt-1">
                            Unlocking Possibilities
                        </span>
                    </div>
                </a>
            </div>

            <!-- Sidebar Navigation Links -->
            <div class="flex flex-1 flex-col overflow-y-auto px-4 py-5 justify-between">
                <nav class="space-y-6">
                    @if($isStaff)
                        <div>
                            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Overview
                            </div>
                            <div class="space-y-1">
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>Dashboard</span>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Management
                            </div>
                            <div class="space-y-1">
                                @if($isAdmin && isset($activeCompany))
                                    <a href="{{ route('admin.companies.show', $activeCompany->id) }}" 
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.show') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="building-2" class="w-4 h-4 {{ request()->routeIs('admin.companies.show') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                        <span>My Company</span>
                                    </a>
                                @elseif($isManager && $currentUser->company_id)
                                    <a href="{{ route('admin.companies.show', $currentUser->company_id) }}" 
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="building-2" class="w-4 h-4 {{ request()->routeIs('admin.companies.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                        <span>My Company</span>
                                    </a>
                                @endif

                                <a href="{{ route('admin.surveys.index') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.surveys.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="clipboard-list" class="w-4 h-4 {{ request()->routeIs('admin.surveys.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>Company Surveys</span>
                                </a>

                                <a href="{{ route('admin.people.index') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.people.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.people.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>Team Directory</span>
                                </a>

                                <a href="{{ route('admin.survey-assessment') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.survey-assessment') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.survey-assessment') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>Survey Assessment</span>
                                </a>
                            </div>
                        </div>

                        @if($isAdmin)
                            <div>
                                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Admin Actions
                                </div>
                                <div class="space-y-1">
                                    <a href="{{ route('admin.companies.index') }}" 
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.companies.index') || request()->routeIs('admin.companies.create') || request()->routeIs('admin.companies.edit') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="layers" class="w-4 h-4 {{ request()->routeIs('admin.companies.index') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                        <span>All Companies</span>
                                    </a>

                                    <a href="{{ route('admin.categories.index') }}" 
                                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                        <i data-lucide="sliders-horizontal" class="w-4 h-4 {{ request()->routeIs('admin.categories.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                        <span>Categories & Recalculation</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div>
                            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Evaluations
                            </div>
                            <div class="space-y-1">
                                @php
                                    $isDashboardActive = (request()->routeIs('participant.assessments.index') && request()->query('view') !== 'matrix') || request()->routeIs('participant.cq-report') || request()->routeIs('participant.assessments.report');
                                    $isMatrixActive = request()->routeIs('participant.assessments.index') && request()->query('view') === 'matrix';
                                @endphp
                                <a href="{{ route('participant.assessments.index') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $isDashboardActive ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="layout-dashboard" class="w-4 h-4 {{ $isDashboardActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>My Dashboard</span>
                                </a>

                                <a href="{{ route('participant.assessments.index', ['view' => 'matrix']) }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $isMatrixActive ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="clipboard-check" class="w-4 h-4 {{ $isMatrixActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>My Assessments</span>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Account
                            </div>
                            <div class="space-y-1">
                                <a href="{{ route('profile.show') }}" 
                                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700 shadow-xs border border-indigo-100' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                                    <i data-lucide="user" class="w-4 h-4 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
                                    <span>My Profile</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </nav>

                <!-- Sidebar Footer User Card & Logout -->
                <div class="pt-4 border-t border-slate-100 mt-6 space-y-2">
                    <a href="{{ route('profile.show') }}" 
                       class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200 group {{ request()->routeIs('profile.*') ? 'bg-indigo-50/70 border-indigo-100' : '' }}"
                       title="Manage Account">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100 shrink-0">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col text-left overflow-hidden">
                            <span class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600 transition">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="text-[10px] text-slate-400 truncate font-medium">
                                {{ auth()->user()->company?->name ?? ($isAdmin ? 'Administrator' : ($isManager ? 'Company Manager' : 'Team Member')) }}
                                @if($isManager)
                                    <span class="text-purple-600 font-bold ml-1">(Manager)</span>
                                @endif
                            </span>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4 text-slate-400 hover:text-rose-500"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area (Pushed right on desktop by lg:pl-64) -->
        <div class="lg:pl-64 flex flex-col min-h-screen">
            <!-- Mobile Sticky Top Bar -->
            <div class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6 lg:hidden">
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="mobileNavOpen = true" 
                            class="p-2 -ml-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-hidden"
                            aria-label="Open sidebar menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <a href="{{ $isStaff ? route('admin.dashboard') : route('participant.assessments.index') }}" class="flex items-center gap-1.5 font-bold text-sm">
                        <div class="w-6 h-6 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                                <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                                <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                                <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                                <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                                <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                            </svg>
                        </div>
                        <span class="tracking-tight font-black text-purple-950">change<span class="text-purple-600">quo</span></span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if($isAdmin && isset($activeCompany))
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-100 max-w-[120px] truncate">
                            {{ $activeCompany->name }}
                        </span>
                    @endif
                    <a href="{{ route('profile.show') }}" class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </a>
                </div>
            </div>

            <!-- Desktop Sticky Header Bar -->
            <header class="sticky top-0 z-30 hidden lg:flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white/95 backdrop-blur-xs px-8">
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="font-bold uppercase tracking-wider text-slate-400">{{ $portalLabel }}</span>
                        <span class="text-slate-300">/</span>
                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100">
                            {{ $currentSection }}
                        </span>
                    </div>

                    @if($isAdmin && isset($allCompanies) && $allCompanies->isNotEmpty())
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" 
                                    @click="open = !open" 
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 transition cursor-pointer shadow-2xs">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                                <span>Company: <strong class="text-slate-900">{{ $activeCompany?->name ?? 'Select Company' }}</strong></span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition" :class="{ 'rotate-180': open }"></i>
                            </button>

                            <div x-show="open" 
                                 @click.outside="open = false" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute left-0 mt-2 w-64 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden z-40 py-1 divide-y divide-slate-100">
                                
                                <div class="px-3.5 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/70">
                                    Select Active Company
                                </div>

                                <div class="max-h-60 overflow-y-auto">
                                    @foreach($allCompanies as $comp)
                                        <a href="{{ request()->fullUrlWithQuery(['switch_company_id' => $comp->id]) }}" 
                                           class="flex items-center justify-between px-3.5 py-2.5 text-xs hover:bg-indigo-50/60 transition {{ ($activeCompany?->id === $comp->id) ? 'bg-indigo-50/70 font-bold text-indigo-900' : 'text-slate-700' }}">
                                            <span class="truncate">{{ $comp->name }}</span>
                                            @if($activeCompany?->id === $comp->id)
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('profile.show') }}" 
                       class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition {{ request()->routeIs('profile.*') ? 'bg-indigo-50 ring-1 ring-indigo-200' : '' }}"
                       title="View Profile & Security">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-xs border border-indigo-100">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-xs font-semibold text-slate-800 leading-tight">
                                {{ auth()->user()->name }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">
                                {{ auth()->user()->company?->name ?? ($isAdmin ? 'Administrator' : ($isManager ? 'Company Manager' : 'Team Member')) }}
                                @if($isManager)
                                    <span class="text-purple-600 font-bold ml-1">(Manager)</span>
                                @endif
                            </span>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition cursor-pointer"
                                title="Sign out">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Flash Messages & Global Errors -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
                @if($errors->any())
                    <div class="mb-4 p-4 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200 shadow-xs" role="alert">
                        <div class="flex items-center gap-2 font-bold mb-2 text-rose-900">
                            <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 shrink-0"></i>
                            <span>Please review and fix the following issues:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-rose-700 font-medium">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 flex items-center gap-3 p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 shadow-xs" role="alert">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                        <div class="font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 flex items-center gap-3 p-4 text-sm text-amber-800 rounded-xl bg-amber-50 border border-amber-200 shadow-xs" role="alert">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0"></i>
                        <div class="font-medium">{{ session('warning') }}</div>
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

            <!-- Authenticated Footer -->
            <footer class="bg-white border-t border-slate-200 mt-auto py-5 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
                    <span>&copy; {{ date('Y') }} Change Quo Assessment System. All rights reserved.</span>
                </div>
            </footer>
        </div>

    @else
        <!-- ========================================== -->
        <!-- GUEST LAYOUT: CLEAN MINIMAL HEADER         -->
        <!-- ========================================== -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Brand / Logo -->
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold group">
                            <div class="w-9 h-9 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <svg class="w-8 h-8" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M48 48 C38 20, 10 15, 8 32 C6 48, 30 50, 48 50 Z" fill="#7C3AED" opacity="0.95"/>
                                    <path d="M48 52 C32 54, 16 66, 20 80 C24 92, 42 78, 48 56 Z" fill="#9333EA" opacity="0.85"/>
                                    <path d="M52 48 C62 20, 90 15, 92 32 C94 48, 70 50, 52 50 Z" fill="#7C3AED" opacity="0.95"/>
                                    <path d="M52 52 C68 54, 84 66, 80 80 C76 92, 58 78, 52 56 Z" fill="#9333EA" opacity="0.85"/>
                                    <ellipse cx="50" cy="50" rx="3" ry="22" fill="#581C87"/>
                                    <circle cx="50" cy="24" r="3.5" fill="#581C87"/>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xl font-black tracking-tight text-purple-950 leading-none">
                                    change<span class="text-purple-600">quo</span>
                                </span>
                                <span class="text-[9px] font-bold tracking-tight text-purple-700 mt-1">
                                    Unlocking Possibilities
                                </span>
                            </div>
                        </a>
                    </div>

                    <div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                            <span>Sign In</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages & Global Errors for Guests -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if($errors->any())
                <div class="mb-4 p-4 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200 shadow-xs" role="alert">
                    <div class="flex items-center gap-2 font-bold mb-2 text-rose-900">
                        <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600 shrink-0"></i>
                        <span>Please review and fix the following issues:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs text-rose-700 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
        </div>

        <!-- Main Page Content for Guests -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </main>

        <!-- Guest Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-2">
                <span>&copy; {{ date('Y') }} Change Quo Assessment System. All rights reserved.</span>
            </div>
        </footer>
    @endif

    <!-- Universal Action Confirmation & Alert Modal -->
    <x-confirm-modal />
</body>
</html>
