@extends('errors.layout')

@section('title', '401 — Authentication Required')

@section('content')
    <!-- Status Icon Badge -->
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-600 mb-6 shadow-xs">
        <i data-lucide="shield-alert" class="w-8 h-8"></i>
    </div>

    <!-- Error Code Pill -->
    <div class="mb-3">
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wide uppercase bg-amber-100/70 text-amber-800 border border-amber-200">
            Error 401 &bull; Unauthorized
        </span>
    </div>

    <!-- Title & Explanation -->
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-3">
        Authentication Required
    </h1>
    <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-md mx-auto mb-8">
        {{ $exception->getMessage() ?: 'You must be signed in with valid credentials to view this protected assessment or portal page.' }}
    </p>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('home') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-sm text-white bg-purple-600 hover:bg-purple-700 active:bg-purple-800 transition shadow-sm hover:shadow">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>{{ auth()->check() ? 'Back to Dashboard' : 'Return to Home' }}</span>
        </a>

        @guest
            <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 transition border border-slate-200">
                <i data-lucide="log-in" class="w-4 h-4 text-slate-500"></i>
                <span>Sign In</span>
            </a>
        @else
            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('home') }}'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 transition border border-slate-200">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span>Go Back</span>
            </button>
        @endguest
    </div>
@endsection
