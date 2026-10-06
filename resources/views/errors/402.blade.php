@extends('errors.layout')

@section('title', '402 — Subscription Required')

@section('content')
    <!-- Status Icon Badge -->
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-200/80 text-indigo-600 mb-6 shadow-xs">
        <i data-lucide="credit-card" class="w-8 h-8"></i>
    </div>

    <!-- Error Code Pill -->
    <div class="mb-3">
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-indigo-100/70 text-indigo-800 border border-indigo-200">
            Error 402 &bull; Payment Required
        </span>
    </div>

    <!-- Title & Explanation -->
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-3">
        Subscription Required
    </h1>
    <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-md mx-auto mb-8">
        {{ $exception->getMessage() ?: 'Access to this specialized 360 assessment report or enterprise feature requires an active organization plan tier.' }}
    </p>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('home') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-sm text-white bg-purple-600 hover:bg-purple-700 active:bg-purple-800 transition shadow-sm hover:shadow">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>{{ auth()->check() ? 'Back to Dashboard' : 'Return to Home' }}</span>
        </a>

        <a href="mailto:support@changequo.com?subject=Enterprise Plan Upgrade Inquiry" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 transition border border-slate-200">
            <i data-lucide="mail" class="w-4 h-4 text-slate-500"></i>
            <span>Contact Administrator</span>
        </a>
    </div>
@endsection
